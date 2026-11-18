<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Chat;

use Neo\BasicBundle\Ai\AiConnection;
use Neo\BasicBundle\Ai\ModelCatalog;
use Neo\SuperAgentBundle\Licence\Features;
use Neo\SuperAgentBundle\Mcp\McpToolbox;
use Neo\SuperAgentBundle\Model\AgentLanguageModel;
use Neo\SuperAgentBundle\Model\AgentModel;
use Neo\SuperAgentBundle\Skill\InstalledSkills;
use Neo\SuperAgentBundle\Skill\SkillPackage;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

/**
 * One reply of the assistant: the model with the core skill, SETTINGS, STATE and
 * the CATALOG decides what to do; it loads skills (load_module) and tool
 * definitions (load_tools) itself, calls the tools and writes the answer. The
 * application runs the loop, executes tools within the limits of the licence and
 * the settings, and writes nothing to the visitor itself: when the model cannot be
 * reached, the widget shows its error notice. Never logs conversation content.
 */
final class ChatHandler
{
    /** Up to this many pages are listed in PAGES; larger sites get the tool find_pages. */
    private const LISTED_PAGES = 150;

    /** Steps that only load again what is loaded already, per reply, that do not count (see run()). */
    private const FREE_REPEAT_LOADS = 3;

    /** Reasoning tokens per effort, as Requesty converts it for models that take a token budget; added to the answer length. */
    private const REASONING_BUDGET = ['none' => 1024, 'low' => 1024, 'medium' => 8192, 'high' => 16384];

    private Features $features;

    private bool $canForward = false;

    private string $submitLabel = '';

    /** @var array<int, array{title: string, url: string, path: string, description: string}> pages the assistant may open */
    private array $pages = [];

    /** Token of the guest check sent by the widget (McpToolbox::prepare). */
    private string $guest = '';

    /** @var array{url: string, title: string, now: bool}|null page to open, set by open_page or the marker line */
    private ?array $navigate = null;

    private ?string $handoff = null;

    /** Reports what the assistant is doing (thinking, pages, form, search), for the status in the chat. */
    private ?\Closure $progress = null;

    /** Called with a text the model wrote together with a tool call (shown at once). */
    private ?\Closure $say = null;

    /** @var list<string> texts written together with tool calls in this reply */
    private array $interim = [];

    /** @var list<string> skills available in this request */
    private array $available = [];

    /** @var list<string> tools available in this request */
    private array $tools = [];

    /** @var list<string> skills loaded in this reply */
    private array $loaded = [];

    /** @var list<string> tools loaded on their own (load_tools) */
    private array $loadedTools = [];

    public function __construct(
        private readonly ProviderClient $client,
        private readonly GroundingProvider $grounding,
        private readonly SystemPromptBuilder $prompt,
        private readonly CannedAnswers $canned,
        private readonly AiConnection $connection,
        private readonly ModelCatalog $models,
        private readonly LoggerInterface $logger,
        private readonly WebResearch $research,
        private readonly DefaultTexts $defaults,
        private readonly PersonalDataGuard $guard,
        private readonly PageIndex $pageIndex,
        private readonly McpToolbox $mcp,
        private readonly InstalledSkills $installedSkills,
        private readonly ConversationMemory $memory,
        private readonly int $maxSteps = 12,
        /** Finds the language record (agent id, page language); Contao's model unless given (unit tests). */
        private readonly ?\Closure $languageRecord = null,
    ) {
        $this->features = Features::forEdition('');
    }

    /**
     * reply: the model's answer. handoff: text that opens the forwarding form,
     * prefilled (the AI cannot send anything). navigate: a page to open (now) or to
     * offer as a button. message: the visitor's message as it was sent, when privacy
     * mode removed personal data from it; the widget keeps this version.
     * error "unavailable": no answer from the AI service; the widget shows its notice.
     *
     * @param list<array{role: string, content: string}> $history
     * @param array{url?: string, title?: string, root?: int}|null $pageContext
     * @param callable(string): void|null                          $progress    called with the step before a tool runs
     * @param string                                               $guest       what the visitor proved earlier in this conversation (GuestCheck token)
     * @param callable(string): void|null                          $say         called with a text the model wrote together with a tool call, while the tool runs
     * @param string                                               $memory      tool results and loaded skills of earlier replies (ConversationMemory token)
     * @param string                                               $host        host of the website the visitor is on (links to its pages)
     *
     * @return array{interim?: list<string>, reply?: string, handoff?: string|null, navigate?: array{url: string, title: string, now: bool}|null, message?: string, error?: string, guest?: string, memory?: string, files?: list<array{token: string, name: string, size: int}>}
     */
    public function reply(AgentModel $agent, string $message, array $history, string $locale, ?array $pageContext = null, ?Features $features = null, ?callable $progress = null, string $guest = '', ?callable $say = null, string $memory = '', string $host = ''): array
    {
        $this->features = $features ?? Features::forEdition('');
        // Results of the connections and the proof of a guest check count only with
        // the connections of the licence: the licence of this request decides, not
        // what the conversation brings along.
        $this->guest = $this->features->mcp ? $guest : '';
        $this->memory->load((int) $agent->id, $memory);

        if (!$this->features->mcp) {
            $this->memory->forgetResults();
        }

        $this->say = null !== $say ? \Closure::fromCallable($say) : null;
        $this->interim = [];
        $this->progress = null !== $progress ? \Closure::fromCallable($progress) : null;
        $this->navigate = null;
        $this->handoff = null;
        $this->pages = $this->features->navigation ? $this->pageIndex->forRoot((int) ($pageContext['root'] ?? 0)) : [];
        $texts = null !== $this->languageRecord ? ($this->languageRecord)((int) $agent->id, $locale) : AgentLanguageModel::findForAgent((int) $agent->id, $locale);
        $this->canForward = $this->features->contact && SystemPromptBuilder::canForward($agent);
        $this->submitLabel = $this->label($agent, $texts, 'esc_submit', $locale);
        $out = [];

        // Privacy mode, first line: identifiers never reach the AI service, not even
        // from earlier messages of the conversation. Only the kinds are logged.
        if ('' !== (string) $agent->privacy_mode) {
            $redacted = $this->guard->redact($message);

            if ([] !== $redacted['types']) {
                $this->logger->info('SuperAgent: personal data removed before the AI call: '.implode(', ', array_unique($redacted['types'])));
                $message = $redacted['text'];
                $out['message'] = $message;
            }

            foreach ($history as $i => $turn) {
                if ('user' === $turn['role']) {
                    $history[$i]['content'] = $this->guard->redact($turn['content'])['text'];
                }
            }
        }

        $apiKey = $this->connection->apiKey();
        $baseUrl = $this->connection->baseUrl();

        if ('' === $apiKey || '' === $baseUrl || '' === (string) $agent->model) {
            return $out + ['error' => 'unavailable'];
        }

        try {
            $content = $this->run($agent, $texts, $locale, $pageContext, $baseUrl, $apiKey, $history, $message);

            if ([] !== $this->interim) {
                $out['interim'] = $this->interim;
            }
        } catch (\Throwable $e) {
            $this->logger->warning('SuperAgent: provider call failed: '.$e->getMessage());

            return $out + ['error' => 'unavailable'];
        }

        // Marker lines of models without tool calling; never shown to the visitor.
        if (preg_match('/\[\[\s*PAGE\s*:\s*(\d+)\s*(now|offer)?\s*\]\]/iu', $content, $m)) {
            $content = trim(str_replace($m[0], '', $content));
            $this->openPage((int) $m[1], 'now' === strtolower($m[2] ?? ''));
        }

        if (preg_match('/\[\[\s*HANDOFF\s*:\s*(.*?)\s*\]\]/su', $content, $m)) {
            $content = trim(str_replace($m[0], '', $content));
            $this->handoff ??= $this->canForward && '' !== trim($m[1]) ? trim(strip_tags($m[1])) : null;
        }

        // Links to pages of the website stay only where the page exists for the
        // assistant: its address is in the knowledge or, with navigation (Basic), in
        // the page list. Without navigation (free edition) the assistant does not
        // link pages itself; with it, a link never points to a page that is not there.
        $knowledge = $this->grounding->forAgent($agent)."\n".implode("\n", $this->canned->pairs($texts));

        if ($this->features->navigation) {
            $knowledge .= "\n".implode("\n", array_column($this->pages, 'url'));
        }

        $content = PageLinks::limit($content, $host, $knowledge);

        if ('' === $content) {
            return $out + ['error' => 'unavailable'];
        }

        // A page opens by itself only when the reply names it: a page opened in
        // place of another, without saying so, becomes the button below the reply.
        if (null !== $this->navigate && $this->navigate['now'] && false === mb_stripos($content, trim($this->navigate['title']))) {
            $this->navigate['now'] = false;
        }

        // The widget puts the reply into the page with JavaScript, past every HTML
        // listener: addresses are defused here.
        $out += [
            'reply' => MailGuard::scrub($content),
            'handoff' => null !== $this->handoff ? MailGuard::scrub($this->handoff) : null,
            'navigate' => $this->navigate,
        ];

        // What the visitor proved (guest check of a connection) goes back with the
        // conversation; the widget sends it with the next message.
        if ('' !== ($token = $this->mcp->guestToken())) {
            $out['guest'] = $token;
        }

        // What the next reply starts with: the tool results and the loaded skills
        // and tools of this conversation, encrypted (ConversationMemory).
        $this->memory->remember($this->loaded, $this->loadedTools);

        if ('' !== ($memory = $this->memory->token())) {
            $out['memory'] = $memory;
        }

        // Files a tool delivered (e.g. an invoice): the visitor downloads them once.
        if ([] !== ($files = $this->mcp->files())) {
            $out['files'] = $files;
        }

        return $out;
    }

    /** Directory of the skill package of this bundle. */
    public static function skillDir(): string
    {
        return \dirname(__DIR__, 2).'/skills';
    }

    /**
     * The loop: the model gets the catalogue and the tools of its loaded skills,
     * loads what it needs and calls tools until it answers. In the last step no
     * tools are offered, so the model writes its answer.
     *
     * @param list<array{role: string, content: string}> $history
     * @param array<string, mixed>|null                  $pageContext
     */
    private function run(AgentModel $agent, ?AgentLanguageModel $texts, string $locale, ?array $pageContext, string $baseUrl, string $apiKey, array $history, string $message): string
    {
        // Tools of the connected MCP servers (edition Plus): in the catalogue, loaded with load_tools.
        $this->mcp->prepare($agent, $this->features->mcp, $this->guest);
        $this->tools = [...$this->availableTools($agent), ...$this->mcp->names()];
        $skills = SkillPackage::load(self::skillDir(), ChatTools::NAMES);

        // Skills installed by an administrator and ticked at the assistant (edition Plus).
        if ($this->features->skills) {
            $skills = $skills->withInstalled(
                $this->installedSkills->forAgent($agent, $this->mcp),
                fn (string $name) => $this->logger->warning(\sprintf('SuperAgent: installed skill "%s" left out (name taken, too long, or a dependency is missing).', $name)),
            );
        }
        $this->available = $this->availableSkills($agent, $skills);
        // Skills the settings require apply from the first step, and those loaded
        // in earlier replies of this conversation stay loaded.
        $this->loaded = $skills->resolve(array_values(array_unique([
            ...array_intersect(['medical', 'privacy'], $this->available),
            ...array_intersect($this->memory->skills(), $this->available),
        ])));
        $this->loadedTools = array_values(array_intersect($this->memory->tools(), $this->tools));

        $context = [
            'locale' => $locale,
            'page' => $pageContext,
            'canForward' => $this->canForward,
            'forwardTo' => trim((string) $agent->escalation_name) ?: $this->defaults->text('widget.team', $locale),
            'submitLabel' => $this->submitLabel,
            'contactPage' => isset($this->pages[(int) $agent->contact_page]) ? ['id' => (int) $agent->contact_page, 'title' => $this->pages[(int) $agent->contact_page]['title']] : null,
            'navigation' => [] !== $this->pages,
            'webSearch' => \in_array('web_search', $this->tools, true),
            'medicalGuidance' => $this->label($agent, $texts, 'medical_refusal', $locale),
            'privacyGuidance' => $this->label($agent, $texts, 'privacy_reject', $locale),
            'knowledge' => $this->grounding->forAgent($agent),
            'qna' => $this->canned->pairs($texts),
            'pages' => \count($this->pages) <= self::LISTED_PAGES ? $this->pages : [],
            'available' => $this->available,
            'loaded' => $this->loaded,
            'tools' => $this->tools,
            'toolSummaries' => array_combine($this->mcp->names(), array_map($this->mcp->summary(...), $this->mcp->names())),
            'toolsMode' => true,
            // Results of earlier replies; those of this reply are in the messages.
            'memory' => $this->memory->entries(),
        ];

        [$reasoning, $maxTokens] = $this->limits($agent);
        $messages = array_merge([['role' => 'system', 'content' => '']], $history, [['role' => 'user', 'content' => $message]]);
        $freeLoads = 0;

        for ($step = 0; $step <= $this->maxSteps; ++$step) {
            $context['loaded'] = $this->loaded;
            // The catalogue lists only tools still to load; loaded ones are offered as functions.
            $context['tools'] = array_values(array_diff($this->tools, $this->activeTools($skills)));
            $context['hasTools'] = [] !== $this->tools;
            $messages[0]['content'] = $this->prompt->build($skills, $agent, $context);
            $offer = $step < $this->maxSteps ? $this->offer($skills) : [];

            try {
                $result = $this->client->call($baseUrl, $apiKey, (string) $agent->model, $this->temperature($agent), $maxTokens, $messages, $offer, $reasoning);
            } catch (ClientExceptionInterface $e) {
                if ([] === $offer || !\in_array($e->getResponse()->getStatusCode(), [400, 404, 422], true)) {
                    throw $e;
                }

                // The service refused the definition of one tool (e.g. a schema it does
                // not accept): that tool is left out, the others stay.
                $refused = self::refusedFunction($e);

                if (null !== $refused && $this->mcp->has($refused)) {
                    $this->mcp->refused($refused);
                    $this->tools = array_values(array_diff($this->tools, [$refused]));
                    $this->loadedTools = array_values(array_diff($this->loadedTools, [$refused]));
                    $context['tools'] = $this->tools;
                    --$step;

                    continue;
                }

                // A model or route without tool calling: all available skills at once,
                // no tools; navigation and forwarding then work through marker lines.
                $this->logger->warning('SuperAgent: the model rejected tool calling, answering without tools: '.$e->getMessage());
                $context['toolsMode'] = false;
                $context['loaded'] = $skills->resolve($this->available);
                $messages[0]['content'] = $this->prompt->build($skills, $agent, $context);

                return $this->client->call($baseUrl, $apiKey, (string) $agent->model, $this->temperature($agent), $maxTokens, $messages, [], $reasoning)['content'];
            }

            if ([] === $result['toolCalls']) {
                return $result['content'];
            }

            // What the model says while its tools run ("one moment, I am checking …")
            // reaches the visitor now, not at the end.
            $this->interim((string) ($result['content'] ?? ''));

            // The model's tool request goes into the history (role, content, tool
            // calls), otherwise the provider rejects the next message as incomplete.
            $messages[] = $result['raw'];
            $offered = array_map(static fn (array $tool): string => (string) $tool['function']['name'], $offer);
            $active = $this->activeTools($skills);
            $repeats = true;

            foreach ($result['toolCalls'] as $call) {
                $this->report($call);
                $repeats = $repeats && $this->repeatsLoad($call, $active);
                // Loading again what is loaded already only gets the hint to call it.
                $runs = \in_array($call['name'], $offered, true) || \in_array($call['name'], [ChatTools::LOAD_MODULE, ChatTools::LOAD_TOOLS], true);
                $content = $runs ? $this->execute($skills, $agent, $call) : 'This tool is not available in this step.';

                // Results of the connections stay for the next replies of this conversation.
                if ($runs && $this->mcp->has($call['name'])) {
                    $this->memory->record($call['name'], $call['arguments'], $content);
                }

                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $call['id'],
                    'name' => $call['name'],
                    'content' => $content,
                ];
            }

            // Some models load every tool once before its first call, although it is
            // offered already: such a step does not use up the steps of the reply.
            if ($repeats && $freeLoads < self::FREE_REPEAT_LOADS) {
                ++$freeLoads;
                --$step;
            }
        }

        return '';
    }

    /**
     * A call of load_tools or load_module for tools or skills that are loaded already.
     *
     * @param array{name: string, arguments: array<string, mixed>} $call
     * @param list<string>                                         $active tools offered in this step
     */
    private function repeatsLoad(array $call, array $active): bool
    {
        $names = static fn (string $key): array => array_map('strval', (array) ($call['arguments'][$key] ?? []));

        return match ($call['name']) {
            ChatTools::LOAD_TOOLS => [] === array_diff($names('tools'), $active),
            ChatTools::LOAD_MODULE => [] === array_diff($names('modules'), $this->loaded),
            default => false,
        };
    }

    /**
     * A text the model wrote together with a tool call: without marker lines, with
     * addresses defused like the answer, kept for the reply and sent at once.
     */
    private function interim(string $text): void
    {
        $text = trim((string) preg_replace('/\[\[\s*(PAGE|HANDOFF)\s*:.*?\]\]/isu', '', $text));

        if ('' === $text) {
            return;
        }

        $text = MailGuard::scrub($text);
        $this->interim[] = $text;

        if (null !== $this->say) {
            ($this->say)($text);
        }
    }

    /**
     * The step a tool call stands for, for the status line in the chat: loading or
     * using navigation = "pages", contact = "form", web search = "search".
     *
     * @param array{name: string, arguments: array<string, mixed>} $call
     */
    private function report(array $call): void
    {
        if (null === $this->progress) {
            return;
        }

        $names = 'load_module' === $call['name'] || 'load_tools' === $call['name']
            ? array_map('strval', array_merge((array) ($call['arguments']['modules'] ?? []), (array) ($call['arguments']['tools'] ?? [])))
            : [$call['name']];
        $step = 'thinking';

        // Only steps this website can do: without forwarding the contact skill only
        // points to the contact options, so the status stays "thinking".
        foreach ($names as $name) {
            $step = match (true) {
                \in_array($name, ['navigation', 'open_page', 'find_pages'], true) && $this->features->navigation => 'pages',
                \in_array($name, ['contact', 'request_handoff'], true) && $this->canForward => 'form',
                \in_array($name, ['web-search', 'web_search'], true) && $this->features->webSearch => 'search',
                default => $step,
            };
        }

        ($this->progress)($step);
    }

    /**
     * Tools for this step: load_module and load_tools while something is left to
     * load, and the tools of the loaded skills plus those loaded on their own.
     *
     * @return list<array<string, mixed>>
     */
    private function offer(SkillPackage $skills): array
    {
        $active = $this->activeTools($skills);
        $offer = [];
        $modules = array_values(array_diff($this->available, $this->loaded));
        $tools = array_values(array_diff($this->tools, $active));

        if ([] !== $modules) {
            $offer[] = ChatTools::loadModule($modules);
        }

        if ([] !== $tools) {
            $offer[] = ChatTools::loadTools($tools);
        }

        foreach ($active as $tool) {
            $offer[] = $this->mcp->has($tool) ? $this->mcp->definition($tool) : ChatTools::definition($tool);
        }

        return $offer;
    }

    /** @return list<string> */
    private function activeTools(SkillPackage $skills): array
    {
        $active = $this->loadedTools;

        foreach ($this->loaded as $id) {
            foreach ($skills->modules()[$id]['tools'] as $tool) {
                $active[] = $tool;
            }
        }

        return array_values(array_intersect($this->tools, array_unique($active)));
    }

    /** @param array{id: string, name: string, arguments: array<string, mixed>} $call */
    private function execute(SkillPackage $skills, AgentModel $agent, array $call): string
    {
        $args = $call['arguments'];

        if ($this->mcp->has($call['name'])) {
            return $this->mcp->call($call['name'], $args);
        }

        switch ($call['name']) {
            case ChatTools::LOAD_MODULE:
                $wanted = array_values(array_intersect(array_map('strval', (array) ($args['modules'] ?? [])), $this->available));
                $new = array_values(array_diff($skills->resolve($wanted), $this->loaded));
                $this->loaded = array_values(array_unique([...$this->loaded, ...$new]));

                return [] === $new
                    ? 'Nothing new to load: these skills are loaded already (or unknown). Call their tools directly as functions now.'
                    : 'Loaded skills: '.implode(', ', $new).'. Their rules are under LOADED SKILLS; their tools are now offered as functions: call them directly, do not load them again.';

            case ChatTools::LOAD_TOOLS:
                $wanted = array_values(array_intersect(array_map('strval', (array) ($args['tools'] ?? [])), $this->tools));
                $already = array_values(array_intersect($wanted, $this->activeTools($skills)));
                $withSkills = [];

                // A tool comes with the skill it belongs to, so its rules apply.
                foreach ($wanted as $tool) {
                    $skill = $skills->skillOfTool($tool);

                    if (null !== $skill && \in_array($skill, $this->available, true)) {
                        $withSkills[] = $skill;
                    }
                }

                $this->loaded = array_values(array_unique([...$this->loaded, ...$skills->resolve($withSkills)]));
                $this->loadedTools = array_values(array_unique([...$this->loadedTools, ...$wanted]));

                if ([] === $wanted) {
                    return 'Nothing loaded: unknown tools.';
                }

                // Models tend to load the same tool again instead of calling it.
                $new = array_values(array_diff($wanted, $already));

                return trim(([] !== $new ? 'Loaded tools: '.implode(', ', $new).'. ' : '').([] !== $already ? 'Already loaded: '.implode(', ', $already).'. ' : '').'They are offered as functions now: call them directly with their arguments, do not load them again.');

            case 'request_handoff':
                $this->handoff = trim(strip_tags((string) ($args['summary'] ?? ''))) ?: null;

                return 'The forwarding form is open below the chat, prefilled with the summary. Nothing has been sent. Its send button is "'.$this->submitLabel.'".';

            case 'open_page':
                if (!$this->openPage((int) ($args['page_id'] ?? 0), (bool) ($args['now'] ?? false))) {
                    return 'There is no page with this id.';
                }

                // The reply names the page that really opens: a page in place of a missing one is no answer.
                return ($this->navigate['now']
                    ? 'The page "'.$this->navigate['title'].'" opens right after your reply.'
                    : 'A button for the page "'.$this->navigate['title'].'" is shown below your reply.')
                    .' Call it by this title. If it is not the page the visitor asked for, call open_page again with the right page, or say that this website has no such page.';

            case 'find_pages':
                return $this->findPages((string) ($args['query'] ?? ''));

            case 'web_search':
                $found = $this->research->search($agent, (string) ($args['query'] ?? ''));
                // For the operator, without user content: how many and which approved sources were read.
                $this->logger->info(\sprintf('SuperAgent: web search ran — %d source(s) read%s', \count($found['sources']), [] !== $found['sources'] ? ': '.implode(', ', $found['sources']) : ''));

                return '' === $found['text']
                    ? 'SEARCH RESULT: no matches in the approved sources.'
                    : "SEARCH RESULT from the approved sources:\n\n".$found['text'];
        }

        return 'Unknown tool.';
    }

    /** @return list<string> */
    private function availableTools(AgentModel $agent): array
    {
        $tools = [];

        if ($this->canForward) {
            $tools[] = 'request_handoff';
        }

        if ([] !== $this->pages) {
            $tools[] = 'open_page';

            if (\count($this->pages) > self::LISTED_PAGES) {
                $tools[] = 'find_pages';
            }
        }

        if ($this->features->webSearch && $this->research->isEnabled($agent)) {
            $tools[] = 'web_search';
        }

        return $tools;
    }

    /** @return list<string> */
    private function availableSkills(AgentModel $agent, SkillPackage $skills): array
    {
        $available = [];

        foreach (array_keys($skills->modules()) as $id) {
            $on = match ($id) {
                'navigation' => [] !== $this->pages,
                'web-search' => \in_array('web_search', $this->tools, true),
                'referral' => '' !== trim((string) $agent->referral_company) || '' !== trim((string) $agent->preferred_solution),
                'medical' => '' !== (string) $agent->no_medical,
                'privacy' => '' !== (string) $agent->privacy_mode,
                default => true,
            };

            if ($on) {
                $available[] = $id;
            }
        }

        return $available;
    }

    /** Name of the function whose definition the AI service refused ("Invalid schema for function 'x'"), or null. */
    private static function refusedFunction(ClientExceptionInterface $e): ?string
    {
        try {
            $body = $e->getResponse()->getContent(false);
        } catch (\Throwable) {
            return null;
        }

        return 1 === preg_match("/function '([A-Za-z0-9_-]{1,64})'/", $body, $m) ? $m[1] : null;
    }

    /**
     * Reasoning effort and token limit. A model that reasons (Requesty model list)
     * gets the effort and room for its reasoning on top of the answer length;
     * others get neither.
     *
     * @return array{0: string, 1: int}
     */
    private function limits(AgentModel $agent): array
    {
        $answer = max(1, (int) ($agent->max_tokens ?: 800));
        $caps = $this->models->capabilities((string) $agent->model);

        if (true !== $caps['reasoning']) {
            return ['', $answer];
        }

        $effort = (string) ($agent->reasoning_effort ?: 'low');
        $reasoning = 'off' === $effort ? 'none' : $effort;
        $tokens = $answer + (self::REASONING_BUDGET[$reasoning] ?? self::REASONING_BUDGET['low']);

        return [$reasoning, null !== $caps['maxOutput'] ? min($tokens, $caps['maxOutput']) : $tokens];
    }

    private function temperature(AgentModel $agent): float
    {
        return max(0.0, min(2.0, (float) str_replace(',', '.', (string) $agent->temperature)));
    }

    /** A text of the language record (with %name% and %to%), else the default of the language. */
    private function label(AgentModel $agent, ?AgentLanguageModel $texts, string $key, string $locale): string
    {
        $params = [
            '%name%' => trim((string) $agent->assistant_name) ?: 'Assistant',
            '%to%' => trim((string) $agent->escalation_name) ?: $this->defaults->text('widget.team', $locale),
        ];
        $own = trim((string) ($texts?->{'ui_'.$key} ?? ''));

        return '' !== $own ? strtr($own, $params) : $this->defaults->text('widget.'.$key, $locale, $params);
    }

    /** Remembers a page of the index to open; false for an id outside the index. */
    private function openPage(int $id, bool $now): bool
    {
        if (!isset($this->pages[$id])) {
            return false;
        }

        $this->navigate = ['url' => $this->pages[$id]['url'], 'title' => $this->pages[$id]['title'], 'now' => $now];

        return true;
    }

    /** Search of the page index by words in title, path and description (for sites with many pages). */
    private function findPages(string $query): string
    {
        $words = array_filter(preg_split('/\W+/u', mb_strtolower($query)) ?: [], static fn (string $w): bool => mb_strlen($w) > 2);
        $hits = [];

        foreach ($this->pages as $id => $page) {
            $haystack = mb_strtolower($page['title'].' '.$page['path'].' '.$page['description']);
            $score = 0;

            foreach ($words as $word) {
                $score += substr_count($haystack, $word);
            }

            if ($score > 0) {
                $hits[$id] = $score;
            }
        }

        arsort($hits);
        $lines = [];

        foreach (\array_slice($hits, 0, 8, true) as $id => $score) {
            $lines[] = $id.' | '.$this->pages[$id]['title'].' | '.$this->pages[$id]['path'].('' !== $this->pages[$id]['description'] ? ' | '.$this->pages[$id]['description'] : '');
        }

        return [] === $lines ? 'No page of this website matches.' : "Pages (id | title | path | description):\n".implode("\n", $lines);
    }
}

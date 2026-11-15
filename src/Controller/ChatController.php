<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Neo\SuperAgentBundle\Chat\ChatHandler;
use Neo\SuperAgentBundle\Chat\ContactForm;
use Neo\SuperAgentBundle\Chat\ConversationMemory;
use Neo\SuperAgentBundle\Chat\Downloads;
use Neo\SuperAgentBundle\Chat\PersonalDataGuard;
use Neo\SuperAgentBundle\Chat\PhoneCodes;
use Neo\SuperAgentBundle\Newsletter\NewsletterSignup;
use Neo\SuperAgentBundle\Licence\FeatureResolver;
use Neo\SuperAgentBundle\Chat\ChatRateLimiter;
use Neo\SuperAgentBundle\Mail\EscalationMailer;
use Neo\SuperAgentBundle\Model\AgentModel;
use Neo\SuperAgentBundle\Security\PreviewAccess;
use Neo\SuperAgentBundle\Voice\Transcriber;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Front-end JSON endpoints for the chat widget:
 *  - POST /api/superagent/chat     -> the assistant's reply, or 503 when the AI service gives none
 *  - POST /api/superagent/escalate -> forward a visitor message to the team
 *  - POST /api/superagent/transcribe -> text of a voice recording (edition "pro"; in privacy mode filtered)
 *  - GET  /api/superagent/file/{token} -> a file a tool delivered in the chat, once (Downloads)
 *
 * The POST endpoints are rate-limited per client and validate/cap all input.
 * Input is never persisted or logged (no PII).
 */
final class ChatController extends AbstractController
{
    private const MAX_MESSAGE = 2000;

    /** An answer of the assistant in the history: with what it said while its tools ran (several offers). */
    private const MAX_ANSWER = 4000;
    private const MAX_ESCALATION = 4000;
    private const HISTORY_TURNS = 30;
    private const TRANSCRIPT_TURNS = 30;
    /** 60 seconds of compressed audio stay far below this. */
    private const MAX_AUDIO_BYTES = 4 * 1024 * 1024;
    private const AUDIO_TYPES = ['audio/webm', 'video/webm', 'audio/ogg', 'audio/mp4', 'audio/x-m4a', 'audio/aac', 'audio/mpeg', 'audio/wav', 'audio/x-wav'];

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly ChatHandler $handler,
        private readonly ChatRateLimiter $limiter,
        private readonly EscalationMailer $escalation,
        private readonly FeatureResolver $features,
        private readonly Transcriber $transcriber,
        private readonly PreviewAccess $preview,
        private readonly NewsletterSignup $newsletter,
        private readonly LoggerInterface $logger,
        private readonly PersonalDataGuard $guard,
        private readonly Downloads $downloads,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function chat(Request $request): Response
    {
        $this->framework->initialize();

        if (!$this->limiter->accept('chat-'.$request->getClientIp())) {
            return $this->json(['error' => 'rate_limited'], 429);
        }

        $payload = $this->payload($request);
        $message = $this->clean((string) ($payload['message'] ?? ''), self::MAX_MESSAGE);

        if ('' === $message) {
            return $this->json(['error' => 'empty_message'], 400);
        }

        $agent = $this->resolveAgent($payload);

        if (null === $agent) {
            return $this->json(['error' => 'no_agent'], 503);
        }

        // Without privacy mode, personal identifiers in the message go to the AI
        // service only after the visitor confirmed it (notice in the widget): the
        // first request with such data gets the kinds found and nothing else; the
        // AI service is not called, nothing is stored. In privacy mode the
        // handler removes them instead (ChatHandler).
        if ('' === (string) $agent->privacy_mode && true !== ($payload['confirmed'] ?? false)) {
            $found = array_values(array_unique($this->guard->redact($message)['types']));

            if ([] !== $found) {
                return $this->json(['confirm' => $found]);
            }
        }

        // What the visitor proved earlier in this conversation (guest check), signed by the server.
        $guest = mb_substr(\is_string($payload['guest'] ?? null) ? $payload['guest'] : '', 0, 4096);
        // Tool results and loaded skills of earlier replies, encrypted by the server (ConversationMemory).
        $memory = \is_string($payload['memory'] ?? null) && \strlen($payload['memory']) <= ConversationMemory::MAX_TOKEN ? $payload['memory'] : '';

        $args = [
            $agent,
            $message,
            $this->history($payload['history'] ?? null),
            $this->locale($request, $payload),
            $this->pageContext($payload),
            $this->features->for($request->getHost()),
        ];

        // The widget asks for a stream: what the assistant is doing goes out step by
        // step ("step"), the answer last ("done" or "error"). Hosting that buffers
        // delivers all events at the end; the widget handles both.
        if (str_contains((string) $request->headers->get('Accept'), 'text/event-stream')) {
            $locale = $args[3];

            return new StreamedResponse(function () use ($args, $guest, $memory, $locale): void {
                while (ob_get_level() > 0) {
                    ob_end_flush();
                }

                $this->events($args, $guest, $memory, $locale, static function (string $event, array $data): void {
                    echo 'event: '.$event."\n".'data: '.json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE)."\n\n";
                    flush();
                });
            }, 200, [
                'Content-Type' => 'text/event-stream; charset=utf-8',
                'Cache-Control' => 'no-cache, no-store',
                'X-Accel-Buffering' => 'no',
            ]);
        }

        $result = $this->withDownloads($this->handler->reply(...[...$args, null, $guest, null, $memory]), $args[3]);

        // No answer from the AI service: the widget shows its error notice; the
        // body still carries the message as sent, when privacy mode changed it.
        return $this->json($result, isset($result['error']) ? 503 : 200);
    }

    /**
     * A file a tool delivered in the chat: once, then it is gone (Downloads).
     * A used or expired link gets a short notice in the language of the chat.
     */
    public function download(Request $request, string $token): Response
    {
        $headers = ['Cache-Control' => 'no-store, private', 'X-Content-Type-Options' => 'nosniff', 'Referrer-Policy' => 'no-referrer', 'X-Robots-Tag' => 'noindex'];
        $file = $this->downloads->take($token);

        if (null === $file) {
            $locale = strtolower((string) strtok((string) $request->query->get('lang', ''), '-_'));
            $locale = 1 === preg_match('/^[a-z]{2,3}$/', $locale) ? $locale : 'en';

            return new Response($this->translator->trans('widget.file_gone', [], 'neo_superagent', $locale), 410, $headers + ['Content-Type' => 'text/plain; charset=utf-8']);
        }

        $fallback = preg_replace('/[^A-Za-z0-9._-]+/', '-', (string) iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $file['name'])) ?: 'document.pdf';

        return new Response($file['data'], 200, $headers + [
            'Content-Type' => $file['type'],
            'Content-Disposition' => HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, $file['name'], $fallback),
            'Content-Security-Policy' => "default-src 'none'; sandbox",
        ]);
    }

    public function escalate(Request $request): JsonResponse
    {
        $this->framework->initialize();

        if (!$this->limiter->accept('esc-'.$request->getClientIp())) {
            return $this->json(['error' => 'rate_limited'], 429);
        }

        $payload = $this->payload($request);
        $email = filter_var(trim((string) ($payload['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        $message = $this->clean((string) ($payload['message'] ?? ''), self::MAX_ESCALATION);

        if (false === $email || '' === $message) {
            return $this->json(['error' => 'invalid'], 400);
        }

        $agent = $this->resolveAgent($payload);

        if (null === $agent || '' === trim((string) $agent->escalation_email)) {
            return $this->json(['error' => 'no_escalation'], 503);
        }

        // The optional fields the assistant shows; required ones must be filled.
        $details = [];

        foreach (ContactForm::fields($agent) as $field) {
            $details[$field] = $this->clean((string) ($payload[$field] ?? ''), ContactForm::OPTIONAL[$field]);
        }

        $missing = array_values(array_filter(ContactForm::required($agent), static fn (string $f): bool => '' === $details[$f]));
        $consent = ContactForm::consent($agent);

        if ($consent && true !== ($payload['consent'] ?? false)) {
            $missing[] = 'consent';
        }

        if ([] !== $missing) {
            return $this->json(['error' => 'missing', 'fields' => $missing], 400);
        }

        // The widget sends the phone number in E.164 form (+436641234567).
        if ('' !== ($details['phone'] ?? '')) {
            $details['phone'] = (string) preg_replace(['/^00/', '/[\s\-\/().]/'], ['+', ''], $details['phone']);

            if (!PhoneCodes::isE164($details['phone'])) {
                return $this->json(['error' => 'invalid', 'fields' => ['phone']], 400);
            }
        }

        // A domain without mail server (MX) or address (A/AAAA) cannot receive the reply.
        if (!ContactForm::domainExists((string) substr((string) strrchr($email, '@'), 1))) {
            return $this->json(['error' => 'invalid', 'fields' => ['email']], 400);
        }

        // Ticked by the visitor, never preset; only when the assistant offers it.
        $newsletter = true === ($payload['newsletter'] ?? false) && $this->newsletter->available($agent);

        // Forwarding is part of the edition "basic" on; a changed widget does not unlock it.
        if (!$this->features->for($request->getHost())->contact) {
            return $this->json(['error' => 'licence'], 403);
        }

        try {
            // The chat so far goes along, so the team sees the context (sent by the
            // visitor's own action, never stored).
            $this->escalation->send($agent, $email, $message, $this->locale($request, $payload), $this->history($payload['transcript'] ?? null, self::TRANSCRIPT_TURNS), $details, $newsletter, $consent);
        } catch (\Throwable) {
            return $this->json(['error' => 'send_failed'], 502);
        }

        // The double opt-in starts after the request went out; a failure never undoes it.
        if ($newsletter) {
            try {
                if (!$this->newsletter->subscribe($agent, $email, $this->locale($request, $payload))) {
                    $this->logger->warning('SuperAgent: the newsletter sign-up from the chat could not be started.');
                }
            } catch (\Throwable $e) {
                $this->logger->warning('SuperAgent: newsletter sign-up from the chat failed: '.$e->getMessage());
            }
        }

        return $this->json(['ok' => true]);
    }

    /**
     * Voice input: the recording goes to the transcription model of the route and
     * the text comes back for the input field; the visitor checks and sends it.
     * Only with the edition "pro", with voice input switched on and privacy mode
     * off (a recording would reach the service unfiltered). Nothing is stored.
     */
    public function transcribe(Request $request): JsonResponse
    {
        $this->framework->initialize();

        if (!$this->limiter->accept('voice-'.$request->getClientIp())) {
            return $this->json(['error' => 'rate_limited'], 429);
        }

        $file = $request->files->get('audio');
        $mimeType = $file instanceof UploadedFile ? strtolower((string) strtok((string) $file->getClientMimeType(), ';')) : '';

        if (!$file instanceof UploadedFile || !$file->isValid() || $file->getSize() > self::MAX_AUDIO_BYTES || !\in_array($mimeType, self::AUDIO_TYPES, true)) {
            return $this->json(['error' => 'invalid'], 400);
        }

        $payload = $request->request->all();
        $agent = $this->resolveAgent($payload);

        $privacy = null !== $agent && '' !== (string) $agent->privacy_mode;

        // In privacy mode only with "Voice input also in privacy mode": the recording goes
        // to the transcription service as it is, the text is filtered below.
        if (null === $agent || '' === (string) $agent->voice_input || ($privacy && '' === (string) $agent->voice_in_privacy)) {
            return $this->json(['error' => 'disabled'], 403);
        }

        if (!$this->features->for($request->getHost())->voice) {
            return $this->json(['error' => 'licence'], 403);
        }

        $language = (string) strtok($this->locale($request, $payload), '-_');

        try {
            $text = $this->transcriber->transcribe(trim((string) $agent->transcription_model), $file->getPathname(), 'video/webm' === $mimeType ? 'audio/webm' : $mimeType, $language);
        } catch (\Throwable $e) {
            // Never the recording or its text, only the failure.
            $this->logger->warning('SuperAgent: transcription failed: '.$e->getMessage());

            return $this->json(['error' => 'unavailable'], 503);
        }

        $text = $this->clean($text, self::MAX_MESSAGE);

        // Privacy mode: the same filter as for a typed message (ChatHandler), before the
        // text reaches the browser and with it any message to the chat model.
        if ($privacy) {
            $redacted = $this->guard->redact($text);

            if ([] !== $redacted['types']) {
                $this->logger->info('SuperAgent: personal data removed from a transcript: '.implode(', ', array_unique($redacted['types'])));
                $text = $redacted['text'];
            }
        }

        return $this->json(['text' => $text]);
    }

    /**
     * Prefer the widget's declared language (page language) over the request
     * locale, which is unreliable for a bare API request.
     *
     * @param array<string, mixed> $payload
     */
    /**
     * The events of a streamed reply: each new step before a tool runs, what the
     * model says while its tools run ("say"), the answer last ("done", or "error"
     * when the AI service gave none).
     *
     * @param list<mixed>                                  $args arguments of ChatHandler::reply() up to the features
     * @param callable(string, array<string, mixed>): void $send
     */
    private function events(array $args, string $guest, string $memory, string $locale, callable $send): void
    {
        $last = '';
        $step = static function (string $name) use (&$last, $send): void {
            if ($name !== $last) {
                $last = $name;
                $send('step', ['step' => $name]);
            }
        };

        $say = static function (string $text) use ($send): void {
            $send('say', ['text' => $text]);
        };

        $step('thinking');
        $result = $this->withDownloads($this->handler->reply(...[...$args, $step, $guest, $say, $memory]), $locale);
        $send(isset($result['error']) ? 'error' : 'done', $result);
    }

    /**
     * The files of a reply as download addresses for the widget (the token
     * stays in the address, the language for the notice of a used link).
     *
     * @param array<string, mixed> $result
     *
     * @return array<string, mixed>
     */
    private function withDownloads(array $result, string $locale): array
    {
        if (empty($result['files'])) {
            return $result;
        }

        $result['files'] = array_map(fn (array $file): array => [
            'url' => $this->generateUrl('neo_superagent_file', ['token' => $file['token'], 'lang' => $locale]),
            'name' => $file['name'],
            'size' => $file['size'],
        ], $result['files']);

        return $result;
    }

    private function locale(Request $request, array $payload): string
    {
        $lang = strtolower(trim((string) ($payload['lang'] ?? '')));

        if (1 === preg_match('/^[a-z]{2,3}(?:[-_][a-z0-9]{2,8}){0,3}$/', $lang) && \strlen($lang) <= 32) {
            return $lang;
        }

        return $request->getLocale() ?: $request->getDefaultLocale();
    }

    /**
     * The agent the widget is bound to (validated + published); otherwise the first
     * published one. Keeps multi-agent setups correct without trusting the client id.
     *
     * @param array<string, mixed> $payload
     */
    private function resolveAgent(array $payload): ?AgentModel
    {
        $id = (int) ($payload['agent'] ?? 0);

        if ($id > 0) {
            $agent = AgentModel::findByPk($id);

            // "Preview only": for logged-in back-end administrators, never for visitors.
            if (null !== $agent && $agent->published && $this->preview->canSee($agent, false)) {
                return $agent;
            }
        }

        return AgentModel::findPublishedAgent($this->preview->adminViewing(false));
    }

    /**
     * The page the visitor is on (URL path + title) so the assistant can answer in
     * context. Capped and tag-stripped; never persisted or logged.
     *
     * @param array<string, mixed> $payload
     *
     * @return array{url: string, title: string}|null
     */
    private function pageContext(array $payload): ?array
    {
        $page = $payload['page'] ?? null;

        if (!\is_array($page)) {
            return null;
        }

        $url = $this->clean((string) ($page['url'] ?? ''), 300);
        $title = $this->clean((string) ($page['title'] ?? ''), 200);
        // Start page of the visitor's page: limits which pages the assistant may open.
        $root = max(0, (int) ($page['root'] ?? 0));

        if ('' === $url && '' === $title) {
            return null;
        }

        return ['url' => $url, 'title' => $title, 'root' => $root];
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request): array
    {
        $data = json_decode((string) $request->getContent(), true);

        return \is_array($data) ? $data : [];
    }

    private function clean(string $value, int $max): string
    {
        $value = trim(strip_tags($value));

        return mb_substr($value, 0, $max);
    }

    /**
     * @return list<array{role: string, content: string}>
     */
    private function history(mixed $raw, int $turns = self::HISTORY_TURNS): array
    {
        if (!\is_array($raw)) {
            return [];
        }

        $history = [];

        foreach (\array_slice($raw, -$turns) as $item) {
            if (!\is_array($item)) {
                continue;
            }

            $role = 'assistant' === ($item['role'] ?? '') ? 'assistant' : 'user';
            $content = $this->clean((string) ($item['content'] ?? ''), 'assistant' === $role ? self::MAX_ANSWER : self::MAX_MESSAGE);

            if ('' === $content) {
                continue;
            }

            $history[] = [
                'role' => $role,
                'content' => $content,
            ];
        }

        return $history;
    }
}

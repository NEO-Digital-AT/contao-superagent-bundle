<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\ModuleModel;
use Contao\PageModel;
use Neo\BasicBundle\Ai\AiConnection;
use Neo\BasicBundle\Dock\Dock;
use Neo\BasicBundle\License\CreditLine;
use Neo\SuperAgentBundle\Chat\ContactForm;
use Neo\SuperAgentBundle\Chat\PhoneCodes;
use Neo\SuperAgentBundle\Newsletter\NewsletterSignup;
use Neo\SuperAgentBundle\Chat\DefaultTexts;
use Neo\SuperAgentBundle\Chat\StarterQuestions;
use Neo\SuperAgentBundle\Licence\FeatureResolver;
use Neo\SuperAgentBundle\Model\AgentLanguageModel;
use Neo\SuperAgentBundle\Model\AgentModel;
use Neo\SuperAgentBundle\Security\PreviewAccess;
use Neo\SuperAgentBundle\SuperAgent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Renders the floating chat widget shell for the active assistant. The markup is
 * fully in the (overridable) Twig template; behaviour and the dynamic message
 * stream are driven by the bundle's vanilla-JS reading the same config blob.
 * Renders nothing when no assistant is published or no AI key is stored under
 * "Access keys and licences" (the chat could not answer; the back end says so).
 * An assistant in "preview only" is rendered only for back-end administrators in
 * the front-end preview.
 *
 * "proof" carries the signed certificate when the licence is valid for this
 * host; the shared credit script of the Basic bundle checks it again before it
 * leaves out the credit line.
 */
#[AsFrontendModule('neo_agent', category: 'neo', template: 'frontend_module/neo_agent')]
final class AgentModuleController extends AbstractFrontendModuleController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly CreditLine $credit,
        private readonly AiConnection $ai,
        private readonly FeatureResolver $featureResolver,
        private readonly Studio $studio,
        private readonly PreviewAccess $preview,
        private readonly NewsletterSignup $newsletter,
        private readonly ContentUrlGenerator $urls,
        // Buttons at the edge of the screen of NEO Basic; null with a Basic that does not have them yet.
        private readonly Dock|null $dock = null,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        if (!$this->ai->isConfigured()) {
            return new Response('');
        }

        // The module may pin a specific agent; otherwise the first published one.
        $agent = null;

        if ((int) $model->agent > 0) {
            $candidate = AgentModel::findByPk((int) $model->agent);

            if (null !== $candidate && $candidate->published && $this->preview->canSee($candidate, true)) {
                $agent = $candidate;
            }
        }

        $agent ??= AgentModel::findPublishedAgent($this->preview->adminViewing(true));

        if (null === $agent) {
            return new Response('');
        }

        // Multilingual sites: one module per language root, each pointing at its
        // own assistant (or sharing one with per-language records). The page
        // language drives the language record, the fixed widget texts
        // (translations/neo_superagent.<lang>.yaml) and the chat replies.
        $page = $request->attributes->get('pageModel');
        $lang = strtolower($page instanceof PageModel ? (string) $page->language : ($request->getLocale() ?: $request->getDefaultLocale())) ?: $request->getDefaultLocale();
        // Default texts: the page language when shipped (eleven languages), otherwise English.
        $locale = DefaultTexts::locale($lang);
        $features = $this->featureResolver->for($request->getHost());

        // Conversational texts for the current page language (fallback handled in the model).
        $texts = AgentLanguageModel::findForAgent((int) $agent->id, $lang);

        $name = trim((string) $agent->assistant_name) ?: 'Assistant';

        $cfg = [
            'name' => $name,
            // Record texts may use %name% like the suggestions they start from.
            'subtitle' => strtr((string) ($texts->subtitle ?? ''), ['%name%' => $name]),
            'greeting' => strtr(trim((string) ($texts->greeting ?? '')), ['%name%' => $name]) ?: $this->t('widget.greeting', ['%name%' => $name], $locale),
            'factNote' => strtr((string) ($texts->fact_note ?? ''), ['%name%' => $name]),
            'placeholder' => strtr(trim((string) ($texts->input_placeholder ?? '')), ['%name%' => $name]) ?: $this->t('widget.placeholder', [], $locale),
            // Empty = "No consent needed": the chat does not wait for a consent tool.
            'consent' => trim((string) $agent->consent_category),
            'gaEvent' => trim((string) $model->nsa_ga_event),
            // The starter questions as buttons below the greeting; their answers go to the model (CannedAnswers).
            'suggestions' => array_column(StarterQuestions::of($texts), 'question'),
            'canEscalate' => $features->contact && '' !== trim((string) $agent->escalation_email),
            // Forwarding form: optional fields of this assistant and the newsletter checkbox.
            'form' => [
                'fields' => ContactForm::fields($agent),
                'required' => ContactForm::required($agent),
                'newsletter' => $this->newsletter->available($agent),
                'consent' => ContactForm::consent($agent),
                // Phone field: preselected country, calling codes and trunk prefixes (the widget sends E.164).
                'phone' => \in_array('phone', ContactForm::fields($agent), true)
                    ? ['country' => PhoneCodes::defaultCountry((string) $agent->phone_country, $lang), 'dial' => PhoneCodes::DIAL, 'trunk' => PhoneCodes::TRUNK]
                    : null,
                'privacyUrl' => ContactForm::consent($agent) ? $this->absoluteUrl(PageModel::findPublishedById((int) $agent->privacy_page)) : '',
            ],
            'features' => $features->toArray(),
            'privacyMode' => '' !== (string) $agent->privacy_mode,
            // The privacy policy, linked in the notice before personal data goes to the AI service.
            'privacyPage' => '' === (string) $agent->privacy_mode ? $this->absoluteUrl(PageModel::findPublishedById((int) $agent->privacy_page)) : '',
            // Voice input: edition "pro", switched on, a transcription model; in privacy mode only
            // with "Voice input also in privacy mode" (the transcript is filtered, ChatController).
            'voice' => $features->voice && '' !== (string) $agent->voice_input && ('' === (string) $agent->privacy_mode || '' !== (string) $agent->voice_in_privacy) && '' !== trim((string) $agent->transcription_model),
            'avatar' => $this->avatarUrl($agent),
            'side' => 'left' === $agent->widget_side ? 'left' : 'right',
            // Start page of this page: the pages the assistant may open lie below it.
            'root' => $page instanceof PageModel ? (int) $page->rootId : 0,
            'lang' => $lang,
            'agent' => (int) $agent->id,
            'endpoints' => [
                'chat' => '/api/superagent/chat',
                'escalate' => '/api/superagent/escalate',
                'transcribe' => '/api/superagent/transcribe',
            ],
            // "removed" shows where privacy mode took personal data out of a message.
            'i18n' => $this->labels($name, $locale, trim((string) $agent->escalation_name), $texts, $features->contact && '' !== trim((string) $agent->escalation_email)) + ['removed' => $this->t('widget.removed', [], $locale)],
        ];

        $cfg['proof'] = $this->credit->proof(SuperAgent::LICENSE_MODULE, $request->getHost());

        $template->set('cfg', $cfg);
        // JSON_HEX_TAG neutralises any "</script>" that admin copy might contain.
        $template->set('cfgJson', json_encode($cfg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_THROW_ON_ERROR));

        // The chat button takes part in the buttons at the edge of the screen of NEO
        // Basic: with buttons of other extensions in its corner (cookie settings,
        // accessibility) it is arranged with them as set in the page layout.
        $this->dock?->register($page instanceof PageModel ? $page : null);

        return $template->getResponse();
    }

    /**
     * Fixed UI strings: the language record's own texts, else the catalogue
     * (translations/neo_superagent.*.yaml; without a file the installation default).
     *
     * @return array<string, string>
     */
    private function labels(string $name, string $locale, string $contact = '', AgentLanguageModel|null $texts = null, bool $canForward = true): array
    {
        // Who receives forwarded requests ("Forward to <name>"); without a name, the team.
        $to = '' !== $contact ? $contact : $this->t('widget.team', [], $locale);
        $p = ['%name%' => $name, '%to%' => $to];

        $out = [];

        foreach (AgentLanguageModel::UI_KEYS as $key) {
            // The text of this language's record first (any language works without
            // a translation file), the catalogue default when the field is empty.
            $own = trim((string) ($texts?->{'ui_'.$key} ?? ''));

            // An error text that mentions forwarding does not fit where there is none.
            if ('error' === $key && !$canForward && str_contains($own, '%to%')) {
                $own = '';
            }

            // Without a forwarding address the error text must not suggest forwarding.
            $default = 'error' === $key && !$canForward ? 'widget.error_plain' : 'widget.'.$key;
            $out[lcfirst(str_replace('_', '', ucwords($key, '_')))] = '' !== $own ? strtr($own, $p) : $this->t($default, $p, $locale);
        }

        return $out;
    }

    /**
     * The assistant's image, resized by Contao (96 × 96, under assets/images, so it
     * is served even from a folder that is not public); '' without one.
     */
    private function avatarUrl(AgentModel $agent): string
    {
        if (empty($agent->avatar)) {
            return '';
        }

        try {
            $figure = $this->studio->createFigureBuilder()->fromUuid($agent->avatar)->setSize([96, 96, 'crop'])->buildIfResourceExists();
        } catch (\Throwable) {
            return '';
        }

        $src = (string) $figure?->getImage()->getImageSrc();

        return '' === $src || str_starts_with($src, '/') || str_contains($src, '://') ? $src : '/'.$src;
    }

    /** @param array<string, string> $params */
    private function t(string $key, array $params, string $locale): string
    {
        return $this->translator->trans($key, $params, 'neo_superagent', $locale);
    }

    /** Absolute address of a page through the content URL generator (PageModel::getAbsoluteUrl() is deprecated). */
    private function absoluteUrl(PageModel|null $page): string
    {
        return null === $page ? '' : $this->urls->generate($page, [], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}

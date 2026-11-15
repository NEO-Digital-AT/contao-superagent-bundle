<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Newsletter;

use Contao\ContentModel;
use Contao\ArticleModel;
use Contao\Idna;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Neo\SuperAgentBundle\Model\AgentModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * The newsletter checkbox of the forwarding form. The assistant names a front-end
 * module whose settings are used, so nothing is configured twice:
 *
 *  - "brevo:<id>": a module of the Brevo extension (type brevo_newsletter): its
 *    lists, double-opt-in template and confirmation page; Brevo sends the
 *    confirmation e-mail.
 *  - "contao:<id>": a Contao module "Subscribe" (type subscribe): its channels and
 *    subscription e-mail text; the confirmation link leads to the page that shows
 *    this module, where Contao activates the subscription.
 *
 * Only what is installed is offered. A sign-up never blocks the forwarding.
 */
final class NewsletterSignup
{
    private const BREVO_CLIENT = 'Neo\BrevoNewsletterBundle\Brevo\BrevoClient';

    private const BREVO_KEYS = 'Neo\BrevoNewsletterBundle\Brevo\ApiKeyResolver';

    /**
     * Back-end choice: installed newsletter modules as "kind:id" => label.
     *
     * @return array<string, string>
     */
    public function options(): array
    {
        $options = [];

        foreach ($this->kinds() as $kind => $type) {
            foreach (ModuleModel::findBy(['type = ?'], [$type], ['order' => 'name']) ?? [] as $module) {
                $label = $GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_kinds'][$kind] ?? $kind;
                $options[$kind.':'.$module->id] = $label.': '.$module->name;
            }
        }

        return $options;
    }

    /** Why the chosen module cannot be used ('' when it can): for the note in the back end. */
    public function problem(AgentModel $agent): string
    {
        [$kind, $module] = $this->module($agent);

        if (null === $module) {
            return '' === (string) $agent->newsletter_module ? '' : 'missing';
        }

        if ('brevo' === $kind) {
            $lists = array_filter(array_map('intval', StringUtil::deserialize($module->brevo_lists, true)));

            if ([] === $lists || (int) $module->brevo_doi_template < 1 || null === PageModel::findByPk((int) $module->brevo_redirect)) {
                return 'brevo_incomplete';
            }

            return '' === System::getContainer()->get(self::BREVO_KEYS)->resolve() ? 'brevo_key' : '';
        }

        if ([] === array_filter(array_map('intval', StringUtil::deserialize($module->nl_channels, true)))) {
            return 'contao_channels';
        }

        return null === $this->pageOf($module) ? 'contao_page' : '';
    }

    /** Whether the form offers the checkbox. */
    public function available(AgentModel $agent): bool
    {
        return '' !== (string) $agent->newsletter_module && '' === $this->problem($agent);
    }

    /** Starts the double opt-in; false when it could not be started. */
    public function subscribe(AgentModel $agent, string $email, string $locale): bool
    {
        if (!$this->available($agent)) {
            return false;
        }

        [$kind, $module] = $this->module($agent);

        return 'brevo' === $kind ? $this->brevo($module, $email) : $this->contao($module, $email, $locale);
    }

    private function brevo(ModuleModel $module, string $email): bool
    {
        $container = System::getContainer();
        $lists = array_values(array_filter(array_map('intval', StringUtil::deserialize($module->brevo_lists, true))));
        $redirectPage = PageModel::findByPk((int) $module->brevo_redirect);
        $redirect = null === $redirectPage ? '' : self::absoluteUrl($redirectPage);
        $apiKey = $container->get(self::BREVO_KEYS)->resolve();

        if ('' === $apiKey || '' === $redirect) {
            return false;
        }

        return $container->get(self::BREVO_CLIENT)->createDoiContact($apiKey, $email, $lists, (int) $module->brevo_doi_template, $redirect);
    }

    /** The steps of Contao's module "Subscribe" (ModuleSubscribe::addRecipient), without its form. */
    private function contao(ModuleModel $module, string $email, string $locale): bool
    {
        $recipients = 'Contao\NewsletterRecipientsModel';
        $channels = 'Contao\NewsletterChannelModel';
        $page = $this->pageOf($module);
        $wanted = array_values(array_filter(array_map('intval', StringUtil::deserialize($module->nl_channels, true))));

        // Channels the address is already confirmed for need nothing.
        $active = [];

        foreach ($recipients::findBy(['email = ?', "active = '1'"], [$email]) ?? [] as $row) {
            $active[] = (int) $row->pid;
        }

        $new = array_values(array_diff($wanted, $active));

        if (null === $page || [] === $new) {
            return null !== $page;
        }

        if (null !== ($old = $recipients::findOldSubscriptionsByEmailAndPids($email, $new))) {
            foreach ($old as $row) {
                $row->delete();
            }
        }

        $related = [];
        $time = time();

        foreach ($new as $pid) {
            $row = new $recipients();
            $row->pid = $pid;
            $row->tstamp = $time;
            $row->email = $email;
            $row->active = false;
            $row->addedOn = $time;
            $row->save();
            $related['tl_newsletter_recipients'][] = $row->id;
        }

        $container = System::getContainer();
        $token = $container->get('contao.opt_in')->create('nl', $email, $related);
        $url = self::absoluteUrl($page);
        $host = (string) parse_url($url, PHP_URL_HOST);
        $titles = implode("\n", $channels::findByIds($new)?->fetchEach('title') ?? []);

        System::loadLanguageFile('default', $locale);

        $token->send(
            sprintf($GLOBALS['TL_LANG']['MSC']['nl_subject'] ?? '%s', Idna::decode($host)),
            $container->get('contao.string.simple_token_parser')->parse((string) $module->nl_subscribe, [
                'token' => $token->getIdentifier(),
                'domain' => Idna::decode($host),
                'link' => $url.(str_contains($url, '?') ? '&' : '?').'token='.$token->getIdentifier(),
                'channel' => $titles,
                'channels' => $titles,
            ]),
        );

        return true;
    }

    /**
     * The first published regular page that shows the module through a content
     * element "Module" (the confirmation link of Contao's opt-in leads there).
     */
    private function pageOf(ModuleModel $module): PageModel|null
    {
        foreach (ContentModel::findBy(['type = ?', 'module = ?', "invisible = ''", "ptable = 'tl_article'"], ['module', (int) $module->id]) ?? [] as $element) {
            $article = ArticleModel::findByPk((int) $element->pid);
            $page = null !== $article && $article->published ? PageModel::findPublishedById((int) $article->pid) : null;

            if (null !== $page && 'regular' === $page->type) {
                return $page;
            }
        }

        return null;
    }

    /** @return array{0: string, 1: ModuleModel|null} */
    private function module(AgentModel $agent): array
    {
        $value = (string) $agent->newsletter_module;

        if (!preg_match('/^(brevo|contao):(\d+)$/', $value, $m) || !isset($this->kinds()[$m[1]])) {
            return ['', null];
        }

        $module = ModuleModel::findByPk((int) $m[2]);

        return [$m[1], null !== $module && $this->kinds()[$m[1]] === $module->type ? $module : null];
    }

    /** @return array<string, string> kind => module type, only what is installed */
    private function kinds(): array
    {
        $kinds = [];

        if (class_exists(self::BREVO_CLIENT) && System::getContainer()->has(self::BREVO_CLIENT)) {
            $kinds['brevo'] = 'brevo_newsletter';
        }

        if (class_exists('Contao\NewsletterRecipientsModel')) {
            $kinds['contao'] = 'subscribe';
        }

        return $kinds;
    }

    /** Absolute address of a page through the content URL generator (PageModel::getAbsoluteUrl() is deprecated). */
    private static function absoluteUrl(PageModel $page): string
    {
        return System::getContainer()->get('contao.routing.content_url_generator')->generate($page, [], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}

<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Backend;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\DataContainer;
use Neo\BasicBundle\Access\AccessSettings;
use Neo\SuperAgentBundle\Licence\FeatureResolver;
use Neo\SuperAgentBundle\Model\AgentModel;
use Neo\SuperAgentBundle\Security\PreviewAccess;

/**
 * Settings of an assistant that have no effect because of another setting, a
 * missing key or the terms are disabled, with the reason at the field
 * (FieldLock); the stored values stay. Decided per record, so only in the
 * edit form of one assistant; the switches that change the outcome reload
 * the form (submitOnChange).
 */
final class DependencyCallbacks
{
    private const TABLE = 'tl_neo_agent';

    /** Fields of the forwarding form: without an escalation address there is no form. */
    private const FORWARDING = ['escalation_name', 'form_fields', 'form_required', 'phone_country', 'form_consent', 'privacy_page', 'newsletter_module'];

    public function __construct(
        private readonly FeatureResolver $features,
        private readonly AccessSettings $access,
        private readonly CookieConsentCheck $cookieConsent,
    ) {
    }

    /** onload_callback of tl_neo_agent. */
    public function lock(DataContainer|null $dc = null): void
    {
        $agent = null !== $dc && $dc->id ? AgentModel::findByPk($dc->id) : null;

        if (null === $agent) {
            return;
        }

        $lang = $GLOBALS['TL_LANG'][self::TABLE] ?? [];
        $privacy = '' !== (string) $agent->privacy_mode;

        if ($privacy && '' === (string) $agent->voice_in_privacy) {
            FieldLock::lock(self::TABLE, 'voice_input', $lang['voice_privacy_off'] ?? '');
            FieldLock::lock(self::TABLE, 'transcription_model', $lang['voice_privacy_off'] ?? '');
        }

        // Without privacy mode the switch for voice input in privacy mode has nothing to decide.
        if (!$privacy) {
            $this->hide('voice_in_privacy');
        }

        if ('' !== (string) $agent->web_search && 'serpapi' === (string) $agent->web_search_mode && !$this->access->hasValue('superagent_serpapi_key')) {
            // Another mode works without the key: the field stays editable.
            FieldLock::warn(self::TABLE, 'web_search_mode', $lang['serpapi_missing'] ?? '');
        }

        if ('' === trim((string) $agent->escalation_email)) {
            foreach (self::FORWARDING as $field) {
                FieldLock::lock(self::TABLE, $field, $lang['forwarding_locked'] ?? '');
            }
        }

        // A consent category the cookie consent extension never asks for: the chat could never start.
        // The field stays editable (another category, or "No consent needed", has an effect).
        if (false === $this->cookieConsent->offers((string) $agent->consent_category)) {
            FieldLock::warn(self::TABLE, 'consent_category', $this->cookieConsent->reason((string) $agent->consent_category));
        }

        // "All visitors" only after the terms; an assistant that is visible already stays so.
        if (!$this->features->canPublish() && PreviewAccess::PREVIEW === (string) $agent->visibility) {
            FieldLock::lock(self::TABLE, 'visibility', $lang['visibility_locked'] ?? '');
        }
    }

    private function hide(string $field): void
    {
        $manipulator = PaletteManipulator::create()->removeField($field);

        foreach (array_keys($GLOBALS['TL_DCA'][self::TABLE]['palettes'] ?? []) as $palette) {
            if ('__selector__' !== $palette) {
                $manipulator->applyToPalette($palette, self::TABLE);
            }
        }
    }
}

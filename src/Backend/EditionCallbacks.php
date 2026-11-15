<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Backend;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Neo\SuperAgentBundle\Licence\FeatureResolver;

/**
 * Settings of features that the licence does not include are not shown in the
 * form of an assistant: they are removed from the palette and the subpalettes
 * (Contao then neither shows nor saves them) and marked doNotShow, which also
 * keeps them out of "Edit multiple", "Override multiple" and the details view.
 * Their stored values stay untouched and the settings appear again with a
 * licence that includes them. What each edition adds is said once, in the
 * licence row of "Access keys and licences".
 *
 * The licence decides, not the terms: settings of a licensed edition stay
 * visible while its terms are pending (PublishCallbacks::pendingMessage says
 * so). The back end may run under another address than the website, so the
 * domain check is skipped, as in the licence row.
 */
final class EditionCallbacks
{
    private const TABLE = 'tl_neo_agent';

    /**
     * Fields per feature (property of Features), from the palette and the
     * subpalettes. Opening the contact page needs the navigation, which comes
     * with the same editions as the contact.
     *
     * @var array<string, list<string>>
     */
    private const FIELDS = [
        'webSearch' => ['web_search', 'web_search_mode', 'web_search_sources', 'web_search_max', 'web_search_key_info'],
        'contact' => ['escalation_email', 'escalation_name', 'contact_page', 'form_fields', 'form_required', 'phone_country', 'form_consent', 'privacy_page', 'newsletter_module', 'newsletter_note'],
        'voice' => ['voice_input', 'transcription_model', 'voice_in_privacy'],
        'skills' => ['skills'],
        'mcp' => ['mcp_tools', 'mcp_note'],
    ];

    /**
     * Buttons above the list that lead to settings of one feature only.
     *
     * @var array<string, string>
     */
    private const OPERATIONS = [
        'connections' => 'mcp',
        'skill_list' => 'skills',
    ];

    public function __construct(private readonly FeatureResolver $features)
    {
    }

    /** onload_callback of tl_neo_agent. */
    public function hideUnlicensed(): void
    {
        $licensed = $this->features->licensed('');
        $hidden = [];

        foreach (self::FIELDS as $feature => $fields) {
            if (!$licensed->{$feature}) {
                array_push($hidden, ...$fields);
            }
        }

        foreach (self::OPERATIONS as $operation => $feature) {
            if (!$licensed->{$feature}) {
                unset($GLOBALS['TL_DCA'][self::TABLE]['list']['global_operations'][$operation]);
            }
        }

        if ([] === $hidden) {
            return;
        }

        $manipulator = PaletteManipulator::create()->removeField($hidden);

        foreach (array_keys($GLOBALS['TL_DCA'][self::TABLE]['palettes']) as $palette) {
            if ('__selector__' !== $palette) {
                $manipulator->applyToPalette($palette, self::TABLE);
            }
        }

        foreach (array_keys($GLOBALS['TL_DCA'][self::TABLE]['subpalettes'] ?? []) as $subpalette) {
            $manipulator->applyToSubpalette($subpalette, self::TABLE);
        }

        foreach ($hidden as $field) {
            $GLOBALS['TL_DCA'][self::TABLE]['fields'][$field]['eval']['doNotShow'] = true;
        }
    }
}

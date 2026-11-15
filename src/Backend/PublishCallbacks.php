<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Backend;

use Contao\DataContainer;
use Contao\StringUtil;
use Contao\System;
use Neo\BasicBundle\Access\AccessPage;
use Neo\SuperAgentBundle\Licence\FeatureResolver;
use Neo\SuperAgentBundle\Security\PreviewAccess;

/**
 * Publishing and the terms in the back end: a new assistant starts in "preview
 * only" (visible to administrators in the front-end preview); "all visitors"
 * can be chosen once the terms are confirmed on "Access keys and licences".
 * Features of an edition stay locked until its terms are confirmed.
 */
final class PublishCallbacks
{
    public function __construct(private readonly FeatureResolver $features)
    {
    }

    /** save_callback of "visibility": "all visitors" only with confirmed terms. */
    public function checkVisibility(mixed $value, DataContainer $dc): string
    {
        $value = (string) $value;

        // An assistant that was already visible (before this setting existed) keeps that state.
        if (PreviewAccess::PREVIEW !== $value && 'public' !== (string) ($dc->getCurrentRecord()['visibility'] ?? '') && !$this->features->canPublish()) {
            throw new \RuntimeException($GLOBALS['TL_LANG']['tl_neo_agent']['visibility_locked'] ?? '');
        }

        return $value;
    }

    /**
     * Note below "visibility" while the terms are not confirmed. When the field is
     * disabled (DependencyCallbacks), the reason is at the field and the note
     * only leads to the terms.
     */
    public function note(DataContainer|null $dc = null): string
    {
        if ($this->features->canPublish()) {
            return '';
        }

        $locked = PreviewAccess::PREVIEW === (string) ($dc?->getCurrentRecord()['visibility'] ?? '');
        $text = $locked ? '' : StringUtil::specialchars($GLOBALS['TL_LANG']['tl_neo_agent']['visibility_note'] ?? '');

        if (AccessPage::canOpen()) {
            System::loadLanguageFile(AccessPage::TABLE);
            $text .= sprintf(
                ' <a href="%s">%s</a>',
                StringUtil::specialchars(AccessPage::url(AccessPage::TERMS_LEGEND)),
                StringUtil::specialchars($GLOBALS['TL_LANG'][AccessPage::TABLE]['terms_confirm_now'] ?? ''),
            );
        }

        $text = trim($text);

        if ('' === $text) {
            return '';
        }

        return '<div class="widget clr"><div class="tl_message"><p class="tl_info">'.$text.'</p></div></div>';
    }

    /** onload_callback: terms still to be confirmed, at the top of the page. */
    public function pendingMessage(): void
    {
        $passage = $this->features->pendingPassage();

        if (null === $passage) {
            return;
        }

        $lang = $GLOBALS['TL_LANG']['tl_neo_agent'];

        if ('all' === $passage) {
            AccessPage::addTermsMessage($lang['terms_missing'] ?? '');

            return;
        }

        System::loadLanguageFile('neo_license');
        AccessPage::addTermsMessage(sprintf($lang['terms_edition_missing'] ?? '%s', $GLOBALS['TL_LANG']['neo_license']['editions'][$passage] ?? $passage));
    }

    /**
     * label_callback of the list: marks assistants in "preview only".
     *
     * @param array<string, mixed> $row
     */
    public function label(array $row, string $label): string
    {
        if (PreviewAccess::PREVIEW !== (string) ($row['visibility'] ?? '')) {
            return $label;
        }

        return $label.' <span class="label-info" style="margin-left:6px">'.StringUtil::specialchars($GLOBALS['TL_LANG']['tl_neo_agent']['preview_badge'] ?? '').'</span>';
    }
}

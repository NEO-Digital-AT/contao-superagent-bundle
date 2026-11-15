<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Backend;

use Contao\DataContainer;
use Contao\StringUtil;
use Neo\SuperAgentBundle\Model\AgentModel;
use Neo\SuperAgentBundle\Newsletter\NewsletterSignup;

/**
 * The forwarding form in the back end: the newsletter modules to choose from and
 * a note when the chosen one cannot be used yet.
 */
final class FormCallbacks
{
    public function __construct(private readonly NewsletterSignup $newsletter)
    {
    }

    /** @return array<string, string> */
    public function newsletterOptions(): array
    {
        return $this->newsletter->options();
    }

    public function newsletterNote(DataContainer $dc): string
    {
        $agent = AgentModel::findByPk($dc->id);
        $problem = null !== $agent ? $this->newsletter->problem($agent) : '';
        $lang = $GLOBALS['TL_LANG']['tl_neo_agent'];

        if ('' !== $problem) {
            $text = $lang['newsletter_problem'][$problem] ?? $problem;
        } elseif ([] === $this->newsletter->options()) {
            $text = $lang['newsletter_none'] ?? '';
        } else {
            return '';
        }

        return sprintf('<div class="widget clr"><div class="tl_message"><p class="tl_info">%s</p></div></div>', StringUtil::specialchars($text));
    }
}

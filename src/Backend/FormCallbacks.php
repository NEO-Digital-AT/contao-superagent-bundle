<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Backend;

use Contao\DataContainer;
use Contao\StringUtil;
use Neo\SuperAgentBundle\Mail\SenderAddress;
use Neo\SuperAgentBundle\Model\AgentModel;
use Neo\SuperAgentBundle\Newsletter\NewsletterSignup;

/**
 * The forwarding form in the back end: the newsletter modules to choose from, a
 * note when the chosen one cannot be used yet, and a warning when forwarded
 * requests would have no sender.
 */
final class FormCallbacks
{
    public function __construct(
        private readonly NewsletterSignup $newsletter,
        private readonly SenderAddress $senders,
    ) {
    }

    /** Below the escalation address: the start points whose requests could not be sent (no sender address). */
    public function senderNote(DataContainer $dc): string
    {
        $agent = AgentModel::findByPk($dc->id);

        if (null === $agent || '' === trim((string) $agent->escalation_email)) {
            return '';
        }

        $missing = $this->senders->startPointsWithoutSender();

        if ([] === $missing) {
            return '';
        }

        $text = sprintf($GLOBALS['TL_LANG']['tl_neo_agent']['sender_missing'] ?? '%s', implode(', ', array_map(static fn (string $title): string => '„'.$title.'“', $missing)));

        return sprintf('<div class="widget clr"><div class="tl_message"><p class="tl_error">%s</p></div></div>', StringUtil::specialchars($text));
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

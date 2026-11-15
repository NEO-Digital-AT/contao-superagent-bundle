<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Mail;

use Neo\SuperAgentBundle\Chat\DefaultTexts;
use Neo\SuperAgentBundle\Model\AgentModel;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Sends a visitor's "forward to the team" request to the agent's escalation
 * address. The From is the host's configured sender (e.g. a Postmark-verified
 * MAILER_FROM) shown under the assistant's name; the visitor's address is set as
 * Reply-To so the team can answer directly. If no sender is configured, the
 * application's global default applies.
 */
final class EscalationMailer
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly TranslatorInterface $translator,
        private readonly string $fromEmail = '',
    ) {
    }

    /**
     * @param list<array{role: string, content: string}> $transcript the chat so far
     * @param array<string, string>                       $details    optional form fields (name, phone, company) => value
     * @param bool                                        $newsletter the visitor ticked the newsletter box (sign-up started)
     * @param bool                                        $consent    the visitor agreed to be contacted (required checkbox)
     */
    public function send(AgentModel $agent, string $visitorEmail, string $message, string $locale, array $transcript = [], array $details = [], bool $newsletter = false, bool $consent = false): void
    {
        $to = trim((string) $agent->escalation_email);

        if ('' === $to) {
            throw new \RuntimeException('No escalation address configured.');
        }

        $name = trim((string) $agent->assistant_name) ?: 'Assistant';
        // Wording from translations/neo_superagent.<lang>.yaml in the language of
        // the page the visitor wrote from; English for languages not shipped.
        $tl = DefaultTexts::locale($locale);
        $params = ['%name%' => $name, '%email%' => $visitorEmail, '%message%' => $message, '%lang%' => $locale];
        $subject = $this->translator->trans('mail.subject', $params, 'neo_superagent', $tl);
        $body = $this->translator->trans('mail.body', $params, 'neo_superagent', $tl);
        $extra = [];

        foreach ($details as $field => $value) {
            if ('' !== $value) {
                $extra[] = $this->translator->trans('mail.'.$field, [], 'neo_superagent', $tl).': '.$value;
            }
        }

        if ($consent) {
            $extra[] = $this->translator->trans('mail.consent', [], 'neo_superagent', $tl);
        }

        if ($newsletter) {
            $extra[] = $this->translator->trans('mail.newsletter', [], 'neo_superagent', $tl);
        }

        if ([] !== $extra) {
            $body = rtrim($body)."\n\n".implode("\n", $extra)."\n";
        }

        if ([] !== $transcript) {
            $visitor = $this->translator->trans('mail.visitor', [], 'neo_superagent', $tl);
            $lines = array_map(
                static fn (array $turn): string => ('assistant' === $turn['role'] ? $name : $visitor).': '.$turn['content'],
                $transcript,
            );
            $body = rtrim($body)."

— ".$this->translator->trans('mail.transcript', [], 'neo_superagent', $tl)." —

".implode("

", $lines)."
";
        }

        $email = (new Email())
            ->to($to)
            ->replyTo($visitorEmail)
            ->subject($subject)
            ->text($body);

        if ('' !== $this->fromEmail) {
            $email->from(new Address($this->fromEmail, $name));
        }

        $this->mailer->send($email);
    }
}

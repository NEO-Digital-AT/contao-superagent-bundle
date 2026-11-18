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
 * address. The From is the sender of SenderAddress (the configured MAILER_FROM,
 * else the e-mail address of the website or system administrator) shown under
 * the assistant's name; the visitor's address is set as Reply-To so the team can
 * answer directly. Without any sender nothing is sent and the visitor gets an
 * error, instead of a message the mailer would drop later.
 */
final class EscalationMailer
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly TranslatorInterface $translator,
        private readonly SenderAddress $senders,
    ) {
    }

    /**
     * @param list<array{role: string, content: string}> $transcript the chat so far
     * @param array<string, string>                       $details    optional form fields (name, phone, company) => value
     * @param bool                                        $newsletter the visitor ticked the newsletter box (sign-up started)
     * @param bool                                        $consent    the visitor agreed to be contacted (required checkbox)
     * @param int                                         $root       start point of the visitor's page (its administrator's address is the fallback sender)
     * @param string                                      $host       host the request came in on
     */
    public function send(AgentModel $agent, string $visitorEmail, string $message, string $locale, array $transcript = [], array $details = [], bool $newsletter = false, bool $consent = false, int $root = 0, string $host = ''): void
    {
        $to = trim((string) $agent->escalation_email);

        if ('' === $to) {
            throw new \RuntimeException('No escalation address configured.');
        }

        $from = $this->senders->resolve($root, $host);

        if ('' === $from) {
            throw new \RuntimeException('No sender address for forwarded requests: set MAILER_FROM or the e-mail address of the website or system administrator.');
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
            ->from(new Address($from, $name))
            ->to($to)
            ->replyTo($visitorEmail)
            ->subject($subject)
            ->text($body);

        $this->mailer->send($email);
    }
}

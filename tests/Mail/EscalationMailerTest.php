<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Mail;

use Neo\SuperAgentBundle\Mail\EscalationMailer;
use Neo\SuperAgentBundle\Model\AgentModel;
use Neo\SuperAgentBundle\Tests\Support\Models;
use Neo\SuperAgentBundle\Tests\Support\Translations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

#[CoversClass(EscalationMailer::class)]
final class EscalationMailerTest extends TestCase
{
    public function testTheTeamGetsTheRequestAndCanReplyToTheVisitor(): void
    {
        $email = $this->send('de-AT', [['role' => 'user', 'content' => 'Habt ihr offen?'], ['role' => 'assistant', 'content' => 'Ja, bis 18 Uhr.']], ['name' => 'Max Mustermann', 'phone' => '', 'company' => 'Muster GmbH'], true, true);

        $this->assertSame('team@example.com', $email->getTo()[0]->getAddress());
        $this->assertSame('max.mustermann@example.com', $email->getReplyTo()[0]->getAddress());
        $this->assertSame(['noreply@example.com', 'Bot'], [$email->getFrom()[0]->getAddress(), $email->getFrom()[0]->getName()]);

        $translator = Translations::translator();
        $t = static fn (string $id): string => $translator->trans($id, [], 'neo_superagent', 'de');
        $body = (string) $email->getTextBody();

        $this->assertStringContainsString('Bitte um Rückruf', $body);
        $this->assertStringContainsString($t('mail.name').': Max Mustermann', $body);
        $this->assertStringContainsString($t('mail.company').': Muster GmbH', $body);
        $this->assertStringNotContainsString($t('mail.phone').':', $body, 'empty fields are left out');
        $this->assertStringContainsString($t('mail.consent'), $body);
        $this->assertStringContainsString($t('mail.newsletter'), $body);
        $this->assertStringContainsString($t('mail.visitor').": Habt ihr offen?\n\nBot: Ja, bis 18 Uhr.", $body);
        $this->assertNotSame('mail.subject', $email->getSubject());
    }

    public function testALanguageThatIsNotShippedWritesEnglish(): void
    {
        $email = $this->send('pt-BR');
        $translator = Translations::translator();

        $this->assertSame($translator->trans('mail.subject', ['%name%' => 'Bot', '%email%' => 'max.mustermann@example.com', '%message%' => 'Bitte um Rückruf', '%lang%' => 'pt-BR'], 'neo_superagent', 'en'), $email->getSubject());
        $this->assertStringNotContainsString($translator->trans('mail.transcript', [], 'neo_superagent', 'en'), (string) $email->getTextBody(), 'no transcript sent');
    }

    public function testWithoutAddressNothingIsSent(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->never())->method('send');

        $this->expectException(\RuntimeException::class);
        (new EscalationMailer($mailer, Translations::translator()))->send(Models::make(AgentModel::class, ['escalation_email' => ' ']), 'max.mustermann@example.com', 'Text', 'en');
    }

    /**
     * @param list<array{role: string, content: string}> $transcript
     * @param array<string, string>                       $details
     */
    private function send(string $locale, array $transcript = [], array $details = [], bool $newsletter = false, bool $consent = false): Email
    {
        $sent = null;
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())->method('send')->willReturnCallback(static function (RawMessage $message) use (&$sent): void {
            $sent = $message;
        });

        $agent = Models::make(AgentModel::class, ['escalation_email' => ' team@example.com ', 'assistant_name' => 'Bot']);
        (new EscalationMailer($mailer, Translations::translator(), 'noreply@example.com'))->send($agent, 'max.mustermann@example.com', 'Bitte um Rückruf', $locale, $transcript, $details, $newsletter, $consent);

        $this->assertInstanceOf(Email::class, $sent);

        return $sent;
    }
}

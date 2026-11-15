<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Chat;

use Neo\SuperAgentBundle\Chat\ContactForm;
use Neo\SuperAgentBundle\Model\AgentModel;
use Neo\SuperAgentBundle\Tests\Support\Models;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContactForm::class)]
final class ContactFormTest extends TestCase
{
    public function testOnlyChosenFieldsAreShownAndRequired(): void
    {
        $agent = Models::make(AgentModel::class, ['form_fields' => serialize(['company', 'name', 'fax']), 'form_required' => serialize(['company', 'phone']), 'form_consent' => '1']);

        $this->assertSame(['name', 'company'], ContactForm::fields($agent), 'in the order of the form, unknown fields left out');
        $this->assertSame(['company'], ContactForm::required($agent), 'a field that is not shown is not required');
        $this->assertTrue(ContactForm::consent($agent));
    }

    public function testWithoutChoiceOnlyAddressAndMessage(): void
    {
        $agent = Models::make(AgentModel::class, ['form_fields' => null, 'form_required' => '', 'form_consent' => '']);

        $this->assertSame([], ContactForm::fields($agent));
        $this->assertSame([], ContactForm::required($agent));
        $this->assertFalse(ContactForm::consent($agent));
    }

    public function testADomainThatCannotExistReceivesNoMail(): void
    {
        $this->assertFalse(ContactForm::domainExists(''), 'PHP 8.4 and 8.5: idn_to_ascii() throws on an empty string');
        $this->assertFalse(ContactForm::domainExists(' . '));
        $this->assertFalse(ContactForm::domainExists('mail.invalid'), 'the top-level domain .invalid never resolves (RFC 2606)');
    }
}

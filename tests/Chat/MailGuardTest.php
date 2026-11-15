<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Chat;

use Neo\SuperAgentBundle\Chat\MailGuard;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/** The chat answer goes into the page as JSON, past the protection of the HTML: no address in plain text. */
#[CoversClass(MailGuard::class)]
final class MailGuardTest extends TestCase
{
    public function testAddressesAreDefused(): void
    {
        $this->assertSame('Write to office(at)example.com or max.mustermann+chat(at)sub.example.org.', MailGuard::scrub('Write to office@example.com or max.mustermann+chat@sub.example.org.'));
        $this->assertSame('Schreiben Sie an jürgen(at)müller.example', MailGuard::scrub('Schreiben Sie an jürgen@müller.example'));
    }

    public function testOtherTextStays(): void
    {
        $this->assertSame('Follow us @example and pay 5 @ 10 %', MailGuard::scrub('Follow us @example and pay 5 @ 10 %'));
        $this->assertSame('No address here.', MailGuard::scrub('No address here.'));
    }
}

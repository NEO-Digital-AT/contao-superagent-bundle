<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Mail;

use Doctrine\DBAL\Connection;
use Neo\SuperAgentBundle\Mail\SenderAddress;
use Neo\SuperAgentBundle\Tests\Support\TestDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SenderAddress::class)]
final class SenderAddressTest extends TestCase
{
    public function testTheConfiguredSenderComesFirst(): void
    {
        $this->assertSame('noreply@example.com', $this->senders('noreply@example.com', 'admin@example.com')->resolve(1, 'www.example.com'));
    }

    public function testThenTheWebsiteAdministratorOfTheStartPoint(): void
    {
        $this->assertSame('office@example.com', $this->senders('', 'admin@example.com')->resolve(1, 'www.example.com'));
        $this->assertSame('office@example.org', $this->senders('', 'admin@example.com')->resolve(2, 'www.example.org'), 'friendly format "Name [address]"');
    }

    public function testAStartPointOfAnotherDomainDoesNotCount(): void
    {
        // A request on www.example.com cannot send in the name of the website on www.example.org.
        $this->assertSame('admin@example.com', $this->senders('', 'admin@example.com')->resolve(2, 'www.example.com'));
    }

    public function testThenTheSystemAdministrator(): void
    {
        $this->assertSame('admin@example.com', $this->senders('', 'Admin <admin@example.com>')->resolve(3, 'www.example.net'));
        $this->assertSame('admin@example.com', $this->senders('', 'admin@example.com')->resolve(0, 'www.example.com'), 'no start point given');
    }

    public function testWithoutAnyAddressThereIsNoSender(): void
    {
        $this->assertSame('', $this->senders('', '')->resolve(3, 'www.example.net'));
        $this->assertSame('', $this->senders('not an address', 'neither')->resolve(3, 'www.example.net'));
    }

    public function testTheBackEndNamesTheStartPointsWithoutSender(): void
    {
        $this->assertSame(['Example Net'], $this->senders('', '')->startPointsWithoutSender());
        $this->assertSame([], $this->senders('', 'admin@example.com')->startPointsWithoutSender(), 'the system administrator covers every start point');
        $this->assertSame([], $this->senders('noreply@example.com', '')->startPointsWithoutSender());
    }

    private function senders(string $configured, string $systemAdmin): SenderAddress
    {
        return new SenderAddress($this->database(), $configured, static fn (): string => $systemAdmin);
    }

    private function database(): Connection
    {
        $db = TestDatabase::create();
        TestDatabase::addPages($db, [
            ['id' => 1, 'type' => 'root', 'title' => 'Example', 'dns' => 'www.example.com', 'adminEmail' => 'office@example.com', 'sorting' => 1],
            ['id' => 2, 'type' => 'root', 'title' => 'Example Org', 'dns' => 'www.example.org', 'adminEmail' => 'Example [office@example.org]', 'sorting' => 2],
            ['id' => 3, 'type' => 'root', 'title' => 'Example Net', 'dns' => '', 'adminEmail' => '', 'sorting' => 3],
            ['id' => 4, 'pid' => 1, 'type' => 'regular', 'title' => 'Contact', 'adminEmail' => ''],
        ]);

        return $db;
    }
}

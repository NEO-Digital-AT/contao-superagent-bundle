<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Chat;

use Neo\SuperAgentBundle\Chat\Downloads;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

#[CoversClass(Downloads::class)]
final class DownloadsTest extends TestCase
{
    public function testAFileIsHandedOutOnceUnderItsToken(): void
    {
        $downloads = new Downloads(new ArrayAdapter());
        $token = $downloads->put('INV-1.pdf', 'application/pdf', '%PDF-1.7');

        $this->assertMatchesRegularExpression('/^[0-9a-f]{48}$/', $token);
        $this->assertNotSame($token, $downloads->put('INV-1.pdf', 'application/pdf', '%PDF-1.7'), 'a new token each time');
        $this->assertSame(['name' => 'INV-1.pdf', 'type' => 'application/pdf', 'data' => '%PDF-1.7'], $downloads->take($token));
        $this->assertNull($downloads->take($token), 'once');
    }

    public function testUnknownAndMalformedTokensGiveNothing(): void
    {
        $downloads = new Downloads(new ArrayAdapter());

        $this->assertNull($downloads->take(str_repeat('a', 48)));
        $this->assertNull($downloads->take('../../etc/passwd'));
        $this->assertNull($downloads->take(''));
    }

    public function testTheFileExpiresAfterFifteenMinutes(): void
    {
        $cache = new ArrayAdapter();
        $token = (new Downloads($cache))->put('a.pdf', 'application/pdf', '%PDF-');
        $item = $cache->getItem('nsa_file_'.$token);

        $this->assertTrue($item->isHit());
        $this->assertSame(900, Downloads::TTL);
        $item->expiresAt(new \DateTimeImmutable('-1 second'));
        $cache->save($item);
        $this->assertNull((new Downloads($cache))->take($token));
    }
}

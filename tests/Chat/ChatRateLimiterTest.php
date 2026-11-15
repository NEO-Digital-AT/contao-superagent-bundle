<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Chat;

use Neo\SuperAgentBundle\Chat\ChatRateLimiter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

#[CoversClass(ChatRateLimiter::class)]
final class ChatRateLimiterTest extends TestCase
{
    public function testTwentyRequestsPerMinuteAndClient(): void
    {
        $limiter = new ChatRateLimiter(new ArrayAdapter());

        for ($i = 0; $i < 20; ++$i) {
            $this->assertTrue($limiter->accept('chat-203.0.113.7'));
        }

        $this->assertFalse($limiter->accept('chat-203.0.113.7'));
        $this->assertTrue($limiter->accept('chat-198.51.100.4'), 'another client');
        $this->assertTrue($limiter->accept('esc-203.0.113.7'), 'another endpoint');
    }
}

<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Chat;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\CacheStorage;

/**
 * Per-client sliding-window rate limit for the chat/escalation endpoints, backed
 * by the application cache. Self-contained so the bundle needs no host-side
 * framework.rate_limiter configuration.
 */
final class ChatRateLimiter
{
    private readonly RateLimiterFactory $factory;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->factory = new RateLimiterFactory(
            [
                'id' => 'neo_superagent',
                'policy' => 'sliding_window',
                'limit' => 20,
                'interval' => '1 minute',
            ],
            new CacheStorage($cache),
        );
    }

    public function accept(string $key): bool
    {
        return $this->factory->create($key)->consume(1)->isAccepted();
    }
}

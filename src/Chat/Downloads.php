<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Chat;

use Psr\Cache\CacheItemPoolInterface;

/**
 * Files a tool delivered in the chat (e.g. an invoice as PDF), for the visitor
 * to download: kept on the server for 15 minutes under a random token and
 * handed out once — the first download deletes it. The token is the only key;
 * it goes to the visitor's browser with the reply and nowhere else. The model
 * never gets the file.
 */
final class Downloads
{
    public const TTL = 900;

    private const PREFIX = 'nsa_file_';

    public function __construct(private readonly CacheItemPoolInterface $cache)
    {
    }

    /** Keeps a file; returns its token (48 hexadecimal characters). */
    public function put(string $name, string $type, string $data): string
    {
        $token = bin2hex(random_bytes(24));
        $item = $this->cache->getItem(self::PREFIX.$token);
        $item->set(['name' => $name, 'type' => $type, 'data' => $data]);
        $item->expiresAfter(self::TTL);
        $this->cache->save($item);

        return $token;
    }

    /**
     * The file of a token, deleted on the way out; null when the token is
     * unknown, used or expired.
     *
     * @return array{name: string, type: string, data: string}|null
     */
    public function take(string $token): ?array
    {
        if (1 !== preg_match('/^[0-9a-f]{48}$/', $token)) {
            return null;
        }

        $item = $this->cache->getItem(self::PREFIX.$token);

        if (!$item->isHit()) {
            return null;
        }

        $this->cache->deleteItem(self::PREFIX.$token);
        $file = $item->get();

        return \is_array($file) && \is_string($file['data'] ?? null) ? ['name' => (string) $file['name'], 'type' => (string) $file['type'], 'data' => $file['data']] : null;
    }
}

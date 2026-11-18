<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Chat;

use Neo\BasicBundle\Security\SecretCipher;

/**
 * What the assistant looked up and did earlier in this conversation: the results
 * of the connection tools (offers, a booking, a folio, a payment link) and the
 * skills and tools it had loaded. Each reply is a new request; without this the
 * model only sees the visible messages, asks for data again and repeats look-ups.
 *
 * Nothing is stored on the server: the memory travels with the conversation as
 * a token, encrypted and authenticated with the application secret
 * (SecretCipher), like the token of the guest check. The widget keeps it in
 * sessionStorage and sends it with the next message; "New conversation" drops
 * it. The visitor can neither read nor change it. A token of another assistant
 * or one older than MAX_AGE counts as empty.
 */
final class ConversationMemory
{
    /** Scope of the key derivation (SecretCipher). */
    public const SCOPE = 'neo-superagent-memory';

    /** Longest token taken from the widget. */
    public const MAX_TOKEN = 65536;

    /** A conversation silent for longer starts without memory. */
    private const MAX_AGE = 7200;

    /** Tool results kept, the newest. */
    private const ENTRIES = 12;

    private const ARGUMENT_CHARS = 800;

    private const RESULT_CHARS = 4000;

    /** All results together, the newest first in. */
    private const TOTAL_CHARS = 24000;

    private int $agent = 0;

    /** @var list<array{tool: string, arguments: string, result: string}> */
    private array $entries = [];

    /** @var list<string> */
    private array $skills = [];

    /** @var list<string> */
    private array $tools = [];

    public function __construct(private readonly SecretCipher $cipher)
    {
    }

    public function load(int $agent, string $token): void
    {
        $this->agent = $agent;
        $this->entries = [];
        $this->skills = [];
        $this->tools = [];

        if ('' === $token || \strlen($token) > self::MAX_TOKEN) {
            return;
        }

        $data = json_decode(self::inflate($this->cipher->decrypt($token, self::SCOPE)), true);

        if (!\is_array($data) || ($data['agent'] ?? null) !== $agent || !\is_int($data['at'] ?? null) || $data['at'] < time() - self::MAX_AGE) {
            return;
        }

        foreach (\is_array($data['entries'] ?? null) ? $data['entries'] : [] as $entry) {
            if (\is_array($entry) && \is_string($entry['tool'] ?? null) && \is_string($entry['arguments'] ?? null) && \is_string($entry['result'] ?? null)) {
                $this->entries[] = ['tool' => $entry['tool'], 'arguments' => $entry['arguments'], 'result' => $entry['result']];
            }
        }

        $this->skills = self::names($data['skills'] ?? null);
        $this->tools = self::names($data['tools'] ?? null);
    }

    /**
     * A tool call of this reply and its result as the model got it (after the
     * checks and the masking of the connection).
     *
     * @param array<string, mixed> $arguments
     */
    public function record(string $tool, array $arguments, string $result): void
    {
        $this->entries[] = [
            'tool' => $tool,
            'arguments' => self::cut((string) json_encode($arguments, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE), self::ARGUMENT_CHARS),
            'result' => self::cut($result, self::RESULT_CHARS),
        ];
    }

    /**
     * Drops the results of the connection tools. Without the connections of the
     * licence (no licence, a lower edition, or a licence that fell back) nothing a
     * connection returned earlier reaches the model or the next token.
     */
    public function forgetResults(): void
    {
        $this->entries = [];
    }

    /**
     * Skills and tools loaded at the end of this reply: the next reply starts with them.
     *
     * @param list<string> $skills
     * @param list<string> $tools
     */
    public function remember(array $skills, array $tools): void
    {
        $this->skills = array_values(array_unique($skills));
        $this->tools = array_values(array_unique($tools));
    }

    /**
     * Results of earlier replies and of this one, oldest first.
     *
     * @return list<array{tool: string, arguments: string, result: string}>
     */
    public function entries(): array
    {
        $kept = [];
        $chars = 0;

        foreach (array_reverse(\array_slice($this->entries, -self::ENTRIES)) as $entry) {
            $chars += mb_strlen($entry['result']) + mb_strlen($entry['arguments']);

            if ([] !== $kept && $chars > self::TOTAL_CHARS) {
                break;
            }

            $kept[] = $entry;
        }

        return array_reverse($kept);
    }

    /** @return list<string> */
    public function skills(): array
    {
        return $this->skills;
    }

    /** @return list<string> */
    public function tools(): array
    {
        return $this->tools;
    }

    /** The token for the widget, '' when there is nothing to remember. */
    public function token(): string
    {
        $entries = $this->entries();

        if ([] === $entries && [] === $this->skills && [] === $this->tools) {
            return '';
        }

        $token = $this->encode($entries);

        // Too long for the next request: the oldest results go first.
        while (\strlen($token) > self::MAX_TOKEN && [] !== $entries) {
            array_shift($entries);
            $token = $this->encode($entries);
        }

        return $token;
    }

    /** @param list<array{tool: string, arguments: string, result: string}> $entries */
    private function encode(array $entries): string
    {
        $json = (string) json_encode([
            'agent' => $this->agent,
            'at' => time(),
            'entries' => $entries,
            'skills' => $this->skills,
            'tools' => $this->tools,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);

        return $this->cipher->encrypt(self::deflate($json), self::SCOPE);
    }

    /** @return list<string> */
    private static function names(mixed $raw): array
    {
        return \is_array($raw) ? array_values(array_filter($raw, static fn (mixed $name): bool => \is_string($name) && 1 === preg_match('/^[A-Za-z0-9_-]{1,64}$/', $name))) : [];
    }

    private static function cut(string $text, int $max): string
    {
        return mb_strlen($text) > $max ? mb_substr($text, 0, $max).' …[cut]' : $text;
    }

    /** Compressed when zlib is there ("z:"), plain otherwise ("j:"). */
    private static function deflate(string $json): string
    {
        $packed = \function_exists('gzdeflate') ? gzdeflate($json, 6) : false;

        return false !== $packed ? 'z:'.$packed : 'j:'.$json;
    }

    private static function inflate(string $plain): string
    {
        if (str_starts_with($plain, 'z:') && \function_exists('gzinflate')) {
            $json = @gzinflate(substr($plain, 2), 1048576);

            return false !== $json ? $json : '';
        }

        return str_starts_with($plain, 'j:') ? substr($plain, 2) : '';
    }
}

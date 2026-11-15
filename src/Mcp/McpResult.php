<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Mcp;

/**
 * Result of tools/call as text for the model. Text items are kept, structured
 * content is passed as JSON when there is no text, other items (images, audio,
 * embedded resources) are named, not passed. Long results are cut, and the
 * model is told so.
 *
 * Files: a PDF in the result (an embedded resource with a blob, a text item that
 * is the file in Base64, or a Base64 value inside a JSON text) is taken out of
 * the text and kept in $files; the text names it instead ("[file: …]"). The
 * model never gets the content of a file; the visitor can download it (Downloads).
 */
final class McpResult
{
    public const MAX_CHARS = 20000;

    /** Largest file taken from a result (decoded). */
    public const MAX_FILE = 10 * 1024 * 1024;

    /** Base64 of "%PDF-": how a PDF starts once encoded. */
    private const PDF_BASE64 = 'JVBERi0';

    /**
     * @param list<array{name: string, type: string, data: string}> $files
     */
    private function __construct(
        public readonly string $text,
        public readonly bool $isError,
        public readonly bool $truncated,
        public readonly array $files = [],
    ) {
    }

    /** @param array<string, mixed> $result the "result" of the JSON-RPC answer */
    public static function fromArray(array $result): self
    {
        $parts = [];
        $files = [];

        foreach ((array) ($result['content'] ?? []) as $item) {
            if (!\is_array($item)) {
                continue;
            }

            $type = (string) ($item['type'] ?? '');

            if ('text' === $type) {
                $parts[] = self::takeFiles((string) ($item['text'] ?? ''), $files);
            } elseif ('resource_link' === $type) {
                $parts[] = sprintf('[resource link: %s %s]', (string) ($item['name'] ?? ''), (string) ($item['uri'] ?? ''));
            } elseif ('resource' === $type && isset($item['resource']['text'])) {
                $parts[] = self::takeFiles((string) $item['resource']['text'], $files);
            } elseif ('resource' === $type && \is_string($item['resource']['blob'] ?? null) && null !== ($pdf = self::pdf($item['resource']['blob']))) {
                $parts[] = self::keep($files, $pdf, basename((string) parse_url((string) ($item['resource']['uri'] ?? ''), PHP_URL_PATH)));
            } elseif ('' !== $type) {
                $parts[] = sprintf('[%s content not passed on]', $type);
            }
        }

        if ([] === array_filter($parts, static fn (string $p): bool => '' !== trim($p)) && isset($result['structuredContent'])) {
            $parts = [(string) json_encode($result['structuredContent'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)];
        }

        $text = trim(implode("\n\n", $parts));
        $truncated = mb_strlen($text) > self::MAX_CHARS;

        if ($truncated) {
            $text = mb_substr($text, 0, self::MAX_CHARS)."\n\n[Result cut after ".self::MAX_CHARS.' characters. Ask the tool for less, e.g. a shorter period or a filter.]';
        }

        return new self($text, true === ($result['isError'] ?? false), $truncated, $files);
    }

    /**
     * The text without the files in it: a text that is a file becomes its
     * placeholder; in a JSON text every value that is a file does.
     *
     * @param list<array{name: string, type: string, data: string}> $files
     */
    private static function takeFiles(string $text, array &$files): string
    {
        $trimmed = trim($text);

        if (null !== ($pdf = self::pdf($trimmed))) {
            return self::keep($files, $pdf, '');
        }

        if (!str_contains($text, self::PDF_BASE64) || !\is_array($data = json_decode($trimmed, true))) {
            return $text;
        }

        $found = false;
        $walk = static function (array $node) use (&$walk, &$files, &$found): array {
            foreach ($node as $key => $value) {
                if (\is_array($value)) {
                    $node[$key] = $walk($value);
                } elseif (\is_string($value) && null !== ($pdf = self::pdf($value))) {
                    $name = '';

                    foreach (['fileName', 'filename', 'name'] as $field) {
                        if (\is_string($node[$field] ?? null) && str_ends_with(strtolower($node[$field]), '.pdf')) {
                            $name = $node[$field];
                        }
                    }

                    $node[$key] = self::keep($files, $pdf, $name);
                    $found = true;
                }
            }

            return $node;
        };
        $data = $walk($data);

        return $found ? (string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $text;
    }

    /** The PDF a value holds in Base64 (also as a data URL), or null. */
    private static function pdf(string $value): ?string
    {
        $value = preg_replace('#^data:application/pdf;base64,#i', '', $value) ?? '';

        if (!str_starts_with($value, self::PDF_BASE64) || \strlen($value) > self::MAX_FILE / 3 * 4 + 8) {
            return null;
        }

        $data = base64_decode(preg_replace('/\s+/', '', $value) ?? '', true);

        return \is_string($data) && str_starts_with($data, '%PDF-') ? $data : null;
    }

    /**
     * @param list<array{name: string, type: string, data: string}> $files
     *
     * @return string the placeholder in the text
     */
    private static function keep(array &$files, string $data, string $name): string
    {
        $name = trim((string) preg_replace('/[^\p{L}\p{N}._-]+/u', '-', $name), '-.');
        $files[] = ['name' => $name, 'type' => 'application/pdf', 'data' => $data];

        return sprintf('[file: %s, PDF, %d KB]', '' !== $name ? $name : 'document', max(1, (int) round(\strlen($data) / 1024)));
    }
}

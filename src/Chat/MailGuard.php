<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Chat;

/**
 * Keeps the assistant from delivering an e-mail address in plain text.
 *
 * The chat answers as JSON, and the frontend script writes it straight into the
 * page, past every response listener that protects the HTML of the pages. An
 * address the model quotes from its knowledge base would then be harvestable in
 * the document again. So it is defused here on the server: "name@example.com"
 * becomes "name(at)example.com".
 *
 * On the server, not by prompt: a model does not reliably obey a prohibition,
 * a replacement does.
 */
final class MailGuard
{
    private const PATTERN = '/[\p{L}\p{N}._%+\-]+@[\p{L}\p{N}.\-]+\.[\p{L}]{2,}/u';

    public static function scrub(string $text): string
    {
        if (!str_contains($text, '@')) {
            return $text;
        }

        return preg_replace_callback(
            self::PATTERN,
            static fn (array $m): string => str_replace('@', '(at)', $m[0]),
            $text,
        ) ?? $text;
    }
}

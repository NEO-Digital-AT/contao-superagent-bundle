<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Chat;

/**
 * Links to pages of the website in a reply, for editions without navigation:
 * the assistant does not link the website's pages on its own there. A link to a
 * page of this website stays only where its address is in the knowledge of the
 * assistant (llms.txt, text, file, questions & answers), so the model passes on
 * what the website says and never builds an address itself. Other links (other
 * websites, e-mail, phone) are left as they are.
 */
final class PageLinks
{
    private const LINK = '/\[((?:\\\\.|[^\\\\\]\n])+)\]\(\s*<?((?:[^\s()<>]|\([^\s()<>]*\))+)>?(?:\s+"[^"\n]*")?\s*\)/u';
    private const AUTOLINK = '/<(https?:\/\/[^\s<>]+)>/iu';
    private const URL = '/(?<![\w\/])https?:\/\/[^\s<]*[^\s<.,;:!?)\]\'"»“”*_]/iu';
    private const KNOWN = '/https?:\/\/[^\s<>()\[\]"\']+/iu';

    /**
     * The reply without links to pages of $host whose address is not in $knowledge;
     * such a link leaves its label, a bare address becomes text without "https://".
     */
    public static function limit(string $reply, string $host, string $knowledge): string
    {
        $host = self::host($host);

        if ('' === $host) {
            return $reply;
        }

        $known = self::known($knowledge, $host);
        $kept = [];
        // Links that stay are set aside, so that their addresses are not taken for bare addresses below.
        $keep = static function (string $link) use (&$kept): string {
            $kept[] = $link;

            return "\u{E000}".(\count($kept) - 1)."\u{E001}";
        };

        // [label](address): the label stays.
        $reply = (string) preg_replace_callback(self::LINK, static fn (array $m): string => self::allowed($m[2], $host, $known) ? $keep($m[0]) : $m[1], $reply);

        // <address> and bare addresses: the address stays readable, but not clickable.
        $reply = (string) preg_replace_callback(self::AUTOLINK, static fn (array $m): string => self::allowed($m[1], $host, $known) ? $keep($m[0]) : self::plain($m[1]), $reply);
        $reply = (string) preg_replace_callback(self::URL, static fn (array $m): string => self::allowed($m[0], $host, $known) ? $m[0] : self::plain($m[0]), $reply);

        return (string) preg_replace_callback("/\u{E000}(\\d+)\u{E001}/u", static fn (array $m): string => $kept[(int) $m[1]], $reply);
    }

    /** @param array<string, true> $known paths of $host in the knowledge */
    private static function allowed(string $url, string $host, array $known): bool
    {
        $path = self::pathOnHost($url, $host);

        return null === $path || isset($known[$path]);
    }

    /**
     * The normalised path of $url when it is a page of $host (a path like /contact,
     * or an address on that host); null for anything else.
     */
    private static function pathOnHost(string $url, string $host): string|null
    {
        $url = trim($url);

        if (1 === preg_match('#^/(?![/\\\\])#', $url)) {
            return self::path($url);
        }

        if (1 !== preg_match('#^https?://#i', $url)) {
            return null;
        }

        $parts = parse_url($url);

        if (!\is_array($parts) || self::host((string) ($parts['host'] ?? '')) !== $host) {
            return null;
        }

        return self::path(($parts['path'] ?? '/').(isset($parts['query']) ? '?'.$parts['query'] : ''));
    }

    /** @return array<string, true> */
    private static function known(string $knowledge, string $host): array
    {
        $known = [];

        if (preg_match_all(self::KNOWN, $knowledge, $matches)) {
            foreach ($matches[0] as $url) {
                $path = self::pathOnHost(rtrim($url, '.,;:!?*_'), $host);

                if (null !== $path) {
                    $known[$path] = true;
                }
            }
        }

        // Paths written without a domain (e.g. "/contact" in a text).
        if (preg_match_all('#(?<![\w.:/])/[A-Za-z0-9][^\s<>()\[\]"\']*#u', $knowledge, $matches)) {
            foreach ($matches[0] as $path) {
                $known[self::path(rtrim($path, '.,;:!?*_'))] = true;
            }
        }

        return $known;
    }

    private static function path(string $path): string
    {
        $path = strtolower(rawurldecode(explode('#', $path, 2)[0]));

        return '/' === $path ? $path : rtrim($path, '/');
    }

    private static function host(string $host): string
    {
        $host = strtolower(trim($host));

        return str_starts_with($host, 'www.') ? substr($host, 4) : $host;
    }

    private static function plain(string $url): string
    {
        return (string) preg_replace('#^https?://#i', '', $url);
    }
}

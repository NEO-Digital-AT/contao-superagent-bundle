<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Mail;

use Contao\Config;
use Doctrine\DBAL\Connection;

/**
 * The From address of forwarded requests, in the order Contao uses for its own
 * e-mails: the sender configured for the extension (MAILER_FROM or the
 * parameter neo_super_agent.from_email), else the e-mail address of the website
 * administrator at the start point of the page, else the one of the system
 * administrator (System › Settings). Without any of them a request is not sent:
 * the mailer would drop the message later without a sender, after the visitor
 * was told it went out.
 */
final class SenderAddress
{
    /** @var \Closure(): string */
    private readonly \Closure $systemAdmin;

    /**
     * @param (\Closure(): string)|null $systemAdmin the system administrator's address (tests); Contao's setting by default
     */
    public function __construct(
        private readonly Connection $db,
        private readonly string $configured = '',
        \Closure|null $systemAdmin = null,
    ) {
        $this->systemAdmin = $systemAdmin ?? static fn (): string => (string) (class_exists(Config::class) ? Config::get('adminEmail') : '');
    }

    /**
     * The sender for a request from the start point $root, reached under $host;
     * '' when there is none. A start point only counts on its own domain (or
     * without one), so a request cannot borrow the address of another website of
     * the installation.
     */
    public function resolve(int $root, string $host): string
    {
        $configured = self::address($this->configured);

        if ('' !== $configured) {
            return $configured;
        }

        if ($root > 0) {
            $row = $this->db->fetchAssociative("SELECT dns, adminEmail FROM tl_page WHERE id = ? AND type = 'root'", [$root]);

            if (false !== $row && ('' === (string) $row['dns'] || 0 === strcasecmp((string) $row['dns'], $host))) {
                $page = self::address((string) $row['adminEmail']);

                if ('' !== $page) {
                    return $page;
                }
            }
        }

        return self::address(($this->systemAdmin)());
    }

    /**
     * The titles of the start points whose requests would go out without a sender
     * (for the note in the back end); empty when every one has one.
     *
     * @return list<string>
     */
    public function startPointsWithoutSender(): array
    {
        if ('' !== self::address($this->configured) || '' !== self::address(($this->systemAdmin)())) {
            return [];
        }

        $titles = [];

        foreach ($this->db->fetchAllAssociative("SELECT title, adminEmail FROM tl_page WHERE type = 'root' ORDER BY sorting") as $row) {
            if ('' === self::address((string) $row['adminEmail'])) {
                $titles[] = (string) $row['title'];
            }
        }

        return $titles;
    }

    /** The address of "Name [address]", "Name <address>" or a bare address; '' when it is not valid. */
    private static function address(string $value): string
    {
        $value = trim($value);

        if (1 === preg_match('/[<\[]([^>\]]+)[>\]]\s*$/', $value, $match)) {
            $value = trim($match[1]);
        }

        return false !== filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : '';
    }
}

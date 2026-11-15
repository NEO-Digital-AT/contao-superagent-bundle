<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Mcp;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCronJob;
use Doctrine\DBAL\Connection;

/**
 * Log of the tool calls of the connections (tl_neo_agent_tool_log): which
 * assistant called which tool, when, how long it took and how it ended. No
 * arguments and no results, so no personal data. Entries older than 30 days
 * are removed daily.
 */
#[AsCronJob('daily')]
final class ToolLog
{
    public const TABLE = 'tl_neo_agent_tool_log';

    public const DAYS = 30;

    public const OK = 'ok';

    /** The server answered with an error result. */
    public const ERROR = 'error';

    /** No result: not reachable, credentials refused. */
    public const FAILED = 'failed';

    /** Not run: locked or not ticked. */
    public const REFUSED = 'refused';

    public function __construct(private readonly Connection $db)
    {
    }

    public function write(int $agent, int $connection, string $tool, string $status, int $durationMs, string $note = ''): void
    {
        try {
            $this->db->insert(self::TABLE, [
                'tstamp' => time(),
                'agent' => $agent,
                'connection' => $connection,
                'tool' => mb_substr($tool, 0, 128),
                'status' => $status,
                'note' => mb_substr($note, 0, 32),
                'duration' => max(0, $durationMs),
            ]);
        } catch (\Throwable) {
            // The table may not exist before the database update; the chat goes on.
        }
    }

    /** Daily cleanup (Contao cron). */
    public function __invoke(): void
    {
        $this->db->executeStatement('DELETE FROM '.self::TABLE.' WHERE tstamp < ?', [time() - self::DAYS * 86400]);
    }
}

<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Mcp;

use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Neo\BasicBundle\Security\SecretCipher;
use Neo\SuperAgentBundle\Model\AgentModel;
use Neo\SuperAgentBundle\Model\ConnectionModel;

/**
 * The connections to MCP servers (edition Plus): their credentials, the tools
 * last read from the server, which of them are locked, and which tools an
 * assistant may use.
 *
 * A tool is usable by an assistant when the assistant has it ticked, the
 * connection is confirmed, the tool is in the last list of the server and it
 * is not locked. This is checked again on every call (McpToolbox).
 */
final class Connections
{
    public const TABLE = 'tl_neo_agent_connection';

    public function __construct(
        private readonly Connection $db,
        private readonly SecretCipher $cipher,
        private readonly McpClient $client,
        private readonly OAuthFlow $oauth,
        private readonly TemplateCatalog $templates,
    ) {
    }

    /** The template the connection was made from, if it still exists. */
    public function template(ConnectionModel $connection): McpTemplate|null
    {
        return $this->templates->get((string) $connection->template);
    }

    /** Kind of a tool: the template's rule first, else what the server says. */
    public function kindOf(ConnectionModel $connection, McpTool $tool): string
    {
        return $this->template($connection)?->kind($tool) ?? $tool->kind();
    }

    /**
     * Whether a tool is locked when it first appears: by the template, or because
     * it is not read only and the template does not unlock it.
     */
    public function lockedByDefault(ConnectionModel $connection, McpTool $tool): bool
    {
        $template = $this->template($connection);

        if (true === $template?->isLocked($tool->name)) {
            return true;
        }

        return McpTool::READ !== $this->kindOf($connection, $tool) && true !== $template?->isUnlocked($tool->name);
    }

    /** Endpoint with the credentials of the connection (an OAuth token is refreshed when it runs out). */
    public function endpoint(ConnectionModel $connection, bool $refresh = false): McpEndpoint
    {
        $headers = [];

        switch ((string) $connection->auth) {
            case ConnectionModel::AUTH_KEY:
                $key = $this->secret((string) $connection->api_key);

                if ('' !== $key) {
                    $headers['Authorization'] = 'Bearer '.$key;
                }

                break;

            case ConnectionModel::AUTH_HEADERS:
                foreach ($this->headerRows($connection) as $row) {
                    $name = trim((string) ($row['key'] ?? ''));

                    if ('' !== $name && preg_match('/^[A-Za-z0-9-]{1,64}$/', $name)) {
                        $headers[$name] = $this->secret((string) ($row['value'] ?? ''));
                    }
                }

                break;

            case ConnectionModel::AUTH_OAUTH:
                $token = $this->oauth->accessToken($connection, $refresh);

                if ('' !== $token) {
                    $headers['Authorization'] = 'Bearer '.$token;
                }

                break;
        }

        return new McpEndpoint(trim((string) $connection->url), $headers);
    }

    /**
     * Reads the tools from the server and stores them. Tools seen for the first
     * time are locked unless they only read (by the template or the server)
     * and the template does not lock them.
     *
     * @return list<McpTool>
     */
    public function readTools(ConnectionModel $connection): array
    {
        try {
            $described = $this->client->describe($this->endpoint($connection));
        } catch (McpAuthRequiredException $e) {
            if (ConnectionModel::AUTH_OAUTH !== (string) $connection->auth || '' === $this->oauth->accessToken($connection)) {
                throw $e;
            }

            $described = $this->client->describe($this->endpoint($connection, true));
        }

        $before = $this->toolData($connection);
        $known = array_column($before['tools'], 'name');
        $locked = $this->locked($connection);
        $new = [];

        foreach ($described['tools'] as $tool) {
            if (\in_array($tool->name, $known, true)) {
                continue;
            }

            // A first read is no news; tools added later are marked "new".
            if ([] !== $known || 0 !== (int) ($before['read_at'] ?? 0)) {
                $new[] = $tool->name;
            }

            if ($this->lockedByDefault($connection, $tool)) {
                $locked[] = $tool->name;
            }
        }

        $data = [
            'read_at' => time(),
            'url' => trim((string) $connection->url),
            'server' => trim($described['server']['name'].' '.$described['server']['version']),
            'tools' => array_map(static fn (McpTool $tool): array => $tool->toArray(), $described['tools']),
            'new' => $new,
        ];

        $this->db->update(self::TABLE, [
            'tools' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'locked' => serialize(array_values(array_unique($locked))),
            'tstamp' => time(),
        ], ['id' => (int) $connection->id]);

        $connection->tools = (string) json_encode($data);
        $connection->locked = serialize(array_values(array_unique($locked)));

        return $described['tools'];
    }

    /** @return array{read_at: int, url: string, server: string, tools: list<array<string, mixed>>, new: list<string>} */
    public function toolData(ConnectionModel $connection): array
    {
        $data = json_decode((string) $connection->tools, true);
        $data = \is_array($data) ? $data : [];

        return [
            'read_at' => (int) ($data['read_at'] ?? 0),
            'url' => (string) ($data['url'] ?? ''),
            'server' => (string) ($data['server'] ?? ''),
            'tools' => array_values(array_filter((array) ($data['tools'] ?? []), 'is_array')),
            'new' => array_values(array_map('strval', (array) ($data['new'] ?? []))),
        ];
    }

    /** @return array<string, McpTool> name => tool, as last read */
    public function tools(ConnectionModel $connection): array
    {
        $tools = [];

        foreach ($this->toolData($connection)['tools'] as $data) {
            $tool = McpTool::fromArray($data);

            if (null !== $tool) {
                $tools[$tool->name] = $tool;
            }
        }

        return $tools;
    }

    /** @return list<string> */
    public function locked(ConnectionModel $connection): array
    {
        return array_values(array_map('strval', StringUtil::deserialize((string) $connection->locked, true)));
    }

    /** Confirmed by an administrator and the tools read from the current address: only then can assistants use it. */
    public function isActive(ConnectionModel $connection): bool
    {
        $data = $this->toolData($connection);

        return (int) $connection->confirmed_at > 0 && 0 !== $data['read_at'] && $data['url'] === trim((string) $connection->url);
    }

    /**
     * Tools an assistant may use now: ticked, of an active connection, still
     * listed by the server and not locked.
     *
     * @return list<array{connection: ConnectionModel, tool: McpTool}>
     */
    public function allowedFor(AgentModel $agent): array
    {
        $byConnection = [];

        foreach (StringUtil::deserialize((string) $agent->mcp_tools, true) as $value) {
            [$id, $name] = array_pad(explode(':', (string) $value, 2), 2, '');

            if ((int) $id > 0 && '' !== $name) {
                $byConnection[(int) $id][] = $name;
            }
        }

        $allowed = [];

        foreach ($byConnection as $id => $names) {
            $connection = ConnectionModel::findByPk($id);

            if (null === $connection || !$this->isActive($connection)) {
                continue;
            }

            $tools = $this->tools($connection);
            $locked = $this->locked($connection);

            foreach (array_unique($names) as $name) {
                if (isset($tools[$name]) && !\in_array($name, $locked, true)) {
                    $allowed[] = ['connection' => $connection, 'tool' => $tools[$name]];
                }
            }
        }

        return $allowed;
    }

    /** @return list<array{key?: string, value?: string}> */
    public function headerRows(ConnectionModel $connection): array
    {
        return array_values(array_filter(StringUtil::deserialize((string) $connection->headers, true), 'is_array'));
    }

    private function secret(string $stored): string
    {
        return trim(html_entity_decode($this->cipher->decrypt($stored), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}

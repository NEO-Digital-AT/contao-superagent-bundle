<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Mcp;

use Neo\SuperAgentBundle\Chat\Downloads;
use Neo\SuperAgentBundle\Chat\PersonalDataGuard;
use Neo\SuperAgentBundle\Model\AgentModel;
use Neo\SuperAgentBundle\Model\ConnectionModel;
use Psr\Log\LoggerInterface;

/**
 * The tools of the connections in one chat request: their names for the
 * catalogue, their definitions as the server delivers them, and the call.
 *
 * Names for the model: "mcp<connection id>_<tool name>", reduced to the
 * characters function names allow, at most 64. Whether a call may run is
 * checked again at the call (ticked, connection active, not locked), never by
 * hiding a definition. Every call is written to the tool log, without
 * arguments and results.
 *
 * Personal data per connection (field personal_data), in both directions —
 * the arguments to the service and the result to the AI service: "mask"
 * replaces e-mail addresses, phone numbers, IBANs, card numbers, social
 * security numbers and dates of birth with a marker; "block" does not run a
 * call with such data and does not pass on a result with it; "send" passes
 * everything. Names and addresses are not recognised.
 *
 * Connections whose template has a guest check (GuestCheck) bring the tool
 * verify_guest ("mcp<connection id>_verify_guest"), and their protected tools
 * run only for the reservation the visitor proved is theirs.
 *
 * Files in a result (a PDF, see McpResult) never go to the model: they are kept
 * for the visitor to download (Downloads), the model learns only that the file
 * is ready, and the reply carries the download (files()).
 */
final class McpToolbox
{
    /** @var array<string, array{connection: ConnectionModel, tool: McpTool}> function name => tool */
    private array $tools = [];

    /** @var array<string, true> function names of verify_guest (no tool of the server) */
    private array $checks = [];

    private int $agentId = 0;

    /** @var list<array{token: string, name: string, size: int}> files of this request for the visitor */
    private array $files = [];

    public function __construct(
        private readonly Connections $connections,
        private readonly McpClient $client,
        private readonly ToolLog $log,
        private readonly PersonalDataGuard $guard,
        private readonly LoggerInterface $logger,
        private readonly GuestCheck $guests,
        private readonly Downloads $downloads,
    ) {
    }

    /**
     * Collects the tools the assistant may use; none without the edition
     * (features->mcp). $guestToken: what the visitor proved earlier in this
     * conversation (GuestCheck::token()).
     */
    public function prepare(AgentModel $agent, bool $enabled, string $guestToken = ''): void
    {
        $this->tools = [];
        $this->files = [];
        $this->checks = [];
        $this->agentId = (int) $agent->id;
        // Without the connections of the licence, an earlier guest check counts for nothing.
        $this->guests->load($this->agentId, $enabled ? $guestToken : '');

        if (!$enabled) {
            return;
        }

        foreach ($this->connections->allowedFor($agent) as $entry) {
            $this->tools[self::functionName((int) $entry['connection']->id, $entry['tool']->name)] = $entry;

            if ([] !== (array) $this->connections->template($entry['connection'])?->guestCheck) {
                $check = self::functionName((int) $entry['connection']->id, GuestCheck::TOOL);
                $this->tools[$check] ??= ['connection' => $entry['connection'], 'tool' => new McpTool(GuestCheck::TOOL, '', 'Checks that the visitor is the guest of a reservation.', [])];
                $this->checks[$check] = true;
            }
        }
    }

    /** What the visitor proved, for the widget to send back; '' when nothing. */
    /**
     * Files the tools of this request delivered, ready for the visitor to download once.
     *
     * @return list<array{token: string, name: string, size: int}>
     */
    public function files(): array
    {
        return $this->files;
    }

    public function guestToken(): string
    {
        return $this->guests->token();
    }

    /** @return list<string> */
    public function names(): array
    {
        return array_keys($this->tools);
    }

    /**
     * Function names of the prepared tools whose name at the server is one of
     * these (a skill names tools as the server lists them).
     *
     * @param list<string> $serverNames
     *
     * @return list<string>
     */
    public function functionNamesFor(array $serverNames): array
    {
        $names = [];

        foreach ($this->tools as $function => $entry) {
            if (\in_array($entry['tool']->name, $serverNames, true)) {
                $names[] = $function;
            }
        }

        return $names;
    }

    public function has(string $name): bool
    {
        return isset($this->tools[$name]);
    }

    /** First sentence of the description, with the connection, for the catalogue. */
    public function summary(string $name): string
    {
        $entry = $this->tools[$name];
        $description = $entry['tool']->description ?: $entry['tool']->title ?: $entry['tool']->name;
        $first = preg_split('/(?<=[.!?])\s|\R/', trim($description), 2)[0] ?? $description;

        return sprintf('%s (%s)', mb_substr(trim($first), 0, 200), (string) $entry['connection']->name);
    }

    /** @return array<string, mixed> function definition for the provider */
    public function definition(string $name): array
    {
        $entry = $this->tools[$name];

        if (isset($this->checks[$name])) {
            return $this->guests->definition($name, (string) $entry['connection']->name);
        }

        $schema = self::schema($entry['tool']->inputSchema);
        unset($schema['$schema']);
        $schema['type'] = 'object';

        // An empty list of properties must go out as an object, not as [].
        if (empty($schema['properties'])) {
            $schema['properties'] = (object) [];
        }

        return [
            'type' => 'function',
            'function' => [
                'name' => $name,
                'description' => mb_substr(sprintf('[%s] %s', (string) $entry['connection']->name, $entry['tool']->description ?: $entry['tool']->title), 0, 1024),
                'parameters' => $schema,
            ],
        ];
    }

    /**
     * Calls the tool; the answer for the model is a fact: the result, the
     * error of the server, or why it did not run.
     *
     * @param array<string, mixed> $arguments
     */
    public function call(string $name, array $arguments): string
    {
        $entry = $this->tools[$name] ?? null;

        if (null === $entry) {
            return 'TOOL NOT RUN: this tool is not available to this assistant.';
        }

        $connection = ConnectionModel::findByPk((int) $entry['connection']->id);
        $tool = $entry['tool']->name;
        $check = isset($this->checks[$name]);

        // Locks and confirmation may have changed since the request started.
        if (null === $connection || !$this->connections->isActive($connection) || (!$check && \in_array($tool, $this->connections->locked($connection), true))) {
            $this->log->write($this->agentId, (int) $entry['connection']->id, $tool, ToolLog::REFUSED, 0, 'locked');

            return 'TOOL NOT RUN: an administrator has locked this tool or its connection.';
        }

        $template = $this->connections->template($connection);
        $guarded = null !== $template && [] !== $template->guestCheck && $this->guests->isProtected($template, $tool);

        // The guest check runs on the server; the model gets only its outcome.
        if ($check && null !== $template) {
            $started = microtime(true);
            $answer = $this->guests->verify($connection, $template, $arguments, fn (string $lookup, array $args): McpResult => $this->run($connection, $lookup, $args));
            $this->log->write($this->agentId, (int) $connection->id, $tool, ToolLog::OK, $this->ms($started), strtolower((string) strtok($answer, ':')));

            return $answer;
        }

        if ($guarded && null !== ($refusal = $this->guests->refuse($connection, $template, $arguments, array_map('strval', array_keys((array) ($entry['tool']->inputSchema['properties'] ?? [])))))) {
            $this->log->write($this->agentId, (int) $connection->id, $tool, ToolLog::REFUSED, 0, 'guest');

            return $refusal;
        }

        $mode = \in_array((string) $connection->personal_data, [ConnectionModel::DATA_BLOCK, ConnectionModel::DATA_SEND], true) ? (string) $connection->personal_data : ConnectionModel::DATA_MASK;
        $found = [];
        $arguments = $this->filter($arguments, $mode, $found);

        if (ConnectionModel::DATA_BLOCK === $mode && [] !== $found) {
            $this->log->write($this->agentId, (int) $connection->id, $tool, ToolLog::REFUSED, 0, 'personal');

            return 'TOOL NOT RUN: the arguments contain personal data ('.implode(', ', array_unique($found)).'), which this connection does not send. Do not retry with it.';
        }

        // Masked values would be stored by a tool that changes data (e.g. a booking
        // with "[removed: e-mail address]" as contact), so such a call does not run.
        if (ConnectionModel::DATA_MASK === $mode && [] !== $found && \in_array($this->connections->kindOf($connection, $entry['tool']), [McpTool::CHANGE, McpTool::DELETE], true)) {
            $this->log->write($this->agentId, (int) $connection->id, $tool, ToolLog::REFUSED, 0, 'personal');

            return 'TOOL NOT RUN: this connection masks personal data ('.implode(', ', array_unique($found)).'), so this tool would store placeholders instead of them. It cannot be done in this chat; offer the team (contact skill).';
        }

        $started = microtime(true);

        try {
            $result = $this->run($connection, $tool, $arguments);
        } catch (McpException $e) {
            $this->log->write($this->agentId, (int) $connection->id, $tool, ToolLog::FAILED, $this->ms($started), $e instanceof McpAuthRequiredException ? 'auth' : 'unreachable');
            $this->logger->warning(sprintf('SuperAgent: MCP tool %s of connection %d failed: %s', $tool, (int) $connection->id, $e->getMessage()));

            return 'TOOL FAILED (the call did not reach a result; this proves nothing about the data): '.$e->getMessage();
        }

        $text = $result->text;
        $note = ConnectionModel::DATA_MASK === $mode && [] !== $found ? 'masked' : ($result->truncated ? 'truncated' : '');

        // A record the visitor creates here (e.g. a new booking) counts as verified.
        if (null !== $template && [] !== $template->guestCheck && !$result->isError && $this->guests->isCreating($template, $tool)) {
            $this->guests->created($connection, $template, $text);
        }

        // A protected result is passed on only when it belongs to the verified reservation.
        // A file alone (no data besides it) came from a call that names only verified
        // records with arguments the tool knows (refuse above), so it is theirs.
        $fileOnly = [] !== $result->files && !\is_array(json_decode($text, true));

        if ($guarded && !$result->isError && !$fileOnly && null !== ($withheld = $this->guests->withhold($connection, $template, $text))) {
            $this->log->write($this->agentId, (int) $connection->id, $tool, ToolLog::OK, $this->ms($started), 'withheld');

            return $withheld;
        }

        if (ConnectionModel::DATA_SEND !== $mode) {
            $redacted = $this->guard->redact($text);

            if ([] !== $redacted['types']) {
                if (ConnectionModel::DATA_BLOCK === $mode) {
                    $this->log->write($this->agentId, (int) $connection->id, $tool, ToolLog::OK, $this->ms($started), 'withheld');

                    return 'TOOL RESULT WITHHELD: the result contains personal data ('.implode(', ', array_unique($redacted['types'])).'), which this connection does not pass on.';
                }

                $text = $redacted['text'];
                $note = 'masked';
            }
        }

        $this->log->write($this->agentId, (int) $connection->id, $tool, $result->isError ? ToolLog::ERROR : ToolLog::OK, $this->ms($started), $note);

        if ($result->isError) {
            return "TOOL ERROR from the server:\n".('' !== $text ? $text : 'no details');
        }

        return "TOOL RESULT:\n".('' !== $text ? $text : '(empty result)').$this->offer($result, $arguments);
    }

    /**
     * The files of a result for the visitor; for the model a note that they are
     * ready. Unnamed files are named after the record the call asked for.
     *
     * @param array<string, mixed> $arguments
     */
    private function offer(McpResult $result, array $arguments): string
    {
        if ([] === $result->files) {
            return '';
        }

        $fallback = '';

        foreach ($arguments as $value) {
            if (\is_string($value) && 1 === preg_match('/^[\p{L}\p{N}._-]{1,80}$/u', $value)) {
                $fallback = $value;
                break;
            }
        }

        $names = [];

        foreach ($result->files as $i => $file) {
            $name = '' !== $file['name'] ? $file['name'] : ('' !== $fallback ? $fallback : 'document').($i > 0 ? '-'.($i + 1) : '').'.pdf';
            $this->files[] = ['token' => $this->downloads->put($name, $file['type'], $file['data']), 'name' => $name, 'size' => \strlen($file['data'])];
            $names[] = '"'.$name.'"';
        }

        return "\n\nFILE READY: ".implode(', ', $names).'. The visitor gets a download button for it under your reply, valid once and for '.(Downloads::TTL / 60).' minutes. '
            .'Say that the file is ready below; do not write a link, and do not describe content you have not seen. If the visitor needs it again later, call the tool again.';
    }

    /**
     * One call of a tool of the connection; an OAuth token that ran out is
     * refreshed once.
     *
     * @param array<string, mixed> $arguments
     */
    private function run(ConnectionModel $connection, string $tool, array $arguments): McpResult
    {
        try {
            return $this->client->callTool($this->connections->endpoint($connection), $tool, $arguments);
        } catch (McpAuthRequiredException $e) {
            if (ConnectionModel::AUTH_OAUTH !== (string) $connection->auth) {
                throw $e;
            }

            return $this->client->callTool($this->connections->endpoint($connection, true), $tool, $arguments);
        }
    }

    /**
     * Arguments with personal data masked ("mask"), or unchanged; the types
     * found are collected either way.
     *
     * @param list<string> $found
     */
    private function filter(mixed $value, string $mode, array &$found): mixed
    {
        if (\is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->filter($item, $mode, $found);
            }

            return $value;
        }

        if (!\is_string($value) || ConnectionModel::DATA_SEND === $mode) {
            return $value;
        }

        $redacted = $this->guard->redact($value);
        array_push($found, ...$redacted['types']);

        return ConnectionModel::DATA_MASK === $mode ? $redacted['text'] : $value;
    }

    /** Function name of a tool: only letters, digits, "_" and "-", at most 64 characters. */
    /**
     * The schema as the server sent it: the stored copy is a PHP array, in which an
     * empty schema ({}, "any value") became []. AI services refuse [] as a schema
     * and with it the whole request, so every place that holds a schema gets its
     * empty object back.
     */
    public static function schema(mixed $schema): mixed
    {
        if (!\is_array($schema)) {
            return $schema;
        }

        if ([] === $schema) {
            return new \stdClass();
        }

        foreach (['properties', 'patternProperties', '$defs', 'definitions', 'dependentSchemas'] as $key) {
            if (isset($schema[$key]) && \is_array($schema[$key])) {
                $schema[$key] = [] === $schema[$key] ? new \stdClass() : array_map(self::schema(...), $schema[$key]);
            }
        }

        foreach (['anyOf', 'oneOf', 'allOf', 'prefixItems'] as $key) {
            if (isset($schema[$key]) && \is_array($schema[$key])) {
                $schema[$key] = array_map(self::schema(...), $schema[$key]);
            }
        }

        foreach (['items', 'additionalProperties', 'additionalItems', 'unevaluatedProperties', 'not', 'contains', 'propertyNames', 'if', 'then', 'else'] as $key) {
            if (\array_key_exists($key, $schema) && \is_array($schema[$key])) {
                // "items" may also be a list of schemas (one per position).
                $schema[$key] = [] !== $schema[$key] && array_is_list($schema[$key]) ? array_map(self::schema(...), $schema[$key]) : self::schema($schema[$key]);
            }
        }

        return $schema;
    }

    /**
     * The AI service refused the definition of this tool: it is left out for the
     * rest of the request and the refusal is logged, so the other tools keep working.
     */
    public function refused(string $function): void
    {
        $entry = $this->tools[$function] ?? null;

        if (null === $entry) {
            return;
        }

        unset($this->tools[$function], $this->checks[$function]);
        $this->log->write($this->agentId, (int) $entry['connection']->id, $entry['tool']->name, ToolLog::REFUSED, 0, 'schema');
        $this->logger->warning(sprintf('SuperAgent: the AI service refused the definition of MCP tool %s of connection %d; it is left out.', $entry['tool']->name, (int) $entry['connection']->id));
    }

    public static function functionName(int $connectionId, string $tool): string
    {
        $name = 'mcp'.$connectionId.'_'.(preg_replace('/[^A-Za-z0-9_-]/', '_', $tool) ?? '');

        return \strlen($name) <= 64 ? $name : substr($name, 0, 55).'_'.substr(hash('sha256', $tool), 0, 8);
    }

    private function ms(float $started): int
    {
        return (int) round((microtime(true) - $started) * 1000);
    }
}

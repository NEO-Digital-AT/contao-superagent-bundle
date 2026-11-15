<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Mcp;

use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Client for MCP servers over the "Streamable HTTP" transport (JSON-RPC 2.0 by
 * POST; the answer comes as JSON or as an event stream). Only the parts a
 * website chat needs: initialize, tools/list, tools/call. The client declares
 * no capabilities, so a server sends it no requests of its own (sampling,
 * elicitation).
 *
 * A session (header Mcp-Session-Id) is kept per endpoint for the current PHP
 * request; a server that forgot it (404) gets one new initialize. Only public
 * HTTPS addresses: the HTTP client of the service blocks private networks,
 * also after DNS resolution and redirects (NoPrivateNetworkHttpClient).
 */
final class McpClient
{
    public const PROTOCOL_VERSION = '2025-06-18';

    /** Largest answer read from a server. */
    private const MAX_BYTES = 4_000_000;

    /** Pages of tools/list read at most (a page has as many tools as the server likes). */
    private const MAX_PAGES = 20;

    /** @var array<string, array{session: string, version: string}> */
    private array $sessions = [];

    private int $nextId = 1;

    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    /**
     * Name and version the server reports, and the tools it lists.
     *
     * @return array{server: array{name: string, version: string}, instructions: string, tools: list<McpTool>}
     */
    public function describe(McpEndpoint $endpoint): array
    {
        $this->forget($endpoint);
        $init = $this->initialize($endpoint);
        $tools = [];
        $cursor = null;

        for ($page = 0; $page < self::MAX_PAGES; ++$page) {
            $result = $this->request($endpoint, 'tools/list', null === $cursor ? [] : ['cursor' => $cursor]);

            foreach ((array) ($result['tools'] ?? []) as $data) {
                $tool = \is_array($data) ? McpTool::fromArray($data) : null;

                if (null !== $tool) {
                    $tools[$tool->name] = $tool;
                }
            }

            $cursor = isset($result['nextCursor']) && '' !== (string) $result['nextCursor'] ? (string) $result['nextCursor'] : null;

            if (null === $cursor) {
                break;
            }
        }

        return [
            'server' => [
                'name' => (string) ($init['serverInfo']['name'] ?? ''),
                'version' => (string) ($init['serverInfo']['version'] ?? ''),
            ],
            'instructions' => (string) ($init['instructions'] ?? ''),
            'tools' => array_values($tools),
        ];
    }

    /** @param array<string, mixed> $arguments */
    public function callTool(McpEndpoint $endpoint, string $name, array $arguments): McpResult
    {
        $result = $this->request($endpoint, 'tools/call', ['name' => $name, 'arguments' => (object) $arguments]);

        return McpResult::fromArray($result);
    }

    /**
     * Sends a request and returns its "result". Initializes the session first
     * when needed.
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    private function request(McpEndpoint $endpoint, string $method, array $params, bool $retried = false): array
    {
        if (!isset($this->sessions[$endpoint->key()])) {
            $this->initialize($endpoint);
        }

        $id = $this->nextId++;
        $response = $this->post($endpoint, ['jsonrpc' => '2.0', 'id' => $id, 'method' => $method, 'params' => (object) $params]);
        $status = $response->getStatusCode();

        // The server ended the session: start a new one, once.
        if (404 === $status && '' !== $this->sessions[$endpoint->key()]['session'] && !$retried) {
            $this->forget($endpoint);

            return $this->request($endpoint, $method, $params, true);
        }

        return $this->result($response, $id, $method);
    }

    /** @return array<string, mixed> the result of initialize */
    private function initialize(McpEndpoint $endpoint): array
    {
        $id = $this->nextId++;
        $response = $this->post($endpoint, [
            'jsonrpc' => '2.0',
            'id' => $id,
            'method' => 'initialize',
            'params' => [
                'protocolVersion' => self::PROTOCOL_VERSION,
                'capabilities' => (object) [],
                'clientInfo' => ['name' => 'contao-superagent', 'version' => '1'],
            ],
        ], false);

        $result = $this->result($response, $id, 'initialize');
        $session = $response->getHeaders(false)['mcp-session-id'][0] ?? '';

        $this->sessions[$endpoint->key()] = [
            'session' => $session,
            'version' => (string) ($result['protocolVersion'] ?? self::PROTOCOL_VERSION),
        ];

        // Required by the protocol; the answer (202 or 200) carries nothing.
        try {
            $this->post($endpoint, ['jsonrpc' => '2.0', 'method' => 'notifications/initialized'])->getStatusCode();
        } catch (ExceptionInterface) {
        }

        return $result;
    }

    private function forget(McpEndpoint $endpoint): void
    {
        unset($this->sessions[$endpoint->key()]);
    }

    /** @param array<string, mixed> $message */
    private function post(McpEndpoint $endpoint, array $message, bool $inSession = true): ResponseInterface
    {
        if ('https' !== strtolower((string) parse_url($endpoint->url, PHP_URL_SCHEME))) {
            throw new McpException('Only HTTPS addresses are allowed for MCP servers.');
        }

        $headers = $endpoint->headers + [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json, text/event-stream',
        ];

        if ($inSession && isset($this->sessions[$endpoint->key()])) {
            $headers['MCP-Protocol-Version'] = $this->sessions[$endpoint->key()]['version'];

            if ('' !== $this->sessions[$endpoint->key()]['session']) {
                $headers['Mcp-Session-Id'] = $this->sessions[$endpoint->key()]['session'];
            }
        }

        try {
            return $this->httpClient->request('POST', $endpoint->url, [
                'headers' => $headers,
                'body' => json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            ]);
        } catch (ExceptionInterface $e) {
            throw new McpException('MCP server not reachable: '.$e->getMessage(), 0, $e);
        }
    }

    /** @return array<string, mixed> */
    private function result(ResponseInterface $response, int $id, string $method): array
    {
        try {
            $status = $response->getStatusCode();
            $headers = $response->getHeaders(false);

            if (401 === $status || 403 === $status) {
                [$metadata, $scope] = $this->challenge((string) ($headers['www-authenticate'][0] ?? ''));

                throw new McpAuthRequiredException(sprintf('The MCP server refused the credentials (HTTP %d).', $status), $metadata, $scope);
            }

            $body = $response->getContent(false);
        } catch (ExceptionInterface $e) {
            throw new McpException(sprintf('No answer from the MCP server to %s: %s', $method, $e->getMessage()), 0, $e);
        }

        if (\strlen($body) > self::MAX_BYTES) {
            throw new McpException(sprintf('The answer to %s is larger than %d bytes.', $method, self::MAX_BYTES));
        }

        if ($status >= 300) {
            throw new McpException(sprintf('The MCP server answered %s with HTTP %d: %s', $method, $status, $this->snippet($body)));
        }

        $type = strtolower((string) ($headers['content-type'][0] ?? ''));
        $messages = str_contains($type, 'text/event-stream') ? $this->events($body) : [json_decode($body, true)];

        foreach ($messages as $message) {
            if (!\is_array($message) || ($message['id'] ?? null) !== $id) {
                continue;
            }

            if (isset($message['error'])) {
                throw new McpException(sprintf('The MCP server answered %s with an error: %s', $method, $this->snippet((string) ($message['error']['message'] ?? json_encode($message['error'])))));
            }

            return \is_array($message['result'] ?? null) ? $message['result'] : [];
        }

        throw new McpException(sprintf('The MCP server sent no answer to %s.', $method));
    }

    /**
     * JSON messages of an event stream (the data lines of each event).
     *
     * @return list<mixed>
     */
    private function events(string $body): array
    {
        $messages = [];

        foreach (preg_split('/\r?\n\r?\n/', $body) ?: [] as $event) {
            $data = [];

            foreach (preg_split('/\r?\n/', $event) ?: [] as $line) {
                if (str_starts_with($line, 'data:')) {
                    $data[] = ltrim(substr($line, 5), ' ');
                }
            }

            if ([] !== $data) {
                $messages[] = json_decode(implode("\n", $data), true);
            }
        }

        return $messages;
    }

    /** @return array{0: string, 1: string} resource_metadata and scope of a Bearer challenge */
    private function challenge(string $header): array
    {
        preg_match('/resource_metadata="([^"]+)"/i', $header, $metadata);
        preg_match('/scope="([^"]*)"/i', $header, $scope);

        return [$metadata[1] ?? '', $scope[1] ?? ''];
    }

    private function snippet(string $text): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($text)) ?? '');

        return mb_strlen($text) > 300 ? mb_substr($text, 0, 300).' …' : $text;
    }
}

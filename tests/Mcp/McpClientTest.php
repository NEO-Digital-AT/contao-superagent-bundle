<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Mcp;

use Neo\SuperAgentBundle\Mcp\McpAuthRequiredException;
use Neo\SuperAgentBundle\Mcp\McpClient;
use Neo\SuperAgentBundle\Mcp\McpEndpoint;
use Neo\SuperAgentBundle\Mcp\McpException;
use Neo\SuperAgentBundle\Mcp\McpTool;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/** JSON-RPC over "Streamable HTTP" against a server played by MockHttpClient. */
#[CoversClass(McpClient::class)]
#[CoversClass(McpEndpoint::class)]
#[CoversClass(McpAuthRequiredException::class)]
final class McpClientTest extends TestCase
{
    /** @var list<array{method: string, id: int|null, headers: array<string, string>, params: mixed}> */
    private array $requests = [];

    public function testInitializeSessionAndPagesOfTools(): void
    {
        $client = $this->server(function (array $message): MockResponse {
            return match ($message['method']) {
                'initialize' => $this->answer($message, ['protocolVersion' => '2025-03-26', 'serverInfo' => ['name' => 'Shop', 'version' => '2.1'], 'instructions' => 'Use the order tools.'], ['mcp-session-id' => 'session-1']),
                'notifications/initialized' => new MockResponse('', ['http_code' => 202]),
                'tools/list' => isset($message['params']['cursor'])
                    ? $this->answer($message, ['tools' => [['name' => 'get_order', 'annotations' => ['readOnlyHint' => true]], ['name' => '']]])
                    : $this->answer($message, ['tools' => [['name' => 'list_orders', 'description' => 'Lists orders.']], 'nextCursor' => 'page-2']),
            };
        });

        $result = $client->describe(new McpEndpoint('https://mcp.example.com/mcp', ['Authorization' => 'Bearer token']));

        $this->assertSame(['name' => 'Shop', 'version' => '2.1'], $result['server']);
        $this->assertSame('Use the order tools.', $result['instructions']);
        $this->assertSame(['list_orders', 'get_order'], array_map(static fn (McpTool $t): string => $t->name, $result['tools']));
        $this->assertSame(['initialize', 'notifications/initialized', 'tools/list', 'tools/list'], array_column($this->requests, 'method'));

        // The client declares no capabilities, so the server sends it no requests of its own.
        $this->assertEquals(new \stdClass(), $this->requests[0]['params']->capabilities);
        $this->assertSame(McpClient::PROTOCOL_VERSION, $this->requests[0]['params']->protocolVersion);
        $this->assertArrayNotHasKey('mcp-session-id', $this->requests[0]['headers']);

        foreach ([1, 2, 3] as $i) {
            $this->assertSame('session-1', $this->requests[$i]['headers']['mcp-session-id']);
            $this->assertSame('2025-03-26', $this->requests[$i]['headers']['mcp-protocol-version'], 'the version the server chose');
            $this->assertSame('Bearer token', $this->requests[$i]['headers']['authorization']);
        }

        $this->assertSame(['cursor' => 'page-2'], (array) $this->requests[3]['params']);
    }

    public function testAToolCallAnsweredAsEventStream(): void
    {
        $client = $this->server(function (array $message): MockResponse {
            if ('tools/call' !== $message['method']) {
                return 'initialize' === $message['method'] ? $this->answer($message, []) : new MockResponse('', ['http_code' => 202]);
            }

            $result = json_encode(['jsonrpc' => '2.0', 'id' => $message['id'], 'result' => ['content' => [['type' => 'text', 'text' => 'Order 42: shipped']]]]);
            $other = json_encode(['jsonrpc' => '2.0', 'method' => 'notifications/progress', 'params' => ['progress' => 1]]);

            return new MockResponse("event: message\ndata: $other\n\nevent: message\ndata: $result\n\n", ['response_headers' => ['content-type' => 'text/event-stream']]);
        });

        $result = $client->callTool(new McpEndpoint('https://mcp.example.com/mcp'), 'get_order', ['id' => 42]);

        $this->assertSame('Order 42: shipped', $result->text);
        $this->assertFalse($result->isError);
        $this->assertSame(['name' => 'get_order', 'arguments' => ['id' => 42]], json_decode(json_encode($this->requests[2]['params']), true));
    }

    public function testAnExpiredSessionIsStartedAgainOnce(): void
    {
        $sessions = 0;
        $client = $this->server(function (array $message) use (&$sessions): MockResponse {
            if ('initialize' === $message['method']) {
                return $this->answer($message, [], ['mcp-session-id' => 'session-'.++$sessions]);
            }

            if ('tools/call' === $message['method'] && 1 === $sessions) {
                return new MockResponse('', ['http_code' => 404]);
            }

            return 'tools/call' === $message['method'] ? $this->answer($message, ['content' => [['type' => 'text', 'text' => 'ok']]]) : new MockResponse('', ['http_code' => 202]);
        });

        $this->assertSame('ok', $client->callTool(new McpEndpoint('https://mcp.example.com/mcp'), 'ping', [])->text);
        $this->assertSame(2, $sessions);
        $this->assertSame('session-2', $this->requests[count($this->requests) - 1]['headers']['mcp-session-id']);
    }

    public function testRefusedCredentialsNameTheAuthorizationServer(): void
    {
        $client = $this->server(static fn (): MockResponse => new MockResponse('', ['http_code' => 401, 'response_headers' => ['www-authenticate' => 'Bearer resource_metadata="https://mcp.example.com/.well-known/oauth-protected-resource", scope="orders.read"']]));

        try {
            $client->describe(new McpEndpoint('https://mcp.example.com/mcp'));
            $this->fail('No exception');
        } catch (McpAuthRequiredException $e) {
            $this->assertSame('https://mcp.example.com/.well-known/oauth-protected-resource', $e->resourceMetadata);
            $this->assertSame('orders.read', $e->scope);
            $this->assertSame('The MCP server refused the credentials (HTTP 401).', $e->getMessage());
        }
    }

    public function testOnlyHttpsAddresses(): void
    {
        $client = $this->server(static fn (): MockResponse => new MockResponse('{}'));

        $this->expectExceptionObject(new McpException('Only HTTPS addresses are allowed for MCP servers.'));

        try {
            $client->describe(new McpEndpoint('http://mcp.example.com/mcp'));
        } finally {
            $this->assertSame([], $this->requests, 'nothing was sent');
        }
    }

    public function testErrorsOfTheServer(): void
    {
        $endpoint = new McpEndpoint('https://mcp.example.com/mcp');

        $error = $this->server(fn (array $m): MockResponse => 'initialize' === $m['method'] ? $this->answer($m, []) : new MockResponse(json_encode(['jsonrpc' => '2.0', 'id' => $m['id'] ?? null, 'error' => ['code' => -32601, 'message' => 'Unknown tool']])));
        $this->assertThrows(fn () => $error->callTool($endpoint, 'x', []), 'The MCP server answered tools/call with an error: Unknown tool');

        $http = $this->server(fn (array $m): MockResponse => 'initialize' === $m['method'] ? $this->answer($m, []) : new MockResponse('<h1>Bad gateway</h1>', ['http_code' => 502]));
        $this->assertThrows(fn () => $http->callTool($endpoint, 'x', []), 'The MCP server answered tools/call with HTTP 502: Bad gateway');

        $none = $this->server(fn (array $m): MockResponse => 'initialize' === $m['method'] ? $this->answer($m, []) : new MockResponse(json_encode(['jsonrpc' => '2.0', 'id' => 999, 'result' => []])));
        $this->assertThrows(fn () => $none->callTool($endpoint, 'x', []), 'The MCP server sent no answer to tools/call.');

        $big = $this->server(fn (array $m): MockResponse => 'initialize' === $m['method'] ? $this->answer($m, []) : new MockResponse(str_repeat('x', 4_000_001)));
        $this->assertThrows(fn () => $big->callTool($endpoint, 'x', []), 'The answer to tools/call is larger than 4000000 bytes.');
    }

    public function testTheKeyOfAnEndpointDependsOnAddressAndHeaders(): void
    {
        $a = new McpEndpoint('https://mcp.example.com/mcp', ['X-Api-Key' => 'one']);

        $this->assertSame($a->key(), (new McpEndpoint('https://mcp.example.com/mcp', ['X-Api-Key' => 'one']))->key());
        $this->assertNotSame($a->key(), (new McpEndpoint('https://mcp.example.com/mcp', ['X-Api-Key' => 'two']))->key());
        $this->assertNotSame($a->key(), (new McpEndpoint('https://other.example.com/mcp', ['X-Api-Key' => 'one']))->key());
    }

    private function assertThrows(callable $call, string $message): void
    {
        try {
            $call();
        } catch (McpException $e) {
            $this->assertSame($message, $e->getMessage());

            return;
        }

        $this->fail('No exception: '.$message);
    }

    /** @param callable(array<string, mixed>): MockResponse $handler */
    private function server(callable $handler): McpClient
    {
        $this->requests = [];

        return new McpClient(new MockHttpClient(function (string $method, string $url, array $options) use ($handler): MockResponse {
            $message = json_decode($options['body'], true);
            $headers = [];

            foreach ($options['headers'] as $line) {
                [$name, $value] = explode(': ', $line, 2);
                $headers[strtolower($name)] = $value;
            }

            $this->requests[] = ['method' => $message['method'], 'id' => $message['id'] ?? null, 'headers' => $headers, 'params' => json_decode($options['body'])->params ?? null];

            return $handler($message);
        }));
    }

    /**
     * @param array<string, mixed>  $message
     * @param array<string, mixed>  $result
     * @param array<string, string> $headers
     */
    private function answer(array $message, array $result, array $headers = []): MockResponse
    {
        return new MockResponse(json_encode(['jsonrpc' => '2.0', 'id' => $message['id'], 'result' => (object) $result]), ['response_headers' => $headers + ['content-type' => 'application/json']]);
    }
}

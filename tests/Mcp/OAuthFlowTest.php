<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Mcp;

use Contao\Model\Registry;
use Doctrine\DBAL\Connection;
use Neo\BasicBundle\Security\SecretCipher;
use Neo\SuperAgentBundle\Mcp\Connections;
use Neo\SuperAgentBundle\Mcp\McpClient;
use Neo\SuperAgentBundle\Mcp\McpException;
use Neo\SuperAgentBundle\Mcp\OAuthFlow;
use Neo\SuperAgentBundle\Mcp\TemplateCatalog;
use Neo\SuperAgentBundle\Model\ConnectionModel;
use Neo\SuperAgentBundle\Tests\Support\Models;
use Neo\SuperAgentBundle\Tests\Support\TestDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/** Sign-in at an MCP server with OAuth 2.1: discovery, registration, PKCE, state, tokens. */
#[CoversClass(OAuthFlow::class)]
final class OAuthFlowTest extends TestCase
{
    private const MCP = 'https://mcp.example.com/mcp';
    private const REDIRECT = 'https://www.example.com/contao/superagent/oauth';

    private Connection $db;

    private SecretCipher $cipher;

    private Session $session;

    /** @var list<array{method: string, url: string, body: mixed}> */
    private array $requests = [];

    /** @var array<string, mixed> overrides of the answers of the services, by address */
    private array $answers = [];

    protected function setUp(): void
    {
        $this->db = TestDatabase::create(Connections::TABLE);
        $this->db->insert(Connections::TABLE, ['tstamp' => 1, 'name' => 'Shop', 'url' => self::MCP]);
        $this->cipher = new SecretCipher('test-secret');
        $this->session = new Session(new MockArraySessionStorage());
    }

    protected function tearDown(): void
    {
        Registry::getInstance()->reset();
    }

    public function testTheSignInIsFoundRegisteredAndProtectedByPkce(): void
    {
        $connection = $this->connection();
        $url = $this->flow()->start($connection, self::REDIRECT);

        $this->assertStringStartsWith('https://id.example.com/authorize?', $url);
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $pending = $this->session->get('neo_superagent_oauth');

        $this->assertSame(['response_type', 'client_id', 'redirect_uri', 'state', 'code_challenge', 'code_challenge_method', 'resource', 'scope'], array_keys($query));
        $this->assertSame(['code', 'client-1', self::REDIRECT, 'S256', self::MCP, 'orders.read'], [$query['response_type'], $query['client_id'], $query['redirect_uri'], $query['code_challenge_method'], $query['resource'], $query['scope']]);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $query['state']);
        $this->assertSame([$query['state']], array_keys($pending));

        // The challenge is the SHA-256 of the verifier that stays on the server.
        $verifier = $pending[$query['state']]['verifier'];
        $this->assertGreaterThanOrEqual(43, \strlen($verifier));
        $this->assertSame(rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '='), $query['code_challenge']);
        $this->assertStringNotContainsString($verifier, $url);

        // Client and endpoints are stored encrypted in the connection.
        $stored = (string) $this->db->fetchOne('SELECT oauth_data FROM '.Connections::TABLE.' WHERE id = 1');
        $this->assertSame($stored, $connection->oauth_data);
        $this->assertStringNotContainsString('client-1', $stored);
        $data = json_decode($this->cipher->decrypt($stored), true);
        $this->assertSame(['client-1', true, 'https://id.example.com/token'], [$data['client']['client_id'], $data['client']['registered'], $data['metadata']['token_endpoint']]);

        $registration = $this->requests[array_search('https://id.example.com/register', array_column($this->requests, 'url'), true)];
        $this->assertSame([self::REDIRECT], $registration['body']['redirect_uris']);
        $this->assertSame('none', $registration['body']['token_endpoint_auth_method']);
    }

    public function testTheCodeIsExchangedWithTheVerifierOnce(): void
    {
        $connection = $this->connection();
        parse_str((string) parse_url($this->flow()->start($connection, self::REDIRECT), PHP_URL_QUERY), $query);
        $verifier = $this->session->get('neo_superagent_oauth')[$query['state']]['verifier'];

        $this->assertSame($connection, $this->flow()->finish($query['state'], 'code-1', 'admin'));

        $token = $this->requests[\count($this->requests) - 1];
        $this->assertSame(['POST', 'https://id.example.com/token'], [$token['method'], $token['url']]);
        $this->assertSame(['grant_type' => 'authorization_code', 'code' => 'code-1', 'redirect_uri' => self::REDIRECT, 'code_verifier' => $verifier, 'client_id' => 'client-1', 'resource' => self::MCP], $token['body']);

        $status = $this->flow()->status($connection);
        $this->assertTrue($status['connected']);
        $this->assertSame('admin', $status['connected_by']);
        $this->assertSame('access-1', $this->flow()->accessToken($connection));

        $this->expectExceptionObject(new McpException('The sign-in is unknown or expired. Start it again with "Connect".'));
        $this->flow()->finish($query['state'], 'code-1', 'admin');
    }

    public function testAnUnknownOrExpiredStateIsRefused(): void
    {
        $this->session->set('neo_superagent_oauth', ['old' => ['id' => 1, 'verifier' => 'v', 'redirect' => self::REDIRECT, 'created' => time() - 901]]);

        foreach (['forged', 'old'] as $state) {
            try {
                $this->flow()->finish($state, 'code', 'admin');
                $this->fail('Accepted: '.$state);
            } catch (McpException $e) {
                $this->assertSame('The sign-in is unknown or expired. Start it again with "Connect".', $e->getMessage());
            }
        }

        $this->assertSame([], $this->session->get('neo_superagent_oauth'));
        $this->assertSame([], $this->requests, 'no token request');
    }

    public function testAnAuthorizationServerWithoutS256IsRefused(): void
    {
        $this->answers['https://id.example.com/.well-known/oauth-authorization-server'] = ['code_challenge_methods_supported' => ['plain']] + $this->serverMetadata();

        $this->expectExceptionObject(new McpException('The authorization server does not support PKCE with S256.'));
        $this->flow()->start($this->connection(), self::REDIRECT);
    }

    public function testEndpointsWithoutHttpsAreRefused(): void
    {
        $this->answers['https://id.example.com/.well-known/oauth-authorization-server'] = ['token_endpoint' => 'http://id.example.com/token'] + $this->serverMetadata();

        $this->expectExceptionObject(new McpException('The service names an address without HTTPS: http://id.example.com/token'));
        $this->flow()->start($this->connection(), self::REDIRECT);
    }

    public function testWithoutRegistrationAnOwnAppIsNeeded(): void
    {
        $metadata = $this->serverMetadata();
        unset($metadata['registration_endpoint']);
        $this->answers['https://id.example.com/.well-known/oauth-authorization-server'] = $metadata;

        $this->expectExceptionObject(new McpException('The service offers no automatic registration. Register an app there and enter its client ID (and secret).'));
        $this->flow()->start($this->connection(), self::REDIRECT);
    }

    public function testAnOwnAppSendsItsSecret(): void
    {
        $connection = $this->connection(['oauth_client_id' => 'own-app', 'oauth_client_secret' => $this->cipher->encrypt('app-secret')]);
        parse_str((string) parse_url($this->flow()->start($connection, self::REDIRECT), PHP_URL_QUERY), $query);

        $this->assertSame('own-app', $query['client_id']);
        $this->assertNotContains('https://id.example.com/register', array_column($this->requests, 'url'));

        $this->flow()->finish($query['state'], 'code-1', 'admin');
        $this->assertSame('app-secret', $this->requests[\count($this->requests) - 1]['body']['client_secret']);
    }

    public function testAnExpiredTokenIsRefreshedAndARefusedRefreshEndsTheSignIn(): void
    {
        $connection = $this->connection();
        $this->store($connection, ['access_token' => 'old', 'refresh_token' => 'refresh-1', 'expires_at' => time() + 30]);

        $this->assertSame('access-1', $this->flow()->accessToken($connection));
        $this->assertSame(['grant_type' => 'refresh_token', 'refresh_token' => 'refresh-1', 'client_id' => 'client-1', 'resource' => self::MCP], $this->requests[0]['body']);
        $this->assertSame('refresh-1', json_decode($this->cipher->decrypt((string) $connection->oauth_data), true)['token']['refresh_token'], 'kept when the service sends no new one');

        $this->answers['https://id.example.com/token'] = new MockResponse('{"error":"invalid_grant"}', ['http_code' => 400]);
        $this->store($connection, ['access_token' => 'old', 'refresh_token' => 'refresh-1', 'expires_at' => time() - 10]);
        $this->assertSame('', $this->flow()->accessToken($connection));

        $this->flow()->forget($connection);
        $this->assertSame('', $connection->oauth_data);
        $this->assertFalse($this->flow()->status($connection)['connected']);
    }

    /** @param array<string, mixed> $token */
    private function store(ConnectionModel $connection, array $token): void
    {
        $connection->oauth_data = $this->cipher->encrypt(json_encode(['metadata' => ['token_endpoint' => 'https://id.example.com/token', 'resource' => self::MCP], 'client' => ['client_id' => 'client-1'], 'token' => $token]));
    }

    /** @param array<string, mixed> $row */
    private function connection(array $row = []): ConnectionModel
    {
        Registry::getInstance()->reset();

        return Models::register(ConnectionModel::class, $row + ['id' => 1, 'url' => self::MCP, 'template' => '', 'oauth_data' => '', 'oauth_client_id' => '', 'oauth_client_secret' => '']);
    }

    private function flow(): OAuthFlow
    {
        $client = new MockHttpClient(function (string $method, string $url, array $options): MockResponse {
            $body = $options['body'] ?? '';
            $parsed = json_decode(\is_string($body) ? $body : '', true);

            if (!\is_array($parsed)) {
                parse_str(\is_string($body) ? $body : '', $parsed);
            }

            $this->requests[] = ['method' => $method, 'url' => $url, 'body' => $parsed];
            $answer = $this->answers[$url] ?? match ($url) {
                self::MCP => new MockResponse('', ['http_code' => 401, 'response_headers' => ['www-authenticate' => 'Bearer resource_metadata="https://mcp.example.com/.well-known/oauth-protected-resource/mcp"']]),
                'https://mcp.example.com/.well-known/oauth-protected-resource/mcp' => ['resource' => self::MCP, 'authorization_servers' => ['https://id.example.com'], 'scopes_supported' => ['orders.read']],
                'https://id.example.com/.well-known/oauth-authorization-server' => $this->serverMetadata(),
                'https://id.example.com/register' => ['client_id' => 'client-1'],
                'https://id.example.com/token' => ['access_token' => 'access-1', 'expires_in' => 3600],
                default => new MockResponse('', ['http_code' => 404]),
            };

            return $answer instanceof MockResponse ? $answer : new MockResponse(json_encode($answer), ['response_headers' => ['content-type' => 'application/json']]);
        });

        $stack = new RequestStack();
        $request = new Request();
        $request->setSession($this->session);
        $stack->push($request);

        return new OAuthFlow($client, new McpClient($client), $this->cipher, $this->db, $stack, new TemplateCatalog(sys_get_temp_dir().'/neo-no-project', new NullLogger()));
    }

    /** @return array<string, mixed> */
    private function serverMetadata(): array
    {
        return [
            'issuer' => 'https://id.example.com',
            'authorization_endpoint' => 'https://id.example.com/authorize',
            'token_endpoint' => 'https://id.example.com/token',
            'registration_endpoint' => 'https://id.example.com/register',
            'code_challenge_methods_supported' => ['S256'],
        ];
    }
}

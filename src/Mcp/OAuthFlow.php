<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Mcp;

use Doctrine\DBAL\Connection;
use Neo\BasicBundle\Security\SecretCipher;
use Neo\SuperAgentBundle\Model\ConnectionModel;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Sign-in at a MCP server with OAuth, as the MCP specification describes it:
 *
 *  1. The server answers 401 and names its protected-resource metadata
 *     (RFC 9728); that names the authorization server and the resource.
 *  2. The metadata of the authorization server (RFC 8414, or OpenID discovery)
 *     names the endpoints.
 *  3. The website registers itself as a client (RFC 7591), unless the
 *     administrator entered the client ID of an app registered by hand.
 *  4. The administrator signs in at the service (authorization code with PKCE,
 *     S256; the resource indicator of RFC 8707 is sent along).
 *  5. The callback exchanges the code for tokens; they are refreshed when they
 *     run out.
 *
 * A template can name the endpoints instead (no discovery), the scopes, that
 * an own app is required (no automatic registration), and that no resource
 * indicator is sent (authorization servers that reject it).
 *
 * Client, endpoints and tokens are stored encrypted in the connection
 * (oauth_data) and never reach the model.
 */
final class OAuthFlow
{
    /** Session key of the sign-ins in progress (state => connection, verifier, redirect address). */
    private const SESSION = 'neo_superagent_oauth';

    /** A sign-in in progress expires after this many seconds. */
    private const STATE_TTL = 900;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly McpClient $client,
        private readonly SecretCipher $cipher,
        private readonly Connection $db,
        private readonly RequestStack $requestStack,
        private readonly TemplateCatalog $templates,
    ) {
    }

    /** Whether the template of the connection requires an own app (client ID) that is not entered yet. */
    public function needsOwnApp(ConnectionModel $connection): bool
    {
        $template = $this->templates->get((string) $connection->template);

        return null !== $template && !$template->oauth['registration'] && '' === trim((string) $connection->oauth_client_id);
    }

    /** Address of the sign-in page of the service; the administrator is sent there. */
    public function start(ConnectionModel $connection, string $redirectUri): string
    {
        $data = $this->data($connection);
        $metadata = $this->discover($connection);
        $client = $this->client($connection, $data, $metadata, $redirectUri);

        $data = ['metadata' => $metadata, 'client' => $client] + $data;
        $this->store($connection, $data);

        $verifier = rtrim(strtr(base64_encode(random_bytes(48)), '+/', '-_'), '=');
        $state = bin2hex(random_bytes(16));
        $session = $this->requestStack->getSession();
        $pending = array_filter((array) $session->get(self::SESSION, []), static fn ($p): bool => \is_array($p) && ($p['created'] ?? 0) > time() - self::STATE_TTL);
        $pending[$state] = ['id' => (int) $connection->id, 'verifier' => $verifier, 'redirect' => $redirectUri, 'created' => time()];
        $session->set(self::SESSION, $pending);

        $query = [
            'response_type' => 'code',
            'client_id' => $client['client_id'],
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '='),
            'code_challenge_method' => 'S256',
        ];

        if ('' !== $metadata['resource']) {
            $query['resource'] = $metadata['resource'];
        }

        if ('' !== $metadata['scope']) {
            $query['scope'] = $metadata['scope'];
        }

        $endpoint = $metadata['authorization_endpoint'];

        return $endpoint.(str_contains($endpoint, '?') ? '&' : '?').http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Callback of the service: exchanges the code for tokens. Returns the
     * connection; throws when the state is unknown, expired or the service
     * refuses.
     */
    public function finish(string $state, string $code, string $username): ConnectionModel
    {
        $session = $this->requestStack->getSession();
        $pending = (array) $session->get(self::SESSION, []);
        $entry = $pending[$state] ?? null;
        unset($pending[$state]);
        $session->set(self::SESSION, $pending);

        if (!\is_array($entry) || ($entry['created'] ?? 0) < time() - self::STATE_TTL) {
            throw new McpException('The sign-in is unknown or expired. Start it again with "Connect".');
        }

        $connection = ConnectionModel::findByPk((int) $entry['id']);

        if (null === $connection || '' === $code) {
            throw new McpException('The sign-in returned no code.');
        }

        $data = $this->data($connection);

        $token = $this->tokenRequest($data, [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => (string) $entry['redirect'],
            'code_verifier' => (string) $entry['verifier'],
        ]);

        $data['token'] = $token;
        $data['connected_at'] = time();
        $data['connected_by'] = mb_substr($username, 0, 64);
        $this->store($connection, $data);

        return $connection;
    }

    /** Current access token, refreshed when it runs out (or when forced after a 401); '' without one. */
    public function accessToken(ConnectionModel $connection, bool $refresh = false): string
    {
        $data = $this->data($connection);
        $token = (array) ($data['token'] ?? []);
        $access = (string) ($token['access_token'] ?? '');

        if ('' === $access) {
            return '';
        }

        $expires = (int) ($token['expires_at'] ?? 0);
        $due = $refresh || ($expires > 0 && $expires < time() + 60);

        if (!$due) {
            return $access;
        }

        if ('' === (string) ($token['refresh_token'] ?? '')) {
            return $refresh || $expires < time() ? '' : $access;
        }

        try {
            $new = $this->tokenRequest($data, ['grant_type' => 'refresh_token', 'refresh_token' => (string) $token['refresh_token']]);
        } catch (McpException) {
            return $expires > time() ? $access : '';
        }

        // A service that does not rotate refresh tokens sends none back: keep the old one.
        $new['refresh_token'] = '' !== $new['refresh_token'] ? $new['refresh_token'] : (string) $token['refresh_token'];
        $data['token'] = $new;
        $this->store($connection, $data);

        return $new['access_token'];
    }

    /**
     * What the back end shows: connected since, by whom, until when the token
     * runs, whether a client was registered automatically.
     *
     * @return array{connected: bool, connected_at: int, connected_by: string, expires_at: int, registered: bool}
     */
    public function status(ConnectionModel $connection): array
    {
        $data = $this->data($connection);

        return [
            'connected' => '' !== (string) ($data['token']['access_token'] ?? ''),
            'connected_at' => (int) ($data['connected_at'] ?? 0),
            'connected_by' => (string) ($data['connected_by'] ?? ''),
            'expires_at' => (int) ($data['token']['expires_at'] ?? 0),
            'registered' => (bool) ($data['client']['registered'] ?? false),
        ];
    }

    /** Signs out: forgets tokens and the registered client. */
    public function forget(ConnectionModel $connection): void
    {
        $this->store($connection, []);
    }

    /**
     * Endpoints of the sign-in: from the template of the connection, else found
     * from the MCP server; scopes, registration and resource indicator as the
     * template says.
     *
     * @return array{resource: string, issuer: string, authorization_endpoint: string, token_endpoint: string, registration_endpoint: string, scope: string}
     */
    private function discover(ConnectionModel $connection): array
    {
        $template = $this->templates->get((string) $connection->template);
        $metadata = null !== $template && '' !== $template->oauth['authorization_endpoint']
            ? [
                'resource' => trim((string) $connection->url),
                'issuer' => $this->origin($template->oauth['authorization_endpoint']),
                'authorization_endpoint' => $template->oauth['authorization_endpoint'],
                'token_endpoint' => $template->oauth['token_endpoint'],
                'registration_endpoint' => '',
                'scope' => '',
            ]
            : $this->discoverFromServer($connection);

        if (null !== $template) {
            if ('' !== $template->oauth['scope']) {
                $metadata['scope'] = $template->oauth['scope'];
            }

            if (!$template->oauth['resource']) {
                $metadata['resource'] = '';
            }

            if (!$template->oauth['registration']) {
                $metadata['registration_endpoint'] = '';
            }
        }

        return $metadata;
    }

    /**
     * Endpoints of the sign-in, found from the MCP server.
     *
     * @return array{resource: string, issuer: string, authorization_endpoint: string, token_endpoint: string, registration_endpoint: string, scope: string}
     */
    private function discoverFromServer(ConnectionModel $connection): array
    {
        $url = trim((string) $connection->url);
        $origin = $this->origin($url);
        $path = rtrim((string) parse_url($url, PHP_URL_PATH), '/');
        $resourceMetadata = '';
        $scope = '';

        try {
            $this->client->describe(new McpEndpoint($url));

            throw new McpException('The server answers without sign-in. Choose "without" as authentication.');
        } catch (McpAuthRequiredException $e) {
            $resourceMetadata = $e->resourceMetadata;
            $scope = $e->scope;
        }

        $candidates = array_unique(array_filter([$resourceMetadata, $origin.'/.well-known/oauth-protected-resource'.$path, $origin.'/.well-known/oauth-protected-resource']));
        $resource = [];

        foreach ($candidates as $candidate) {
            $resource = $this->json($candidate);

            if ([] !== $resource) {
                break;
            }
        }

        $issuer = rtrim((string) ($resource['authorization_servers'][0] ?? $origin), '/');
        $issuerOrigin = $this->origin($issuer);
        $issuerPath = rtrim((string) parse_url($issuer, PHP_URL_PATH), '/');
        $server = [];

        foreach (array_unique([
            $issuerOrigin.'/.well-known/oauth-authorization-server'.$issuerPath,
            $issuerOrigin.'/.well-known/openid-configuration'.$issuerPath,
            $issuer.'/.well-known/openid-configuration',
        ]) as $candidate) {
            $server = $this->json($candidate);

            if ('' !== (string) ($server['authorization_endpoint'] ?? '') && '' !== (string) ($server['token_endpoint'] ?? '')) {
                break;
            }
        }

        if ('' === (string) ($server['authorization_endpoint'] ?? '') || '' === (string) ($server['token_endpoint'] ?? '')) {
            throw new McpException('The server names no OAuth authorization server (no metadata found).');
        }

        $methods = (array) ($server['code_challenge_methods_supported'] ?? ['S256']);

        if (!\in_array('S256', $methods, true)) {
            throw new McpException('The authorization server does not support PKCE with S256.');
        }

        if ('' === $scope) {
            $scope = implode(' ', array_map('strval', (array) ($resource['scopes_supported'] ?? [])));
        }

        return [
            'resource' => (string) ($resource['resource'] ?? $url),
            'issuer' => (string) ($server['issuer'] ?? $issuer),
            'authorization_endpoint' => $this->https((string) $server['authorization_endpoint']),
            'token_endpoint' => $this->https((string) $server['token_endpoint']),
            'registration_endpoint' => '' !== (string) ($server['registration_endpoint'] ?? '') ? $this->https((string) $server['registration_endpoint']) : '',
            'scope' => trim($scope),
        ];
    }

    /**
     * The client of this website: entered by hand (app registered at the
     * service), or registered automatically for this redirect address.
     *
     * @param array<string, mixed> $data
     * @param array<string, string> $metadata
     *
     * @return array{client_id: string, client_secret: string, auth_method: string, redirect_uri: string, registered: bool}
     */
    private function client(ConnectionModel $connection, array $data, array $metadata, string $redirectUri): array
    {
        $manual = trim((string) $connection->oauth_client_id);

        if ('' !== $manual) {
            $secret = trim(html_entity_decode($this->cipher->decrypt((string) $connection->oauth_client_secret), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            return ['client_id' => $manual, 'client_secret' => $secret, 'auth_method' => '' !== $secret ? 'client_secret_post' : 'none', 'redirect_uri' => $redirectUri, 'registered' => false];
        }

        $known = (array) ($data['client'] ?? []);

        if (($known['registered'] ?? false) && ($known['redirect_uri'] ?? '') === $redirectUri && ($known['issuer'] ?? '') === $metadata['issuer'] && '' !== (string) ($known['client_id'] ?? '')) {
            return $known + ['client_secret' => '', 'auth_method' => 'none'];
        }

        if ('' === $metadata['registration_endpoint']) {
            throw new McpException('The service offers no automatic registration. Register an app there and enter its client ID (and secret).');
        }

        try {
            $response = $this->httpClient->request('POST', $metadata['registration_endpoint'], [
                'json' => [
                    'client_name' => 'Contao SuperAgent',
                    'redirect_uris' => [$redirectUri],
                    'grant_types' => ['authorization_code', 'refresh_token'],
                    'response_types' => ['code'],
                    'token_endpoint_auth_method' => 'none',
                ],
            ]);
            $registered = $response->toArray(false);
            $status = $response->getStatusCode();
        } catch (ExceptionInterface $e) {
            throw new McpException('The registration at the service failed: '.$e->getMessage(), 0, $e);
        }

        if ($status >= 300 || '' === (string) ($registered['client_id'] ?? '')) {
            throw new McpException(sprintf('The service refused the registration (HTTP %d): %s', $status, $this->snippet((string) ($registered['error_description'] ?? $registered['error'] ?? ''))));
        }

        $secret = (string) ($registered['client_secret'] ?? '');

        return [
            'client_id' => (string) $registered['client_id'],
            'client_secret' => $secret,
            'auth_method' => (string) ($registered['token_endpoint_auth_method'] ?? ('' !== $secret ? 'client_secret_basic' : 'none')),
            'redirect_uri' => $redirectUri,
            'registered' => true,
            'issuer' => $metadata['issuer'],
        ];
    }

    /**
     * @param array<string, mixed>  $data
     * @param array<string, string> $params
     *
     * @return array{access_token: string, refresh_token: string, expires_at: int, scope: string}
     */
    private function tokenRequest(array $data, array $params): array
    {
        $metadata = (array) ($data['metadata'] ?? []);
        $client = (array) ($data['client'] ?? []);
        $endpoint = (string) ($metadata['token_endpoint'] ?? '');

        if ('' === $endpoint || '' === (string) ($client['client_id'] ?? '')) {
            throw new McpException('The connection is not signed in. Use "Connect".');
        }

        $params += ['client_id' => (string) $client['client_id'], 'resource' => (string) ($metadata['resource'] ?? '')];
        $options = ['headers' => ['Accept' => 'application/json']];
        $secret = (string) ($client['client_secret'] ?? '');

        if ('' !== $secret && 'client_secret_basic' === ($client['auth_method'] ?? '')) {
            $options['auth_basic'] = [rawurlencode((string) $client['client_id']), rawurlencode($secret)];
        } elseif ('' !== $secret) {
            $params['client_secret'] = $secret;
        }

        $options['body'] = array_filter($params, static fn (string $v): bool => '' !== $v);

        try {
            $response = $this->httpClient->request('POST', $endpoint, $options);
            $status = $response->getStatusCode();
            $token = $response->toArray(false);
        } catch (ExceptionInterface $e) {
            throw new McpException('The token request failed: '.$e->getMessage(), 0, $e);
        }

        if ($status >= 300 || '' === (string) ($token['access_token'] ?? '')) {
            throw new McpException(sprintf('The service refused the token request (HTTP %d): %s', $status, $this->snippet((string) ($token['error_description'] ?? $token['error'] ?? ''))));
        }

        return [
            'access_token' => (string) $token['access_token'],
            'refresh_token' => (string) ($token['refresh_token'] ?? ''),
            'expires_at' => isset($token['expires_in']) ? time() + max(0, (int) $token['expires_in']) : 0,
            'scope' => (string) ($token['scope'] ?? ''),
        ];
    }

    /** @return array<string, mixed> */
    private function json(string $url): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->https($url), ['headers' => ['Accept' => 'application/json']]);

            if (200 !== $response->getStatusCode()) {
                return [];
            }

            return $response->toArray(false);
        } catch (\Throwable) {
            return [];
        }
    }

    /** @return array<string, mixed> */
    private function data(ConnectionModel $connection): array
    {
        $stored = (string) $connection->oauth_data;

        if ('' === $stored) {
            return [];
        }

        $data = json_decode($this->cipher->decrypt($stored), true);

        return \is_array($data) ? $data : [];
    }

    /** @param array<string, mixed> $data */
    private function store(ConnectionModel $connection, array $data): void
    {
        $value = [] === $data ? '' : $this->cipher->encrypt((string) json_encode($data, JSON_UNESCAPED_SLASHES));
        $this->db->update(Connections::TABLE, ['oauth_data' => $value], ['id' => (int) $connection->id]);
        $connection->oauth_data = $value;
    }

    private function origin(string $url): string
    {
        $parts = parse_url($url);

        return strtolower((string) ($parts['scheme'] ?? 'https')).'://'.strtolower((string) ($parts['host'] ?? '')).(isset($parts['port']) ? ':'.$parts['port'] : '');
    }

    private function https(string $url): string
    {
        if ('https' !== strtolower((string) parse_url($url, PHP_URL_SCHEME))) {
            throw new McpException('The service names an address without HTTPS: '.$this->snippet($url));
        }

        return $url;
    }

    private function snippet(string $text): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($text)) ?? '');

        return mb_strlen($text) > 200 ? mb_substr($text, 0, 200).' …' : $text;
    }
}

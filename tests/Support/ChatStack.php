<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Support;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Doctrine\DBAL\Connection;
use Neo\BasicBundle\Access\AccessSettings;
use Neo\BasicBundle\Ai\AiConnection;
use Neo\BasicBundle\Ai\ModelCatalog;
use Neo\BasicBundle\Security\SecretCipher;
use Neo\SuperAgentBundle\Chat\CannedAnswers;
use Neo\SuperAgentBundle\Chat\ChatHandler;
use Neo\SuperAgentBundle\Chat\ConversationMemory;
use Neo\SuperAgentBundle\Chat\DefaultTexts;
use Neo\SuperAgentBundle\Chat\Downloads;
use Neo\SuperAgentBundle\Chat\GroundingProvider;
use Neo\SuperAgentBundle\Chat\PageIndex;
use Neo\SuperAgentBundle\Chat\PersonalDataGuard;
use Neo\SuperAgentBundle\Chat\ProviderClient;
use Neo\SuperAgentBundle\Chat\SystemPromptBuilder;
use Neo\SuperAgentBundle\Chat\WebResearch;
use Neo\SuperAgentBundle\Mcp\Connections;
use Neo\SuperAgentBundle\Mcp\GuestCheck;
use Neo\SuperAgentBundle\Mcp\McpClient;
use Neo\SuperAgentBundle\Mcp\McpToolbox;
use Neo\SuperAgentBundle\Mcp\OAuthFlow;
use Neo\SuperAgentBundle\Mcp\TemplateCatalog;
use Neo\SuperAgentBundle\Mcp\ToolLog;
use Neo\SuperAgentBundle\Model\AgentLanguageModel;
use Neo\SuperAgentBundle\Skill\InstalledSkills;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The chat with all its parts, against an AI service played by MockHttpClient:
 * what the service received is in $sent, its answers are taken from $answers in
 * order (a text, a list of tool calls [id, name, arguments] or a MockResponse).
 */
final class ChatStack
{
    /** Pages the assistant may open on the start page 1. */
    public const PAGES = [
        12 => ['title' => 'Contact', 'url' => 'https://www.example.com/contact', 'path' => '/contact', 'description' => 'How to reach us'],
        14 => ['title' => 'Prices', 'url' => 'https://www.example.com/prices', 'path' => '/prices', 'description' => ''],
    ];

    public readonly Connection $db;

    public readonly SecretCipher $cipher;

    /** @var list<array<string, mixed>> */
    public array $sent = [];

    /** @var list<string|list<array{id: string, name: string, arguments: array<string, mixed>}>|MockResponse> */
    public array $answers = [];

    /** @var array<string, mixed> entry of the model in the model list of the route */
    public array $model = ['id' => 'provider/model-a'];

    public string $key = 'sk-test';

    /** @var array<string, string> answers of the MCP server at mcp.example.com per tool name */
    public array $mcpAnswers = [];

    /** @var list<array{name: string, arguments: array<string, mixed>}> tool calls the MCP server received */
    public array $mcpCalls = [];

    public AgentLanguageModel|null $texts = null;

    /** Files of tools for the visitor, shared by the handler and the download endpoint. */
    public Downloads $downloads;

    public function __construct()
    {
        $this->downloads = new Downloads(new ArrayAdapter());
        $this->db = TestDatabase::create('tl_neo_access', ToolLog::TABLE, InstalledSkills::TABLE, Connections::TABLE);
        $this->cipher = new SecretCipher('test-secret');
    }

    public function access(): AccessSettings
    {
        return new AccessSettings($this->db, $this->cipher);
    }

    public function connection(): AiConnection
    {
        (new AiConnection($this->access()))->store(AiConnection::CUSTOM, 'https://router.example.com/v1', $this->key);

        return new AiConnection($this->access());
    }

    public function http(): MockHttpClient
    {
        return new MockHttpClient(function (string $method, string $url, array $options): MockResponse {
            if (str_ends_with($url, '/models')) {
                return new MockResponse(json_encode(['data' => [$this->model]]));
            }

            // The MCP server of a connection (JSON-RPC): starts the session, answers tool calls.
            if (str_starts_with($url, 'https://mcp.example.com/')) {
                $rpc = json_decode($options['body'], true);

                if ('tools/call' === $rpc['method']) {
                    $this->mcpCalls[] = ['name' => $rpc['params']['name'], 'arguments' => (array) $rpc['params']['arguments']];
                    $text = $this->mcpAnswers[$rpc['params']['name']] ?? 'ok';

                    return new MockResponse(json_encode(['jsonrpc' => '2.0', 'id' => $rpc['id'], 'result' => ['content' => [['type' => 'text', 'text' => $text]]]]), ['response_headers' => ['content-type' => 'application/json']]);
                }

                return isset($rpc['id'])
                    ? new MockResponse(json_encode(['jsonrpc' => '2.0', 'id' => $rpc['id'], 'result' => ['protocolVersion' => '2025-06-18']]), ['response_headers' => ['content-type' => 'application/json']])
                    : new MockResponse('', ['http_code' => 202]);
            }

            $this->sent[] = json_decode($options['body'], true);
            $answer = array_shift($this->answers) ?? 'No more answers.';

            if ($answer instanceof MockResponse) {
                return $answer;
            }

            // A tool step may carry a text for the visitor: ['say' => '…', 'calls' => [...]].
            $say = \is_array($answer) && isset($answer['calls']) ? (string) ($answer['say'] ?? '') : null;
            $calls = \is_array($answer) && isset($answer['calls']) ? $answer['calls'] : $answer;

            $message = \is_string($answer)
                ? ['role' => 'assistant', 'content' => $answer]
                : ['role' => 'assistant', 'content' => $say, 'tool_calls' => array_map(static fn (array $c): array => ['id' => $c['id'], 'type' => 'function', 'function' => ['name' => $c['name'], 'arguments' => json_encode($c['arguments'])]], $calls)];

            return new MockResponse(json_encode(['choices' => [['message' => $message]]]));
        });
    }

    public function handler(ContaoFramework $framework, ContentUrlGenerator $urls, int $maxSteps = 8): ChatHandler
    {
        $http = $this->http();
        $connection = $this->connection();
        $cache = new ArrayAdapter();
        $cache->save($cache->getItem('neo_agent_pages_1')->set(self::PAGES));
        $templates = new TemplateCatalog(sys_get_temp_dir().'/neo-no-project', new NullLogger());
        $mcpClient = new McpClient($http);
        $connections = new Connections($this->db, $this->cipher, $mcpClient, new OAuthFlow($http, $mcpClient, $this->cipher, $this->db, new RequestStack(), $templates), $templates);

        return new ChatHandler(
            new ProviderClient($http),
            new GroundingProvider($framework, sys_get_temp_dir()),
            new SystemPromptBuilder(),
            new CannedAnswers(),
            $connection,
            new ModelCatalog($http, new ArrayAdapter(), $connection, new NullLogger()),
            new NullLogger(),
            new WebResearch($http, new ArrayAdapter(), $this->access(), new NullLogger()),
            new DefaultTexts(Translations::translator()),
            new PersonalDataGuard(),
            new PageIndex($this->db, $framework, $urls, $cache),
            new McpToolbox($connections, $mcpClient, new ToolLog($this->db), new PersonalDataGuard(), new NullLogger(), new GuestCheck('test-secret', new ArrayAdapter(), new RequestStack()), $this->downloads),
            new InstalledSkills($this->db),
            new ConversationMemory($this->cipher),
            $maxSteps,
            fn (): AgentLanguageModel|null => $this->texts,
        );
    }
}

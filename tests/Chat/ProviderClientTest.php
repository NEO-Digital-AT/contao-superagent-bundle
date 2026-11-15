<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Chat;

use Neo\SuperAgentBundle\Chat\ProviderClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

#[CoversClass(ProviderClient::class)]
final class ProviderClientTest extends TestCase
{
    private const MESSAGES = [['role' => 'system', 'content' => 'Rules'], ['role' => 'user', 'content' => 'Hello']];

    public function testTheRequestIsAnOpenAiChatCompletion(): void
    {
        $sent = null;
        $client = new MockHttpClient(static function (string $method, string $url, array $options) use (&$sent): MockResponse {
            $sent = [$method, $url, $options];

            return new MockResponse(json_encode(['choices' => [['message' => ['role' => 'assistant', 'content' => "  Hello!\n "]]]]));
        });

        $answer = (new ProviderClient($client))->complete('https://router.example.com/v1/', 'sk-test', 'provider/model-a', 0.3, 600, self::MESSAGES);

        $this->assertSame('Hello!', $answer);
        $this->assertSame(['POST', 'https://router.example.com/v1/chat/completions'], [$sent[0], $sent[1]]);
        $this->assertContains('Authorization: Bearer sk-test', $sent[2]['headers']);
        $this->assertSame(['model' => 'provider/model-a', 'max_tokens' => 600, 'messages' => self::MESSAGES, 'temperature' => 0.3], json_decode($sent[2]['body'], true));
    }

    public function testAReasoningModelGetsTheEffortInsteadOfATemperature(): void
    {
        $body = null;
        $client = new MockHttpClient(static function (string $method, string $url, array $options) use (&$body): MockResponse {
            $body = json_decode($options['body'], true);

            return new MockResponse('{"choices":[{"message":{"content":"ok"}}]}');
        });

        (new ProviderClient($client))->call('https://router.example.com/v1', 'sk-test', 'model-r', 0.7, 900, self::MESSAGES, [['type' => 'function', 'function' => ['name' => 'web_search']]], 'low');

        $this->assertSame('low', $body['reasoning_effort']);
        $this->assertArrayNotHasKey('temperature', $body);
        $this->assertSame('auto', $body['tool_choice']);
        $this->assertSame('web_search', $body['tools'][0]['function']['name']);
    }

    public function testToolCallsAreReadAndTheReasoningStaysBehind(): void
    {
        $message = [
            'role' => 'assistant',
            'content' => null,
            'reasoning' => 'internal thoughts bound to this step',
            'tool_calls' => [
                ['id' => 'call_1', 'type' => 'function', 'function' => ['name' => 'open_page', 'arguments' => '{"page_id":12,"now":true}']],
                ['id' => 'call_2', 'type' => 'function', 'function' => ['name' => 'find_pages', 'arguments' => 'not json']],
                ['id' => 'call_3', 'type' => 'function', 'function' => ['arguments' => '{}']],
            ],
        ];
        $client = new MockHttpClient(new MockResponse(json_encode(['choices' => [['message' => $message]]])));

        $result = (new ProviderClient($client))->call('https://router.example.com/v1', 'sk-test', 'model-a', 0.3, 600, self::MESSAGES, [['type' => 'function']]);

        $this->assertSame('', $result['content']);
        $this->assertSame([
            ['id' => 'call_1', 'name' => 'open_page', 'arguments' => ['page_id' => 12, 'now' => true]],
            ['id' => 'call_2', 'name' => 'find_pages', 'arguments' => []],
        ], $result['toolCalls']);
        $this->assertSame(['role', 'content', 'tool_calls'], array_keys($result['raw']));
    }

    public function testAnErrorOfTheServiceIsThrown(): void
    {
        $client = new MockHttpClient(new MockResponse('{"error":{"message":"invalid key"}}', ['http_code' => 401]));

        $this->expectException(HttpExceptionInterface::class);
        (new ProviderClient($client))->complete('https://router.example.com/v1', 'sk-wrong', 'model-a', 0.3, 600, self::MESSAGES);
    }

    public function testAnAnswerWithoutChoicesIsEmpty(): void
    {
        $this->assertSame('', (new ProviderClient(new MockHttpClient(new MockResponse('{}'))))->complete('https://router.example.com/v1', 'sk-test', 'model-a', 0.3, 600, self::MESSAGES));
    }
}

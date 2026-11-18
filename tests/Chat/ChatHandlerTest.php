<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Chat;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Neo\SuperAgentBundle\Chat\ChatHandler;
use Contao\Model\Registry;
use Neo\SuperAgentBundle\Licence\Features;
use Neo\SuperAgentBundle\Mcp\Connections;
use Neo\SuperAgentBundle\Mcp\ToolLog;
use Neo\SuperAgentBundle\Model\ConnectionModel;
use Neo\SuperAgentBundle\Model\AgentLanguageModel;
use Neo\SuperAgentBundle\Model\AgentModel;
use Neo\SuperAgentBundle\Tests\Support\ChatStack;
use Neo\SuperAgentBundle\Tests\Support\Models;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Response\MockResponse;

/** One reply of the assistant against an AI service played by MockHttpClient. */
#[CoversClass(ChatHandler::class)]
final class ChatHandlerTest extends TestCase
{
    private ChatStack $chat;

    protected function setUp(): void
    {
        $this->chat = new ChatStack();
    }

    public function testWithoutConnectionThereIsNoAnswer(): void
    {
        $this->chat->key = '';

        $this->assertSame(['error' => 'unavailable'], $this->reply([], 'Hello'));
        $this->assertSame([], $this->chat->sent);
    }

    public function testTheAnswerGoesOutWithoutPlainAddresses(): void
    {
        $this->chat->answers = ['Write to office@example.com.'];

        $result = $this->reply([], 'How can I reach you?', [['role' => 'user', 'content' => 'Hi'], ['role' => 'assistant', 'content' => 'Hello!']]);

        $this->assertSame(['reply' => 'Write to office(at)example.com.', 'handoff' => null, 'navigate' => null], $result);
        $messages = $this->chat->sent[0]['messages'];
        $this->assertSame('system', $messages[0]['role']);
        $this->assertStringContainsString('Assistant name: Bot', $messages[0]['content']);
        $this->assertSame([['role' => 'user', 'content' => 'Hi'], ['role' => 'assistant', 'content' => 'Hello!'], ['role' => 'user', 'content' => 'How can I reach you?']], \array_slice($messages, 1));
        $this->assertSame(0.4, $this->chat->sent[0]['temperature']);
        $this->assertSame(500, $this->chat->sent[0]['max_tokens']);
    }

    public function testPersonalDataNeverReachesTheService(): void
    {
        $this->chat->answers = ['Thank you.'];

        $result = $this->reply(['privacy_mode' => '1'], 'My IBAN is AT61 1904 3002 3457 3201', [['role' => 'user', 'content' => 'I am max.mustermann@example.com'], ['role' => 'assistant', 'content' => 'Noted, max.mustermann@example.com.']]);

        $this->assertSame('My IBAN is [removed: IBAN]', $result['message']);
        $body = json_encode($this->chat->sent[0]['messages']);
        $this->assertStringNotContainsString('AT61', $body);
        $this->assertStringNotContainsString('I am max.mustermann@example.com', $body);
        $this->assertStringContainsString('I am [removed: e-mail address]', $body);
        $this->assertStringContainsString('LOADED SKILLS', $this->chat->sent[0]['messages'][0]['content'], 'the privacy skill applies from the first step');
    }

    public function testWithoutLicenceNoToolOfAPaidEditionIsOffered(): void
    {
        $this->chat->answers = ['Fine.'];

        $this->reply(['escalation_email' => 'team@example.com', 'web_search' => '1', 'web_search_sources' => serialize(['docs.example.com'])], 'Hello', [], Features::forEdition(''));

        $this->assertSame(['load_module'], $this->offered(0));
        $this->assertNotContains('navigation', $this->chat->sent[0]['tools'][0]['function']['parameters']['properties']['modules']['items']['enum']);
        $this->assertStringContainsString("Forwarding: not available\nNavigation: not available; link a page of this website only with an address from the knowledge", $this->chat->sent[0]['messages'][0]['content']);
    }

    public function testWithoutNavigationTheReplyLinksOnlyPagesFromTheKnowledge(): void
    {
        $reply = "See [Contact](https://www.example.com/en/contact) and [Prices](/en/prices), or [Partner](https://partner.example.org/).";
        $agent = ['grounding_source' => 'text', 'grounding_text' => 'We rent bikes. Contact: https://www.example.com/en/contact'];

        // Free edition: the page that is not in the knowledge is not linked.
        $this->chat->answers = [$reply];
        $this->assertSame('See [Contact](https://www.example.com/en/contact) and Prices, or [Partner](https://partner.example.org/).', $this->reply($agent, 'Where is the contact page?', [], Features::forEdition(''))['reply']);

        // With navigation (Basic) the pages of the page list are linked as well, but no page that is not there.
        $this->chat->answers = ["See [Prices](https://www.example.com/prices), [Contact](/en/contact) and [Imprint](/imprint)."];
        $this->assertSame('See [Prices](https://www.example.com/prices), [Contact](/en/contact) and Imprint.', $this->reply($agent, 'Where is the contact page?', [], Features::forEdition('basic'))['reply']);
    }

    public function testAPageOfTheIndexIsOpenedAfterItsSkillIsLoaded(): void
    {
        $steps = [];
        $this->chat->answers = [
            [['id' => 'c1', 'name' => 'open_page', 'arguments' => ['page_id' => 12, 'now' => true]]],
            [['id' => 'c2', 'name' => 'load_module', 'arguments' => ['modules' => ['navigation']]]],
            [['id' => 'c3', 'name' => 'open_page', 'arguments' => ['page_id' => 99, 'now' => true]], ['id' => 'c4', 'name' => 'open_page', 'arguments' => ['page_id' => 14, 'now' => false]]],
            'Here are the prices.',
        ];

        $result = $this->reply([], 'Prices?', [], Features::forEdition('basic'), static function (string $step) use (&$steps): void {
            $steps[] = $step;
        });

        $this->assertSame(['url' => 'https://www.example.com/prices', 'title' => 'Prices', 'now' => false], $result['navigate']);
        $this->assertSame('This tool is not available in this step.', $this->toolAnswer(1, 'c1'), 'not before its skill is loaded');
        $this->assertStringStartsWith('Loaded skills: navigation.', $this->toolAnswer(2, 'c2'));
        $this->assertSame('There is no page with this id.', $this->toolAnswer(3, 'c3'));
        $this->assertStringStartsWith('A button for the page "Prices" is shown below your reply. Call it by this title.', $this->toolAnswer(3, 'c4'), 'the reply names the page that really opens');
        $this->assertContains('open_page', $this->offered(2));
        $this->assertStringContainsString("12 | Contact | /contact | How to reach us\n14 | Prices | /prices", $this->chat->sent[0]['messages'][0]['content']);
        $this->assertSame(['pages', 'pages', 'pages', 'pages'], $steps, 'one status per tool call');
    }

    public function testAPageOpensByItselfOnlyWhenTheReplyNamesIt(): void
    {
        $open = [['id' => 'c1', 'name' => 'load_module', 'arguments' => ['modules' => ['navigation']]]];
        $prices = [['id' => 'c2', 'name' => 'open_page', 'arguments' => ['page_id' => 14, 'now' => true]]];

        // The visitor asked for an imprint; the model opened the price page and wrote about something else.
        $this->chat->answers = [$open, $prices, 'This website has no imprint page. I can forward your request.'];
        $this->assertSame(['url' => 'https://www.example.com/prices', 'title' => 'Prices', 'now' => false], $this->reply([], 'Open the imprint, please.', [], Features::forEdition('basic'))['navigate'], 'only the button, no page opened in place of another');

        $this->chat->answers = [$open, $prices, 'I am opening the page "Prices" for you.'];
        $this->assertTrue($this->reply([], 'Open the prices, please.', [], Features::forEdition('basic'))['navigate']['now']);
    }

    public function testWhatTheModelSaysWhileItsToolsRunReachesTheVisitorAtOnce(): void
    {
        $said = [];
        $this->chat->answers = [
            ['say' => 'One moment, I am checking the prices for you@example.com.', 'calls' => [['id' => 'c1', 'name' => 'load_module', 'arguments' => ['modules' => ['navigation']]]]],
            [['id' => 'c2', 'name' => 'open_page', 'arguments' => ['page_id' => 14, 'now' => false]]],
            'Here are the prices.',
        ];

        $result = $this->chat->handler($this->createMock(ContaoFramework::class), $this->createMock(ContentUrlGenerator::class))->reply(
            Models::make(AgentModel::class, ['id' => 3, 'assistant_name' => 'Bot', 'model' => 'provider/model-a', 'temperature' => '0,4', 'max_tokens' => 500, 'grounding_source' => 'text', 'grounding_text' => 'We rent bikes.']),
            'Prices?',
            [],
            'en',
            ['url' => '/prices', 'title' => 'Prices', 'root' => 1],
            Features::forEdition('basic'),
            null,
            '',
            static function (string $text) use (&$said): void {
                $said[] = $text;
            },
        );

        $this->assertSame(['One moment, I am checking the prices for you(at)example.com.'], $said, 'sent while the tool runs, addresses defused');
        $this->assertSame($said, $result['interim'], 'also in the reply, for a widget without a stream');
        $this->assertSame('Here are the prices.', $result['reply']);
        $this->assertStringContainsString('the visitor waits', $this->chat->sent[0]['messages'][0]['content'], 'the model is told to say what it checks');
    }

    public function testAToolWhoseDefinitionTheServiceRefusesIsLeftOutAndTheOthersStay(): void
    {
        $tools = json_encode(['read_at' => 1, 'url' => 'https://mcp.example.com/mcp', 'server' => 'Hotel 1', 'tools' => [
            ['name' => 'ListOffers', 'description' => 'Offers for a stay.', 'inputSchema' => ['type' => 'object', 'properties' => ['arrival' => ['type' => 'string']]], 'annotations' => ['readOnlyHint' => true]],
            ['name' => 'Strange', 'description' => 'A tool the service refuses.', 'inputSchema' => ['type' => 'object'], 'annotations' => ['readOnlyHint' => true]],
        ], 'new' => []]);
        $row = ['id' => 1, 'tstamp' => 1, 'name' => 'Hotel', 'url' => 'https://mcp.example.com/mcp', 'auth' => ConnectionModel::AUTH_NONE, 'confirmed_at' => 1, 'tools' => $tools, 'locked' => serialize([]), 'personal_data' => ConnectionModel::DATA_MASK, 'template' => ''];
        $this->chat->db->insert(Connections::TABLE, array_intersect_key($row, array_flip(['id', 'tstamp', 'name', 'url', 'tools', 'locked'])));
        Models::register(ConnectionModel::class, $row);
        $this->chat->answers = [
            [['id' => 'c1', 'name' => 'load_tools', 'arguments' => ['tools' => ['mcp1_ListOffers', 'mcp1_Strange']]]],
            new MockResponse('{"error":{"origin":"provider","message":"Invalid schema for function \'mcp1_Strange\': [] is not of type \'object\', \'boolean\'."}}', ['http_code' => 400]),
            'Which dates?',
        ];

        try {
            $result = $this->reply(['mcp_tools' => serialize(['1:ListOffers', '1:Strange'])], 'A room, please.', [], Features::forEdition('plus'));
        } finally {
            Registry::getInstance()->reset();
        }

        $this->assertSame('Which dates?', $result['reply'], 'the request goes on instead of losing all tools');
        $mcp = static fn (array $names): array => array_values(array_filter($names, static fn (string $name): bool => str_starts_with($name, 'mcp1_')));
        $this->assertSame(['mcp1_ListOffers', 'mcp1_Strange'], $mcp($this->offered(1)));
        $this->assertSame(['mcp1_ListOffers'], $mcp($this->offered(2)), 'only the refused tool is left out');
        $this->assertSame([['Strange', 'refused', 'schema']], $this->chat->db->fetchAllNumeric('SELECT tool, status, note FROM '.ToolLog::TABLE), 'visible in the tool log');
    }

    public function testTheNextReplyKnowsTheToolResultsAndKeepsTheLoadedTools(): void
    {
        $this->hotelConnection();
        $this->chat->mcpAnswers = ['ListOffers' => '{"offers":[{"ratePlanId":"NONREF","total":88}]}'];
        $this->chat->answers = [
            [['id' => 'c1', 'name' => 'load_tools', 'arguments' => ['tools' => ['mcp1_ListOffers']]]],
            [['id' => 'c2', 'name' => 'mcp1_ListOffers', 'arguments' => ['arrival' => '2026-12-01']]],
            'One offer: 88 EUR, not refundable.',
        ];
        $agent = ['mcp_tools' => serialize(['1:ListOffers'])];

        try {
            $first = $this->reply($agent, 'A room on 1 December, please.', [], Features::forEdition('plus'));
            $this->assertArrayHasKey('memory', $first);
            $this->assertStringNotContainsString('NONREF', base64_decode(substr($first['memory'], 4)), 'the visitor cannot read the results');

            $this->chat->sent = [];
            $this->chat->answers = ['Booked.'];
            $second = $this->reply($agent, 'I take it.', [], Features::forEdition('plus'), memory: $first['memory']);

            $prompt = $this->chat->sent[0]['messages'][0]['content'];
            $this->assertStringContainsString("=== EARLIER TOOL RESULTS ===\n", $prompt);
            $this->assertStringContainsString('- mcp1_ListOffers {"arrival":"2026-12-01"}'."\n".'  result: TOOL RESULT:', $prompt);
            $this->assertStringContainsString('{"offers":[{"ratePlanId":"NONREF","total":88}]}', $prompt);
            $this->assertContains('mcp1_ListOffers', $this->offered(0), 'the tool loaded before is offered from the first step');
            $this->assertStringNotContainsString('- mcp1_ListOffers:', $prompt, 'not in the catalogue of tools to load');
            $this->assertCount(1, $this->chat->mcpCalls, 'the look-up is not repeated by the application');
            $this->assertArrayHasKey('memory', $second, 'the memory goes on');

            $this->chat->sent = [];
            $this->chat->answers = ['Hello.'];
            $this->reply(['id' => 4] + $agent, 'I take it.', [], Features::forEdition('plus'), memory: $first['memory']);
            $this->assertStringNotContainsString('EARLIER TOOL RESULTS', $this->chat->sent[0]['messages'][0]['content'], 'the memory of another assistant counts as empty');
        } finally {
            Registry::getInstance()->reset();
        }
    }

    public function testAToolStepWithoutTextSaysNothing(): void
    {
        $this->chat->answers = [
            [['id' => 'c1', 'name' => 'load_module', 'arguments' => ['modules' => ['navigation']]]],
            'Here are the prices.',
        ];

        $result = $this->reply([], 'Prices?', [], Features::forEdition('basic'));

        $this->assertArrayNotHasKey('interim', $result);
    }

    public function testTheFormIsPrefilledButNothingIsSent(): void
    {
        $this->chat->answers = [
            [['id' => 'c1', 'name' => 'load_tools', 'arguments' => ['tools' => ['request_handoff', 'shell']]]],
            [['id' => 'c2', 'name' => 'request_handoff', 'arguments' => ['summary' => '<b>Call back</b> max.mustermann@example.com']]],
            'The form is open.',
        ];

        $result = $this->reply(['escalation_email' => 'team@example.com'], 'Call me back', [], Features::forEdition('basic'));

        $this->assertSame('Call back max.mustermann(at)example.com', $result['handoff']);
        $this->assertSame('Loaded tools: request_handoff. They are offered as functions now: call them directly with their arguments, do not load them again.', $this->toolAnswer(1, 'c1'));
        $this->assertStringStartsWith('The forwarding form is open below the chat, prefilled with the summary. Nothing has been sent.', $this->toolAnswer(2, 'c2'));
    }

    public function testTheCatalogueListsOnlyToolsStillToLoadAndLoadingAgainSaysCallIt(): void
    {
        $this->chat->answers = [
            [['id' => 'c1', 'name' => 'load_tools', 'arguments' => ['tools' => ['request_handoff']]]],
            [['id' => 'c2', 'name' => 'load_tools', 'arguments' => ['tools' => ['request_handoff']]]],
            'Done.',
        ];

        $this->reply(['escalation_email' => 'team@example.com'], 'Call me back', [], Features::forEdition('basic'));

        $catalogue = static fn (string $prompt): string => 1 === preg_match('/=== CATALOG ===\n(.*?)(?:\n\n===|\z)/s', $prompt, $m) ? $m[1] : '';
        $this->assertStringContainsString('- request_handoff:', $catalogue($this->chat->sent[0]['messages'][0]['content']), 'listed before it is loaded');
        $this->assertStringNotContainsString('- request_handoff:', $catalogue($this->chat->sent[1]['messages'][0]['content']), 'offered as a function once loaded, no longer in the list');
        $this->assertStringContainsString('call them directly; never load a tool that is offered already', $this->chat->sent[1]['messages'][0]['content']);
        $this->assertSame('Already loaded: request_handoff. They are offered as functions now: call them directly with their arguments, do not load them again.', $this->toolAnswer(2, 'c2'), 'models load the same tool again instead of calling it');
    }

    public function testAModelWithoutToolCallingUsesMarkerLines(): void
    {
        $this->chat->answers = [
            new MockResponse('{"error":{"message":"tools not supported"}}', ['http_code' => 400]),
            "Here is the contact page.\n[[PAGE: 12 now]]\n[[HANDOFF: Wants a call]]",
        ];

        $result = $this->reply([], 'Contact?', [], Features::forEdition('basic'));

        $this->assertSame(['reply' => 'Here is the contact page.', 'handoff' => null, 'navigate' => ['url' => 'https://www.example.com/contact', 'title' => 'Contact', 'now' => true]], $result, 'no forwarding without an address');
        $this->assertArrayNotHasKey('tools', $this->chat->sent[1]);
        $this->assertStringContainsString('--- SKILL navigation ---', $this->chat->sent[1]['messages'][0]['content'], 'all available skills at once');
    }

    public function testTheLastStepHasNoToolsAndAnEmptyAnswerIsNone(): void
    {
        $this->chat->answers = array_fill(0, 9, [['id' => 'c', 'name' => 'load_module', 'arguments' => ['modules' => ['contact']]]]);

        $this->assertSame(['error' => 'unavailable'], $this->reply([], 'Loop', [], null, null, 2));
        $this->assertCount(6, $this->chat->sent, 'two steps, three times loading again what is loaded does not count, the last step');
        $this->assertArrayNotHasKey('tools', $this->chat->sent[5]);
    }

    public function testAReasoningModelGetsItsEffortAndRoomForItsReasoning(): void
    {
        $this->chat->model = ['id' => 'provider/model-a', 'supports_reasoning' => true, 'max_output_tokens' => 4000];
        $this->chat->answers = ['Answer.'];

        $this->reply(['reasoning_effort' => 'medium', 'max_tokens' => 500], 'Hello');

        $this->assertSame('medium', $this->chat->sent[0]['reasoning_effort']);
        $this->assertSame(4000, $this->chat->sent[0]['max_tokens'], '500 + 8192 for the reasoning, capped by the model');
        $this->assertArrayNotHasKey('temperature', $this->chat->sent[0]);
    }

    public function testTheOwnTextsOfTheLanguageRecord(): void
    {
        $this->chat->answers = ['Fine.'];
        $texts = Models::make(AgentLanguageModel::class, ['ui_medical_refusal' => '%name% gives no medical advice.', 'canned' => serialize([['question' => 'Parking?', 'answer' => 'In the yard.']])]);

        $this->reply(['no_medical' => '1'], 'Hello', [], null, null, 8, $texts);

        $system = $this->chat->sent[0]['messages'][0]['content'];
        $this->assertStringContainsString('MEDICAL GUIDANCE: Bot gives no medical advice.', $system);
        $this->assertStringContainsString("Q: Parking?\nA: In the yard.", $system);
    }

    /**
     * @param array<string, mixed>                       $agent
     * @param list<array{role: string, content: string}> $history
     */
    public function testAfterTheLicenceIsGoneNothingOfTheConnectionReachesTheModel(): void
    {
        $this->hotelConnection();
        $this->chat->mcpAnswers = ['ListOffers' => '{"offers":[{"ratePlanId":"NONREF","total":88}]}'];
        $this->chat->answers = [
            [['id' => 'c1', 'name' => 'load_tools', 'arguments' => ['tools' => ['mcp1_ListOffers']]]],
            [['id' => 'c2', 'name' => 'mcp1_ListOffers', 'arguments' => ['arrival' => '2026-12-01']]],
            'One offer: 88 EUR, not refundable.',
        ];
        $agent = ['mcp_tools' => serialize(['1:ListOffers'])];

        try {
            $first = $this->reply($agent, 'A room on 1 December, please.', [], Features::forEdition('plus'));

            // The licence falls back to the free edition; the widget still sends the memory of the conversation.
            $this->chat->sent = [];
            $this->chat->mcpCalls = [];
            $this->chat->answers = [
                [['id' => 'c3', 'name' => 'mcp1_ListOffers', 'arguments' => ['arrival' => '2026-12-02']]],
                'Sorry.',
            ];
            $second = $this->reply($agent, 'And on 2 December?', [], Features::forEdition(''), memory: $first['memory']);

            $prompt = $this->chat->sent[0]['messages'][0]['content'];
            $this->assertStringNotContainsString('EARLIER TOOL RESULTS', $prompt, 'no results of the connection');
            $this->assertStringNotContainsString('NONREF', $prompt);
            $this->assertStringNotContainsString('mcp1_ListOffers', $prompt, 'the tool is not named');
            $this->assertNotContains('mcp1_ListOffers', $this->offered(0), 'the tool is not offered');
            $this->assertSame([], $this->chat->mcpCalls, 'the connection is not called');
            $this->assertArrayNotHasKey('memory', $second, 'the memory of the paid edition is not handed on');
        } finally {
            Registry::getInstance()->reset();
        }
    }

    private function reply(array $agent, string $message, array $history = [], Features|null $features = null, callable|null $progress = null, int $maxSteps = 8, AgentLanguageModel|null $texts = null, string $memory = '', string $host = 'www.example.com'): array
    {
        $this->chat->texts = $texts;

        return $this->chat->handler($this->createMock(ContaoFramework::class), $this->createMock(ContentUrlGenerator::class), $maxSteps)->reply(
            Models::make(AgentModel::class, $agent + ['id' => 3, 'assistant_name' => 'Bot', 'model' => 'provider/model-a', 'temperature' => '0,4', 'max_tokens' => 500, 'grounding_source' => 'text', 'grounding_text' => 'We rent bikes.']),
            $message,
            $history,
            'en',
            ['url' => '/prices', 'title' => 'Prices', 'root' => 1],
            $features,
            $progress,
            memory: $memory,
            host: $host,
        );
    }

    /** A connection 1 to the MCP server of ChatStack with the tool ListOffers. */
    private function hotelConnection(): void
    {
        $tools = json_encode(['read_at' => 1, 'url' => 'https://mcp.example.com/mcp', 'server' => 'Hotel 1', 'tools' => [
            ['name' => 'ListOffers', 'description' => 'Offers for a stay.', 'inputSchema' => ['type' => 'object', 'properties' => ['arrival' => ['type' => 'string']]], 'annotations' => ['readOnlyHint' => true]],
        ], 'new' => []]);
        $row = ['id' => 1, 'tstamp' => 1, 'name' => 'Hotel', 'url' => 'https://mcp.example.com/mcp', 'auth' => ConnectionModel::AUTH_NONE, 'confirmed_at' => 1, 'tools' => $tools, 'locked' => serialize([]), 'personal_data' => ConnectionModel::DATA_SEND, 'template' => ''];
        $this->chat->db->insert(Connections::TABLE, array_intersect_key($row, array_flip(['id', 'tstamp', 'name', 'url', 'tools', 'locked'])));
        Models::register(ConnectionModel::class, $row);
    }

    /** @return list<string> names of the tools offered in a step */
    private function offered(int $step): array
    {
        return array_map(static fn (array $tool): string => $tool['function']['name'], $this->chat->sent[$step]['tools'] ?? []);
    }

    /** The answer of a tool call as it went back to the service in a later step. */
    private function toolAnswer(int $step, string $callId): string
    {
        foreach ($this->chat->sent[$step]['messages'] as $message) {
            if ('tool' === ($message['role'] ?? '') && $callId === $message['tool_call_id']) {
                return (string) $message['content'];
            }
        }

        $this->fail('No answer to '.$callId);
    }
}

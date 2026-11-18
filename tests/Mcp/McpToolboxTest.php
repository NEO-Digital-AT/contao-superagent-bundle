<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Mcp;

use Contao\Model\Registry;
use Doctrine\DBAL\Connection;
use Neo\BasicBundle\Security\SecretCipher;
use Neo\SuperAgentBundle\Chat\Downloads;
use Neo\SuperAgentBundle\Chat\PersonalDataGuard;
use Neo\SuperAgentBundle\Mcp\Connections;
use Neo\SuperAgentBundle\Mcp\McpClient;
use Neo\SuperAgentBundle\Mcp\GuestCheck;
use Neo\SuperAgentBundle\Mcp\McpToolbox;
use Neo\SuperAgentBundle\Mcp\OAuthFlow;
use Neo\SuperAgentBundle\Mcp\TemplateCatalog;
use Neo\SuperAgentBundle\Mcp\ToolLog;
use Neo\SuperAgentBundle\Model\AgentModel;
use Neo\SuperAgentBundle\Model\ConnectionModel;
use Neo\SuperAgentBundle\Tests\Support\Models;
use Neo\SuperAgentBundle\Tests\Support\TestDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/** Which tools of connected MCP servers an assistant may call, and what reaches the service and the model. */
#[CoversClass(McpToolbox::class)]
#[CoversClass(Connections::class)]
#[CoversClass(ToolLog::class)]
final class McpToolboxTest extends TestCase
{
    private const URL = 'https://mcp.example.com/mcp';

    private Connection $db;

    private SecretCipher $cipher;

    /** @var list<array<string, mixed>> tools/call requests as the server received them */
    private array $calls = [];

    /** @var list<array<string, string>> */
    private array $headers = [];

    private string $result = 'Order 42: shipped';

    private bool $isError = false;

    private Downloads $downloads;

    protected function setUp(): void
    {
        $this->downloads = new Downloads(new ArrayAdapter());
        $this->db = TestDatabase::create(Connections::TABLE, ToolLog::TABLE);
        $this->cipher = new SecretCipher('test-secret');
        Registry::getInstance()->reset();
    }

    protected function tearDown(): void
    {
        Registry::getInstance()->reset();
    }

    public function testAnEmptySchemaGoesOutAsAnObjectAndARefusedToolIsLeftOut(): void
    {
        // UpdateReservation of the apaleo MCP server: "value" accepts any JSON ({}).
        $schema = json_decode('{"additionalProperties":false,"properties":{"body":{"items":{"properties":{"op":{"anyOf":[{"type":"string"},{"type":"null"}],"default":null},"value":{"anyOf":[{},{"type":"null"}],"default":null}},"type":"object"},"type":"array"},"id":{"type":"string"}},"required":["id","body"],"type":"object"}', true);
        $tools = json_encode(['read_at' => 1, 'url' => self::URL, 'server' => 'Hotel 1', 'tools' => [
            ['name' => 'UpdateReservation', 'description' => 'Update a reservation.', 'inputSchema' => $schema],
            ['name' => 'get_order', 'description' => 'Shows an order.', 'inputSchema' => ['type' => 'object', 'properties' => ['meta' => [], 'tags' => ['type' => 'array', 'items' => []]], 'required' => []]],
        ], 'new' => []]);
        $this->connection(1, ['tools' => $tools]);
        $toolbox = $this->toolbox();
        $toolbox->prepare(Models::make(AgentModel::class, ['id' => 9, 'mcp_tools' => serialize(['1:UpdateReservation', '1:get_order'])]), true);

        $update = json_encode($toolbox->definition('mcp1_UpdateReservation'));
        $this->assertStringContainsString('"value":{"anyOf":[{},{"type":"null"}]', $update);
        $this->assertStringNotContainsString('[]', $update);
        $this->assertStringContainsString('"properties":{"meta":{},"tags":{"type":"array","items":{}}},"required":[]', json_encode($toolbox->definition('mcp1_get_order')), 'empty schemas become objects, an empty list stays a list');

        $toolbox->refused('mcp1_UpdateReservation');

        $this->assertSame(['mcp1_get_order'], $toolbox->names());
        $this->assertSame([['UpdateReservation', 'refused', 'schema']], $this->db->fetchAllNumeric('SELECT tool, status, note FROM '.ToolLog::TABLE));
    }

    public function testOnlyTickedListedAndUnlockedToolsOfAnActiveConnection(): void
    {
        $this->connection(1, ['locked' => serialize(['delete_order'])]);
        $this->connection(2, ['confirmed_at' => 0]);
        $this->connection(3, ['tools' => $this->tools(['get_order'], 'https://old.example.com/mcp')]);
        $agent = Models::make(AgentModel::class, ['id' => 9, 'mcp_tools' => serialize(['1:get_order', '1:delete_order', '1:gone', '2:get_order', '3:get_order', 'nonsense'])]);

        $toolbox = $this->toolbox();
        $toolbox->prepare($agent, true);
        $this->assertSame(['mcp1_get_order'], $toolbox->names());
        $this->assertSame(['mcp1_get_order'], $toolbox->functionNamesFor(['get_order', 'other']));
        $this->assertSame('Shows an order. (Shop)', $toolbox->summary('mcp1_get_order'));
        $this->assertSame('[Shop] Shows an order. More text.', $toolbox->definition('mcp1_get_order')['function']['description']);

        $toolbox->prepare($agent, false);
        $this->assertSame([], $toolbox->names(), 'without the edition no tools');
    }

    public function testAToolLockedDuringTheRequestDoesNotRun(): void
    {
        $connection = $this->connection(1);
        $toolbox = $this->prepared();
        $connection->locked = serialize(['get_order']);

        $this->assertSame('TOOL NOT RUN: an administrator has locked this tool or its connection.', $toolbox->call('mcp1_get_order', ['id' => 42]));
        $this->assertSame('TOOL NOT RUN: this tool is not available to this assistant.', $toolbox->call('mcp1_delete_order', []));
        $this->assertSame([], $this->calls);
        $this->assertSame([['get_order', 'refused', 'locked']], $this->log());
    }

    public function testPersonalDataIsMaskedInBothDirections(): void
    {
        $this->connection(1, ['personal_data' => ConnectionModel::DATA_MASK]);
        $this->result = 'Order 42 of max.mustermann@example.com';

        $answer = $this->prepared()->call('mcp1_get_order', ['query' => 'orders of max.mustermann@example.com', 'filter' => ['phone' => '+43 664 1234567'], 'limit' => 5]);

        $this->assertSame(['query' => 'orders of [removed: e-mail address]', 'filter' => ['phone' => '[removed: phone number]'], 'limit' => 5], $this->calls[0]['arguments']);
        $this->assertSame("TOOL RESULT:\nOrder 42 of [removed: e-mail address]", $answer);
        $this->assertSame([['get_order', 'ok', 'masked']], $this->log());
    }

    public function testMaskedPersonalDataIsNeverStoredByAToolThatChangesData(): void
    {
        $tools = json_encode(['read_at' => 1, 'url' => self::URL, 'server' => 'Shop 1', 'tools' => [
            ['name' => 'create_booking', 'description' => 'Books.', 'inputSchema' => ['type' => 'object'], 'annotations' => ['readOnlyHint' => false]],
        ], 'new' => []]);
        $this->connection(1, ['personal_data' => ConnectionModel::DATA_MASK, 'tools' => $tools]);
        $toolbox = $this->toolbox();
        $toolbox->prepare(Models::make(AgentModel::class, ['id' => 9, 'mcp_tools' => serialize(['1:create_booking'])]), true);

        $answer = $toolbox->call('mcp1_create_booking', ['booker' => ['lastName' => 'Mustermann', 'email' => 'max.mustermann@example.com']]);

        $this->assertStringStartsWith('TOOL NOT RUN: this connection masks personal data (email)', $answer);
        $this->assertSame([], $this->calls, 'no booking with "[removed: e-mail address]" as contact');
        $this->assertSame([['create_booking', 'refused', 'personal']], $this->log());
        $this->assertStringStartsWith('TOOL RESULT', $toolbox->call('mcp1_create_booking', ['booker' => ['lastName' => 'Mustermann']]), 'without personal data it runs');
    }

    public function testBlockedPersonalDataNeverLeaves(): void
    {
        $this->connection(1, ['personal_data' => ConnectionModel::DATA_BLOCK]);
        $toolbox = $this->prepared();

        $this->assertSame('TOOL NOT RUN: the arguments contain personal data (email), which this connection does not send. Do not retry with it.', $toolbox->call('mcp1_get_order', ['email' => 'max.mustermann@example.com']));
        $this->assertSame([], $this->calls);

        $this->result = 'Customer phone +43 664 1234567';
        $this->assertSame('TOOL RESULT WITHHELD: the result contains personal data (phone), which this connection does not pass on.', $toolbox->call('mcp1_get_order', ['id' => 42]));
        $this->assertSame([['get_order', 'refused', 'personal'], ['get_order', 'ok', 'withheld']], $this->log());
    }

    public function testSendPassesEverything(): void
    {
        $this->connection(1, ['personal_data' => ConnectionModel::DATA_SEND]);
        $this->result = 'Customer max.mustermann@example.com';

        $this->assertSame("TOOL RESULT:\nCustomer max.mustermann@example.com", $this->prepared()->call('mcp1_get_order', ['email' => 'max.mustermann@example.com']));
        $this->assertSame(['email' => 'max.mustermann@example.com'], $this->calls[0]['arguments']);
    }

    public function testErrorsAreFactsForTheModel(): void
    {
        $this->connection(1);
        $this->isError = true;
        $this->result = '';

        $this->assertSame("TOOL ERROR from the server:\nno details", $this->prepared()->call('mcp1_get_order', []));

        $this->connection(4, ['url' => 'http://insecure.example.com/mcp', 'tools' => $this->tools(['get_order', 'delete_order'], 'http://insecure.example.com/mcp')]);
        $toolbox = $this->toolbox();
        $toolbox->prepare(Models::make(AgentModel::class, ['id' => 9, 'mcp_tools' => serialize(['4:get_order'])]), true);
        $this->assertSame('TOOL FAILED (the call did not reach a result; this proves nothing about the data): Only HTTPS addresses are allowed for MCP servers.', $toolbox->call('mcp4_get_order', []));
        $this->assertSame([['get_order', 'error', ''], ['get_order', 'failed', 'unreachable']], $this->log());
    }

    public function testTheCredentialsOfTheConnection(): void
    {
        $key = $this->connection(1, ['auth' => ConnectionModel::AUTH_KEY, 'api_key' => $this->cipher->encrypt('sk-mcp&amp;1')]);
        $this->assertSame(['Authorization' => 'Bearer sk-mcp&1'], $this->connections()->endpoint($key)->headers);

        $headers = $this->connection(2, ['auth' => ConnectionModel::AUTH_HEADERS, 'headers' => serialize([['key' => 'X-Api-Key', 'value' => $this->cipher->encrypt('k1')], ['key' => "Bad\r\nHeader", 'value' => 'x'], ['key' => '', 'value' => 'y']])]);
        $this->assertSame(['X-Api-Key' => 'k1'], $this->connections()->endpoint($headers)->headers);

        $none = $this->connection(3, ['auth' => ConnectionModel::AUTH_NONE, 'api_key' => $this->cipher->encrypt('unused')]);
        $this->assertSame([], $this->connections()->endpoint($none)->headers);
    }

    public function testToolsThatDoMoreThanReadAreLockedWhenTheyFirstAppear(): void
    {
        $connection = $this->connection(1, ['tools' => null, 'locked' => null]);
        $listed = [['name' => 'get_order', 'annotations' => ['readOnlyHint' => true]], ['name' => 'delete_order', 'annotations' => ['destructiveHint' => true]], ['name' => 'unmarked']];
        $connections = $this->connections($listed);

        $connections->readTools($connection);
        $this->assertSame(['delete_order', 'unmarked'], $connections->locked($connection));
        $this->assertSame([], $connections->toolData($connection)['new'], 'a first read is no news');

        $listed[] = ['name' => 'list_orders', 'annotations' => ['readOnlyHint' => true]];
        $this->connections($listed)->readTools($connection);
        $this->assertSame(['list_orders'], $connections->toolData($connection)['new']);
        $this->assertSame(['delete_order', 'unmarked'], $connections->locked($connection));
        $this->assertSame(serialize(['delete_order', 'unmarked']), $this->db->fetchOne('SELECT locked FROM '.Connections::TABLE.' WHERE id = 1'));
    }

    public function testATemplateFreesTheToolsItUnlocks(): void
    {
        $project = sys_get_temp_dir().'/nsa-project-'.bin2hex(random_bytes(4));
        mkdir($project.'/config/superagent/mcp-templates/shop', 0777, true);
        file_put_contents($project.'/config/superagent/mcp-templates/shop/template.json', json_encode(['name' => 'Shop', 'url' => 'https://mcp.example.com/mcp', 'tools' => ['locked' => ['delete_*'], 'unlocked' => ['update_order', 'delete_order']]]));

        try {
            $connection = $this->connection(1, ['tools' => null, 'locked' => null, 'template' => 'shop']);
            $listed = [['name' => 'update_order', 'annotations' => ['readOnlyHint' => false]], ['name' => 'delete_order', 'annotations' => ['destructiveHint' => true]], ['name' => 'create_order']];

            $this->connections($listed, $project)->readTools($connection);

            $this->assertSame(['delete_order', 'create_order'], $this->connections([], $project)->locked($connection), 'unlocked is free, locked wins, the rest stays locked');
        } finally {
            unlink($project.'/config/superagent/mcp-templates/shop/template.json');
            rmdir($project.'/config/superagent/mcp-templates/shop');
            rmdir($project.'/config/superagent/mcp-templates');
            rmdir($project.'/config/superagent');
            rmdir($project.'/config');
            rmdir($project);
        }
    }

    public function testProtectedToolsNeedTheGuestCheckAndReachTheServiceOnlyAfterIt(): void
    {
        $project = sys_get_temp_dir().'/nsa-project-'.bin2hex(random_bytes(4));
        mkdir($project.'/config/superagent/mcp-templates/shop', 0777, true);
        file_put_contents($project.'/config/superagent/mcp-templates/shop/template.json', json_encode([
            'name' => 'Shop',
            'url' => self::URL,
            'guest_check' => ['lookups' => [['tool' => 'get_order', 'arguments' => ['id' => '{reference}']]], 'persons' => ['customer'], 'arguments' => ['id'], 'protected' => ['get_order']],
        ]));

        try {
            $this->connection(1, ['template' => 'shop', 'personal_data' => ConnectionModel::DATA_SEND]);
            $toolbox = new McpToolbox($this->connections([], $project), $this->client(), new ToolLog($this->db), new PersonalDataGuard(), new NullLogger(), new GuestCheck('test-secret', new ArrayAdapter(), new RequestStack()), $this->downloads);
            $toolbox->prepare(Models::make(AgentModel::class, ['id' => 9, 'mcp_tools' => serialize(['1:get_order'])]), true);

            $this->assertSame(['mcp1_get_order', 'mcp1_verify_guest'], $toolbox->names());
            $this->assertSame(['mcp1_verify_guest'], $toolbox->functionNamesFor([GuestCheck::TOOL]), 'skills name it as verify_guest');
            $this->assertSame('mcp1_verify_guest', $toolbox->definition('mcp1_verify_guest')['function']['name']);

            $this->assertStringStartsWith('TOOL NOT RUN: this tool shows or changes', $toolbox->call('mcp1_get_order', ['id' => 'A-1']));
            $this->assertSame([], $this->calls, 'nothing reached the service before the check');

            $this->result = (string) json_encode(['id' => 'A-1', 'customer' => ['lastName' => 'Kunde', 'email' => 'k@example.com']]);
            $this->assertStringStartsWith('NOT VERIFIED', $toolbox->call('mcp1_verify_guest', ['reference' => 'A-1', 'name' => 'Kunde', 'contact' => 'x@example.com']));
            $this->assertStringStartsWith('VERIFIED', $toolbox->call('mcp1_verify_guest', ['reference' => 'A-1', 'name' => 'Kunde', 'contact' => 'k@example.com']));
            $this->assertSame([['name' => 'get_order', 'arguments' => ['id' => 'A-1']], ['name' => 'get_order', 'arguments' => ['id' => 'A-1']]], $this->calls, 'the lookups, server side');

            $this->assertStringStartsWith('TOOL RESULT:', $toolbox->call('mcp1_get_order', ['id' => 'A-1']));
            $this->assertStringStartsWith('TOOL NOT RUN: B-2 does not belong', $toolbox->call('mcp1_get_order', ['id' => 'B-2']));
            $this->assertNotSame('', $toolbox->guestToken());

            // A file of the verified record: for the visitor, never for the model.
            $pdf = "%PDF-1.7\nInvoice of Kunde\n%%EOF";
            $this->result = base64_encode($pdf);
            $answer = $toolbox->call('mcp1_get_order', ['id' => 'A-1']);
            $this->assertStringStartsWith("TOOL RESULT:\n[file: document, PDF, 1 KB]\n\nFILE READY: \"A-1.pdf\".", $answer, 'not withheld: the call named the verified record');
            $this->assertStringNotContainsString(base64_encode($pdf), $answer);
            $this->assertStringNotContainsString('Kunde', $answer);
            $this->assertSame([['token' => $toolbox->files()[0]['token'], 'name' => 'A-1.pdf', 'size' => \strlen($pdf)]], $toolbox->files());
            $this->assertSame($pdf, $this->downloads->take($toolbox->files()[0]['token'])['data'] ?? null);

            // The licence falls back: the proof the widget brings along counts for nothing, no tool is offered.
            $token = $toolbox->guestToken();
            $toolbox->prepare(Models::make(AgentModel::class, ['id' => 9, 'mcp_tools' => serialize(['1:get_order'])]), false, $token);
            $this->assertSame([], $toolbox->names());
            $this->assertSame('', $toolbox->guestToken(), 'no proof handed on without the connections of the licence');

            // With the licence again, the same token still proves the guest.
            $toolbox->prepare(Models::make(AgentModel::class, ['id' => 9, 'mcp_tools' => serialize(['1:get_order'])]), true, $token);
            $this->assertNotSame('', $toolbox->guestToken());
        } finally {
            unlink($project.'/config/superagent/mcp-templates/shop/template.json');
            rmdir($project.'/config/superagent/mcp-templates/shop');
            rmdir($project.'/config/superagent/mcp-templates');
            rmdir($project.'/config/superagent');
            rmdir($project.'/config');
            rmdir($project);
        }
    }

    public function testFunctionNamesForTheProvider(): void
    {
        $this->assertSame('mcp3_get_order', McpToolbox::functionName(3, 'get_order'));
        $this->assertSame('mcp3_orders_list_v2', McpToolbox::functionName(3, 'orders.list/v2'));
        $long = McpToolbox::functionName(3, str_repeat('a', 80));
        $this->assertSame(64, \strlen($long));
        $this->assertNotSame($long, McpToolbox::functionName(3, str_repeat('a', 79).'b'), 'a hash keeps long names apart');
    }

    public function testTheLogIsKeptThirtyDays(): void
    {
        $this->db->insert(ToolLog::TABLE, ['tstamp' => time() - 31 * 86400, 'tool' => 'old']);
        $this->db->insert(ToolLog::TABLE, ['tstamp' => time() - 29 * 86400, 'tool' => 'kept']);

        (new ToolLog($this->db))();

        $this->assertSame(['kept'], $this->db->fetchFirstColumn('SELECT tool FROM '.ToolLog::TABLE));
        (new ToolLog(TestDatabase::create()))->write(1, 1, 'x', ToolLog::OK, 5); // no table: the chat goes on
    }

    /** @param array<string, mixed> $row */
    private function connection(int $id, array $row = []): ConnectionModel
    {
        $row += ['id' => $id, 'tstamp' => 1, 'name' => 'Shop', 'url' => self::URL, 'auth' => ConnectionModel::AUTH_NONE, 'confirmed_at' => 1, 'tools' => $this->tools(['get_order', 'delete_order'], $row['url'] ?? self::URL), 'locked' => serialize([]), 'personal_data' => ConnectionModel::DATA_MASK, 'template' => ''];
        $this->db->insert(Connections::TABLE, array_intersect_key($row, array_flip(['id', 'tstamp', 'name', 'url', 'tools', 'locked'])));

        return Models::register(ConnectionModel::class, $row);
    }

    /** @param list<string> $names */
    private function tools(array $names, string $url): string
    {
        return json_encode(['read_at' => 1, 'url' => $url, 'server' => 'Shop 1', 'tools' => array_map(static fn (string $n): array => ['name' => $n, 'description' => 'Shows an order. More text.', 'inputSchema' => ['type' => 'object']], $names), 'new' => []]);
    }

    private function prepared(): McpToolbox
    {
        $toolbox = $this->toolbox();
        $toolbox->prepare(Models::make(AgentModel::class, ['id' => 9, 'mcp_tools' => serialize(['1:get_order'])]), true);

        return $toolbox;
    }

    private function toolbox(): McpToolbox
    {
        return new McpToolbox($this->connections(), $this->client(), new ToolLog($this->db), new PersonalDataGuard(), new NullLogger(), new GuestCheck('test-secret', new ArrayAdapter(), new RequestStack()), $this->downloads);
    }

    /** @param list<array<string, mixed>> $listed tools/list of the server */
    private function connections(array $listed = [], string $project = ''): Connections
    {
        $client = $this->client($listed);
        $templates = new TemplateCatalog('' !== $project ? $project : sys_get_temp_dir().'/neo-no-project', new NullLogger());

        return new Connections($this->db, $this->cipher, $client, new OAuthFlow(new MockHttpClient(), $client, $this->cipher, $this->db, new RequestStack(), $templates), $templates);
    }

    /** @param list<array<string, mixed>> $listed */
    private function client(array $listed = []): McpClient
    {
        return new McpClient(new MockHttpClient(function (string $method, string $url, array $options) use ($listed): MockResponse {
            $message = json_decode($options['body'], true);
            $result = match ($message['method']) {
                'initialize' => ['serverInfo' => ['name' => 'Shop', 'version' => '1']],
                'tools/list' => ['tools' => $listed],
                'tools/call' => ['content' => [['type' => 'text', 'text' => $this->result]], 'isError' => $this->isError],
                default => null,
            };

            if ('tools/call' === $message['method']) {
                $this->calls[] = $message['params'];
            }

            return null === $result ? new MockResponse('', ['http_code' => 202]) : new MockResponse(json_encode(['jsonrpc' => '2.0', 'id' => $message['id'], 'result' => $result]));
        }));
    }

    /** @return list<array{string, string, string}> */
    private function log(): array
    {
        return array_map('array_values', $this->db->fetchAllAssociative('SELECT tool, status, note FROM '.ToolLog::TABLE.' ORDER BY id'));
    }
}

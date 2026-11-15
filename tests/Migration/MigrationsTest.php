<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Migration;

use Doctrine\DBAL\Connection;
use Neo\BasicBundle\Access\AccessSettings;
use Neo\BasicBundle\Ai\AiConnection;
use Neo\BasicBundle\Security\SecretCipher;
use Neo\SuperAgentBundle\Migration\AdoptAiConnectionMigration;
use Neo\SuperAgentBundle\Migration\AdoptSerpApiKeyMigration;
use Neo\SuperAgentBundle\Migration\StarterQuestionsMigration;
use Neo\SuperAgentBundle\Migration\TableColumns;
use Neo\SuperAgentBundle\SuperAgent;
use Neo\SuperAgentBundle\Tests\Support\TestDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AdoptAiConnectionMigration::class)]
#[CoversClass(AdoptSerpApiKeyMigration::class)]
#[CoversClass(StarterQuestionsMigration::class)]
#[CoversClass(TableColumns::class)]
final class MigrationsTest extends TestCase
{
    private SecretCipher $cipher;

    protected function setUp(): void
    {
        $this->cipher = new SecretCipher('test-secret');
    }

    public function testColumnsOnDbal3AndDbal4(): void
    {
        $db = TestDatabase::create('tl_neo_agent_language');
        $schema = $db->createSchemaManager();

        $this->assertTrue(TableColumns::has($schema, 'tl_neo_agent_language', 'STARTER_QUESTIONS'));
        $this->assertFalse(TableColumns::has($schema, 'tl_neo_agent_language', 'missing'));
        $this->assertFalse(TableColumns::has($schema, 'tl_missing', 'id'));
        $this->assertContains('suggestions', TableColumns::names($schema, 'tl_neo_agent_language'));
    }

    public function testTheKeyOfThePublishedAssistantMovesToTheSharedConnectionOnce(): void
    {
        $db = TestDatabase::create('tl_neo_access', 'tl_neo_agent');
        $this->agent($db, ['provider' => 'custom', 'base_url' => 'https://router.example.com/v1/', 'api_key' => $this->secret('sk-draft'), 'published' => '']);
        $published = $this->agent($db, ['provider' => 'requesty', 'api_key' => $this->secret('sk-live&amp;1'), 'published' => '1']);
        $this->agent($db, ['provider' => 'openai', 'api_key' => '', 'published' => '1']);

        $migration = new AdoptAiConnectionMigration($db, $this->connection($db), $this->cipher);
        $this->assertTrue($migration->shouldRun());

        $result = $migration->run();
        $this->assertSame(sprintf('Taken over the key and route "requesty_eu" of assistant ID %d. 1 other assistant(s) had a key of their own; they now use this one.', $published), $result->getMessage());

        $connection = $this->connection($db);
        $this->assertSame('requesty_eu', $connection->route(), 'the early route "requesty" is the EU router');
        $this->assertSame('sk-live&1', $connection->apiKey());
        $this->assertFalse((new AdoptAiConnectionMigration($db, $connection, $this->cipher))->shouldRun(), 'once');
        $this->assertNotSame('', (string) $db->fetchOne('SELECT api_key FROM tl_neo_agent WHERE id = ?', [$published]), 'the old column stays');
    }

    public function testAnOwnRouterAddressIsKept(): void
    {
        $db = TestDatabase::create('tl_neo_access', 'tl_neo_agent');
        $this->agent($db, ['provider' => 'custom', 'base_url' => 'https://router.example.com/v1', 'api_key' => $this->secret('sk-own')]);

        (new AdoptAiConnectionMigration($db, $this->connection($db), $this->cipher))->run();

        $this->assertSame(['custom', 'https://router.example.com/v1'], [$this->connection($db)->route(), $this->connection($db)->baseUrl()]);
    }

    public function testAKeyThatIsThereAlreadyIsNotOverwritten(): void
    {
        $db = TestDatabase::create('tl_neo_access', 'tl_neo_agent');
        $this->connection($db)->store('openai', '', 'sk-shared');
        $id = $this->agent($db, ['provider' => 'requesty_global', 'api_key' => $this->secret('sk-agent')]);

        $migration = new AdoptAiConnectionMigration($db, $this->connection($db), $this->cipher);
        $this->assertTrue($migration->shouldRun(), 'still listed: the run is recorded');
        $this->assertSame(sprintf('The shared AI connection already has a key; the key of assistant ID %d was not taken over.', $id), $migration->run()->getMessage());
        $this->assertSame('sk-shared', $this->connection($db)->apiKey());
        $this->assertFalse((new AdoptAiConnectionMigration($db, $this->connection($db), $this->cipher))->shouldRun());
    }

    public function testNothingToTakeOverBeforeTheTablesExist(): void
    {
        $db = TestDatabase::create('tl_neo_agent');
        $this->agent($db, ['api_key' => $this->secret('sk-agent')]);

        $this->assertFalse((new AdoptAiConnectionMigration($db, $this->connection($db), $this->cipher))->shouldRun(), 'no shared connection yet');
        $this->assertFalse((new AdoptAiConnectionMigration(TestDatabase::create('tl_neo_access'), $this->connection(TestDatabase::create('tl_neo_access')), $this->cipher))->shouldRun());
    }

    public function testTheSerpApiKeyMovesOnceAndIsNotOverwritten(): void
    {
        $db = TestDatabase::create('tl_neo_access', 'tl_neo_agent');
        $id = $this->agent($db, ['web_search_key' => $this->secret('serp-1'), 'published' => '1']);
        $access = new AccessSettings($db, $this->cipher);

        $migration = new AdoptSerpApiKeyMigration($db, $access, $this->cipher);
        $this->assertTrue($migration->shouldRun());
        $this->assertSame(sprintf('Taken over the SerpAPI key of assistant ID %d.', $id), $migration->run()->getMessage());

        $access = new AccessSettings($db, $this->cipher);
        $this->assertSame('serp-1', $access->secret('superagent_serpapi_key'));
        $this->assertFalse((new AdoptSerpApiKeyMigration($db, $access, $this->cipher))->shouldRun());

        // Removed later: it does not come back.
        $access->store(['superagent_serpapi_key' => '']);
        $this->assertFalse((new AdoptSerpApiKeyMigration($db, new AccessSettings($db, $this->cipher), $this->cipher))->shouldRun());
    }

    public function testSuggestedQuestionsBecomeStarterQuestions(): void
    {
        $db = TestDatabase::create('tl_neo_agent_language');
        $db->insert('tl_neo_agent_language', ['tstamp' => 1, 'language' => 'en', 'suggestions' => serialize(['What do you offer?', ''])]);
        $db->insert('tl_neo_agent_language', ['tstamp' => 1, 'language' => 'de', 'suggestions' => serialize(['Alt?']), 'starter_questions' => serialize([['question' => 'Neu?', 'answer' => 'Ja.']])]);
        $db->insert('tl_neo_agent_language', ['tstamp' => 1, 'language' => 'fr', 'suggestions' => serialize([])]);

        $migration = new StarterQuestionsMigration($db);
        $this->assertTrue($migration->shouldRun());
        $this->assertSame('Converted the suggested questions of 1 language records into starter questions.', $migration->run()->getMessage());

        $rows = $db->fetchAllKeyValue('SELECT language, starter_questions FROM tl_neo_agent_language ORDER BY id');
        $this->assertSame([['question' => 'What do you offer?', 'answer' => '']], unserialize($rows['en']));
        $this->assertSame([['question' => 'Neu?', 'answer' => 'Ja.']], unserialize($rows['de']), 'written starter questions stay');
        $this->assertNull($rows['fr']);
        $this->assertNotNull($db->fetchOne("SELECT suggestions FROM tl_neo_agent_language WHERE language = 'en'"), 'the old column stays');
        $this->assertFalse($migration->shouldRun(), 'nothing left');
    }

    private function connection(Connection $db): AiConnection
    {
        return new AiConnection(new AccessSettings($db, $this->cipher));
    }

    private function secret(string $plain): string
    {
        return $this->cipher->encrypt($plain, SuperAgent::CIPHER_SCOPE);
    }

    /** @param array<string, mixed> $values */
    private function agent(Connection $db, array $values): int
    {
        $db->insert('tl_neo_agent', $values + ['tstamp' => 1]);

        return (int) $db->lastInsertId();
    }
}

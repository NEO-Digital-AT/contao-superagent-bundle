<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Neo\BasicBundle\Ai\AiConnection;
use Neo\BasicBundle\Security\SecretCipher;
use Neo\SuperAgentBundle\SuperAgent;

/**
 * Route and key used to be stored per assistant; they now live once on
 * "Access keys and licences" (Basic bundle). An installation that has no key there yet
 * gets the key and route of its assistant, once: the first published assistant
 * with a key, otherwise the first assistant with a key. Nothing stored in the
 * settings is overwritten; the old columns stay untouched. The run is recorded
 * (AiConnection::markAdopted), so it stays listed even when another extension
 * fills the connection in the same run.
 */
final class AdoptAiConnectionMigration extends AbstractMigration
{
    private const SOURCE = 'superagent';

    /** Address of the old route "requesty" of early versions. */
    private const LEGACY_ROUTES = ['requesty' => 'https://router.eu.requesty.ai/v1'];

    public function __construct(
        private readonly Connection $db,
        private readonly AiConnection $connection,
        private readonly SecretCipher $cipher,
    ) {
    }

    public function getName(): string
    {
        return 'SuperAgent: take over route and key of the assistant into the shared AI connection';
    }

    public function shouldRun(): bool
    {
        return null !== $this->candidate();
    }

    public function run(): MigrationResult
    {
        $agent = $this->candidate();

        if (null === $agent) {
            return $this->createResult(true);
        }

        $provider = (string) $agent['provider'];

        if (isset(AiConnection::ROUTES[$provider]) && AiConnection::CUSTOM !== $provider) {
            $route = ['route' => $provider, 'base_url' => ''];
        } else {
            $route = AiConnection::routeFor((string) (self::LEGACY_ROUTES[$provider] ?? $agent['base_url']));
        }

        $this->connection->markAdopted(self::SOURCE);

        // Another extension may have filled the shared connection in the same run.
        if ($this->connection->hasKey()) {
            return $this->createResult(true, sprintf('The shared AI connection already has a key; the key of assistant ID %d was not taken over.', $agent['id']));
        }

        $this->connection->store($route['route'], $route['base_url'], $agent['key']);

        $others = (int) $this->db->fetchOne("SELECT COUNT(*) FROM tl_neo_agent WHERE api_key <> '' AND id <> ?", [$agent['id']]);

        return $this->createResult(true, sprintf(
            'Taken over the key and route "%s" of assistant ID %d.%s',
            $route['route'],
            $agent['id'],
            $others > 0 ? sprintf(' %d other assistant(s) had a key of their own; they now use this one.', $others) : '',
        ));
    }

    /** @return array{id: int, provider: string, base_url: string, key: string}|null */
    private function candidate(): array|null
    {
        // Not "already has a key": the answer must not change while other
        // takeover migrations run in the same process. On a new installation the
        // table of the connection only exists in the second pass.
        if (!$this->connection->isStorable() || $this->connection->isAdopted(self::SOURCE)) {
            return null;
        }

        $schema = $this->db->createSchemaManager();

        if (!TableColumns::has($schema, 'tl_neo_agent', 'api_key')) {
            return null;
        }

        $rows = $this->db->fetchAllAssociative("SELECT id, provider, base_url, api_key FROM tl_neo_agent WHERE api_key <> '' ORDER BY published DESC, id ASC");

        foreach ($rows as $row) {
            $key = trim(html_entity_decode($this->cipher->decrypt((string) $row['api_key'], SuperAgent::CIPHER_SCOPE), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            if ('' !== $key) {
                return ['id' => (int) $row['id'], 'provider' => (string) $row['provider'], 'base_url' => (string) $row['base_url'], 'key' => $key];
            }
        }

        return null;
    }
}

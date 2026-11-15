<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Neo\BasicBundle\Access\AccessSettings;
use Neo\BasicBundle\Security\SecretCipher;
use Neo\SuperAgentBundle\SuperAgent;

/**
 * The SerpAPI key used to be stored per assistant; it now lives once on
 * "Access keys and licences". An installation without a key there gets the key of
 * its assistant, once: the first published assistant with a key, otherwise the
 * first assistant with a key. A key already stored there is not overwritten; the
 * old column stays untouched. The run is recorded, so a key removed later does
 * not come back.
 */
final class AdoptSerpApiKeyMigration extends AbstractMigration
{
    private const SOURCE = 'superagent_serpapi';
    private const COLUMN = 'superagent_serpapi_key';

    public function __construct(
        private readonly Connection $db,
        private readonly AccessSettings $access,
        private readonly SecretCipher $cipher,
    ) {
    }

    public function getName(): string
    {
        return 'SuperAgent: take over the SerpAPI key of the assistant into "Access keys and licences"';
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

        $this->access->markAdopted(self::SOURCE);

        if ($this->access->hasValue(self::COLUMN)) {
            return $this->createResult(true, sprintf('A SerpAPI key is already stored; the key of assistant ID %d was not taken over.', $agent['id']));
        }

        $this->access->store([self::COLUMN => $agent['key']], [self::COLUMN]);

        return $this->createResult(true, sprintf('Taken over the SerpAPI key of assistant ID %d.', $agent['id']));
    }

    /** @return array{id: int, key: string}|null */
    private function candidate(): array|null
    {
        if (!$this->access->hasColumns('adopted', self::COLUMN) || $this->access->isAdopted(self::SOURCE)) {
            return null;
        }

        $schema = $this->db->createSchemaManager();

        if (!TableColumns::has($schema, 'tl_neo_agent', 'web_search_key')) {
            return null;
        }

        $rows = $this->db->fetchAllAssociative("SELECT id, web_search_key FROM tl_neo_agent WHERE web_search_key <> '' ORDER BY published DESC, id ASC");

        foreach ($rows as $row) {
            $key = trim(html_entity_decode($this->cipher->decrypt((string) $row['web_search_key'], SuperAgent::CIPHER_SCOPE), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            if ('' !== $key) {
                return ['id' => (int) $row['id'], 'key' => $key];
            }
        }

        return null;
    }
}

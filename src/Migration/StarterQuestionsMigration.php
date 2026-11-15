<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Neo\SuperAgentBundle\Chat\StarterQuestions;

/**
 * The suggested questions of a language record (suggestions, one question per
 * row) become its starter questions (starter_questions, rows of question and
 * an optional answer), without an answer. Only records whose starter questions
 * were never written (NULL) and that have suggested questions; the old column
 * stays as it is, so nothing is lost. Until this has run, the chat reads the
 * old column (StarterQuestions::of()). Runs again only for records it has not
 * converted yet.
 */
final class StarterQuestionsMigration extends AbstractMigration
{
    private const TABLE = 'tl_neo_agent_language';

    public function __construct(private readonly Connection $db)
    {
    }

    public function getName(): string
    {
        return 'SuperAgent: suggested questions of the language records become starter questions';
    }

    public function shouldRun(): bool
    {
        return [] !== $this->pending();
    }

    public function run(): MigrationResult
    {
        $pending = $this->pending();

        foreach ($pending as $id => $rows) {
            $this->db->update(self::TABLE, ['starter_questions' => serialize($rows)], ['id' => $id]);
        }

        return $this->createResult(true, sprintf('Converted the suggested questions of %d language records into starter questions.', \count($pending)));
    }

    /** @return array<int, list<array{question: string, answer: string}>> record ID => starter questions */
    private function pending(): array
    {
        $schema = $this->db->createSchemaManager();

        if (!TableColumns::has($schema, self::TABLE, 'suggestions') || !TableColumns::has($schema, self::TABLE, 'starter_questions')) {
            return [];
        }

        $pending = [];

        foreach ($this->db->fetchAllAssociative('SELECT id, suggestions FROM '.self::TABLE.' WHERE starter_questions IS NULL AND suggestions IS NOT NULL') as $row) {
            $rows = StarterQuestions::fromList($row['suggestions']);

            if ([] !== $rows) {
                $pending[(int) $row['id']] = $rows;
            }
        }

        return $pending;
    }
}

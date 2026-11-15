<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Neo\SuperAgentBundle\Skill\TemplateSkills;

/**
 * After an update of the extension (or of a project template): the installed
 * skills of connection templates take over the new text of their file
 * (TemplateSkills). Runs whenever such a text changed.
 */
final class TemplateSkillsMigration extends AbstractMigration
{
    public function __construct(private readonly TemplateSkills $skills)
    {
    }

    public function getName(): string
    {
        return 'SuperAgent: skills of connection templates take over the new text of their template';
    }

    public function shouldRun(): bool
    {
        return [] !== $this->skills->outdated();
    }

    public function run(): MigrationResult
    {
        $names = $this->skills->refresh();

        return $this->createResult(true, sprintf('Updated %d skills of connection templates: %s.', \count($names), implode(', ', $names)));
    }
}

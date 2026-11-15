<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Skill;

use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Neo\SuperAgentBundle\Mcp\McpToolbox;
use Neo\SuperAgentBundle\Model\AgentModel;

/**
 * The skills administrators installed (tl_neo_agent_skill, edition Plus) and
 * those an assistant may use (tl_neo_agent.skills). Only the installed text is
 * used; nothing is fetched while the chat runs.
 */
final class InstalledSkills
{
    public const TABLE = 'tl_neo_agent_skill';

    public function __construct(private readonly Connection $db)
    {
    }

    /**
     * Installed skills (with a confirmed text), by name.
     *
     * @return array<string, array{id: int, name: string, description: string, body: string, tools: list<string>, depends: list<string>}>
     */
    public function all(): array
    {
        try {
            $rows = $this->db->fetchAllAssociative('SELECT id, name, description, body, tools, depends FROM '.self::TABLE." WHERE body != '' ORDER BY name");
        } catch (\Throwable) {
            // Table not created yet (before the database update).
            return [];
        }

        $skills = [];

        foreach ($rows as $row) {
            $skills[(string) $row['name']] = [
                'id' => (int) $row['id'],
                'name' => (string) $row['name'],
                'description' => (string) $row['description'],
                'body' => (string) $row['body'],
                'tools' => array_values(array_map('strval', StringUtil::deserialize((string) $row['tools'], true))),
                'depends' => array_values(array_map('strval', StringUtil::deserialize((string) $row['depends'], true))),
            ];
        }

        return $skills;
    }

    /**
     * The installed skills ticked at the assistant, as modules of the skill
     * package. A skill's tools are the tools of the connections this assistant
     * may use whose server name the skill names.
     *
     * @return array<string, array{description: string, body: string, tools: list<string>, dependsOn: list<string>}>
     */
    public function forAgent(AgentModel $agent, McpToolbox $mcp): array
    {
        $ticked = array_map('strval', StringUtil::deserialize((string) $agent->skills, true));

        if ([] === $ticked) {
            return [];
        }

        $modules = [];

        foreach ($this->all() as $name => $skill) {
            if (!\in_array($name, $ticked, true)) {
                continue;
            }

            $modules[$name] = [
                'description' => $skill['description'],
                'body' => $skill['body'],
                'tools' => $mcp->functionNamesFor($skill['tools']),
                'dependsOn' => $skill['depends'],
            ];
        }

        return $modules;
    }
}

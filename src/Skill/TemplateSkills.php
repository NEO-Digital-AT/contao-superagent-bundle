<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Skill;

use Doctrine\DBAL\Connection;
use Neo\SuperAgentBundle\Mcp\TemplateCatalog;

/**
 * Skills a connection template brought along (source "template") follow the
 * file of the template: when the bundle or the project ships a new text of
 * such a skill, the installed copy takes it over. Like the first installation
 * (ConnectionCallbacks::installTemplateSkill) this needs no confirmation, the
 * text comes from the same place as the template. A new text that would rename
 * the skill or fails the checks of a skill stays out; the administrator sees it
 * with "Check for a new version" of the installed skill.
 */
final class TemplateSkills
{
    public function __construct(
        private readonly Connection $db,
        private readonly TemplateCatalog $templates,
    ) {
    }

    /**
     * Installed template skills whose file has a new, valid text.
     *
     * @return list<array{id: int, file: SkillFile, hash: string}>
     */
    public function outdated(): array
    {
        try {
            $rows = $this->db->fetchAllAssociative('SELECT id, url, name, version_hash FROM '.InstalledSkills::TABLE." WHERE source = 'template' AND body != ''");
        } catch (\Throwable) {
            // Table or columns not created yet (before the database update).
            return [];
        }

        $outdated = [];

        foreach ($rows as $row) {
            // Reference: the template id, or "<template id>/<skill>" for one of several skills.
            $reference = (string) $row['url'];
            $path = (string) $this->templates->get(explode('/', $reference, 2)[0])?->skillFileFor($reference);

            if ('' === $path || !is_file($path)) {
                continue;
            }

            $content = (string) file_get_contents($path);
            $hash = hash('sha256', $content);

            if ($hash === (string) $row['version_hash']) {
                continue;
            }

            $file = SkillFile::parse($content);
            $check = $file->check([], [$file->name, ...$file->depends], $file->tools);

            if ($file->name !== (string) $row['name'] || [] !== $check['errors']) {
                continue;
            }

            $outdated[] = ['id' => (int) $row['id'], 'file' => $file, 'hash' => $hash];
        }

        return $outdated;
    }

    /**
     * Takes over the new texts.
     *
     * @return list<string> names of the updated skills
     */
    public function refresh(): array
    {
        $names = [];

        foreach ($this->outdated() as $entry) {
            $file = $entry['file'];

            $this->db->update(InstalledSkills::TABLE, [
                'description' => $file->description,
                'body' => $file->body,
                'tools' => serialize($file->tools),
                'depends' => serialize($file->depends),
                'version_hash' => $entry['hash'],
                'version_at' => time(),
                'pending' => null,
                'tstamp' => time(),
            ], ['id' => $entry['id']]);

            $names[] = $file->name;
        }

        return $names;
    }
}

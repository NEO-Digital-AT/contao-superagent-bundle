<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Skill;

use Neo\SuperAgentBundle\Mcp\TemplateCatalog;
use Neo\SuperAgentBundle\Migration\TemplateSkillsMigration;
use Neo\SuperAgentBundle\Skill\InstalledSkills;
use Neo\SuperAgentBundle\Skill\TemplateSkills;
use Neo\SuperAgentBundle\Tests\Support\TestDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(TemplateSkills::class)]
#[CoversClass(TemplateSkillsMigration::class)]
final class TemplateSkillsTest extends TestCase
{
    private string $project = '';

    protected function setUp(): void
    {
        $this->project = sys_get_temp_dir().'/neo-template-skills-'.bin2hex(random_bytes(4));
        $dir = $this->project.'/config/superagent/mcp-templates/rooms';
        mkdir($dir.'/booking', 0777, true);
        mkdir($dir.'/stay', 0777, true);
        file_put_contents($dir.'/template.json', json_encode(['name' => 'Rooms', 'url' => 'https://mcp.example.com/mcp', 'skills' => ['booking/SKILL.md', 'stay/SKILL.md']]));
        $this->skill('booking', "---\nname: booking\ndescription: Offers and bookings.\ntools: [ListOffers]\n---\nAsk for the age of each child.");
        $this->skill('stay', "---\nname: stay\ndescription: Changes of a stay.\n---\nRules of a stay.");
    }

    protected function tearDown(): void
    {
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->project, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }

        rmdir($this->project);
    }

    public function testInstalledTemplateSkillsTakeOverTheNewTextOfTheirFile(): void
    {
        $db = TestDatabase::create(InstalledSkills::TABLE);
        $current = file_get_contents($this->project.'/config/superagent/mcp-templates/rooms/stay/SKILL.md');
        $db->insert(InstalledSkills::TABLE, ['tstamp' => 1, 'source' => 'template', 'url' => 'rooms/booking', 'name' => 'booking', 'description' => 'Offers.', 'body' => 'Old rules.', 'tools' => serialize([]), 'depends' => serialize([]), 'version_hash' => 'old', 'version_at' => 1]);
        $db->insert(InstalledSkills::TABLE, ['tstamp' => 1, 'source' => 'template', 'url' => 'rooms/stay', 'name' => 'stay', 'description' => 'Changes of a stay.', 'body' => 'Rules of a stay.', 'version_hash' => hash('sha256', (string) $current), 'version_at' => 1]);
        $db->insert(InstalledSkills::TABLE, ['tstamp' => 1, 'source' => 'github', 'url' => 'rooms/booking', 'name' => 'own', 'description' => 'Own.', 'body' => 'Own rules.', 'version_hash' => 'old', 'version_at' => 1]);

        $skills = new TemplateSkills($db, new TemplateCatalog($this->project, new NullLogger()));
        $migration = new TemplateSkillsMigration($skills);

        $this->assertTrue($migration->shouldRun());
        $this->assertSame(['booking'], $skills->refresh(), 'only the template skill whose file changed');

        $row = $db->fetchAssociative('SELECT * FROM '.InstalledSkills::TABLE." WHERE name = 'booking'");
        $this->assertSame('Ask for the age of each child.', $row['body']);
        $this->assertSame('Offers and bookings.', $row['description']);
        $this->assertSame(['ListOffers'], unserialize((string) $row['tools']));
        $this->assertSame('Own rules.', $db->fetchOne('SELECT body FROM '.InstalledSkills::TABLE." WHERE name = 'own'"), 'skills of other sources keep their text');
        $this->assertFalse($migration->shouldRun(), 'nothing left after the update');
    }

    public function testATextThatRenamesTheSkillOrFailsTheChecksStaysOut(): void
    {
        $db = TestDatabase::create(InstalledSkills::TABLE);
        $db->insert(InstalledSkills::TABLE, ['tstamp' => 1, 'source' => 'template', 'url' => 'rooms/booking', 'name' => 'booking', 'description' => 'Offers.', 'body' => 'Old rules.', 'version_hash' => 'old', 'version_at' => 1]);
        $db->insert(InstalledSkills::TABLE, ['tstamp' => 1, 'source' => 'template', 'url' => 'rooms/stay', 'name' => 'stay', 'description' => 'Changes.', 'body' => 'Old rules.', 'version_hash' => 'old', 'version_at' => 1]);
        $db->insert(InstalledSkills::TABLE, ['tstamp' => 1, 'source' => 'template', 'url' => 'gone/skill', 'name' => 'gone', 'description' => 'Gone.', 'body' => 'Old rules.', 'version_hash' => 'old', 'version_at' => 1]);
        $this->skill('booking', "---\nname: bookings\ndescription: Renamed.\n---\nNew rules.");
        $this->skill('stay', "---\nname: stay\ndescription: Too long.\n---\n".str_repeat('x', 3000));

        $skills = new TemplateSkills($db, new TemplateCatalog($this->project, new NullLogger()));

        $this->assertSame([], $skills->refresh());
        $this->assertSame(['Old rules.'], array_values(array_unique($db->fetchFirstColumn('SELECT body FROM '.InstalledSkills::TABLE))));
        $this->assertSame([], (new TemplateSkills(TestDatabase::create(), new TemplateCatalog($this->project, new NullLogger())))->outdated(), 'before the database update');
    }

    private function skill(string $folder, string $content): void
    {
        file_put_contents($this->project.'/config/superagent/mcp-templates/rooms/'.$folder.'/SKILL.md', $content);
    }
}

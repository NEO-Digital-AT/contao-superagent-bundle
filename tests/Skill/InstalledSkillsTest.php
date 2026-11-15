<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Skill;

use Neo\BasicBundle\Security\SecretCipher;
use Neo\SuperAgentBundle\Chat\Downloads;
use Neo\SuperAgentBundle\Chat\PersonalDataGuard;
use Neo\SuperAgentBundle\Mcp\Connections;
use Neo\SuperAgentBundle\Mcp\GuestCheck;
use Neo\SuperAgentBundle\Mcp\McpClient;
use Neo\SuperAgentBundle\Mcp\McpTemplate;
use Neo\SuperAgentBundle\Mcp\McpToolbox;
use Neo\SuperAgentBundle\Mcp\OAuthFlow;
use Neo\SuperAgentBundle\Mcp\TemplateCatalog;
use Neo\SuperAgentBundle\Mcp\ToolLog;
use Neo\SuperAgentBundle\Model\AgentModel;
use Neo\SuperAgentBundle\Skill\InstalledSkills;
use Neo\SuperAgentBundle\Tests\Support\Models;
use Neo\SuperAgentBundle\Tests\Support\TestDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\RequestStack;

#[CoversClass(InstalledSkills::class)]
#[CoversClass(TemplateCatalog::class)]
final class InstalledSkillsTest extends TestCase
{
    private string $project = '';

    protected function tearDown(): void
    {
        if ('' !== $this->project) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->project, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);

            foreach ($files as $file) {
                $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
            }

            rmdir($this->project);
        }
    }

    public function testOnlyConfirmedSkillsTickedAtTheAssistant(): void
    {
        $db = TestDatabase::create(InstalledSkills::TABLE, Connections::TABLE, ToolLog::TABLE);
        $db->insert(InstalledSkills::TABLE, ['tstamp' => 1, 'name' => 'shop', 'description' => 'Orders', 'body' => 'Rules', 'tools' => serialize(['get_order']), 'depends' => serialize(['contact'])]);
        $db->insert(InstalledSkills::TABLE, ['tstamp' => 1, 'name' => 'pending', 'description' => 'Not confirmed', 'body' => '']);
        $db->insert(InstalledSkills::TABLE, ['tstamp' => 1, 'name' => 'other', 'description' => 'Not ticked', 'body' => 'Rules']);
        $skills = new InstalledSkills($db);

        $this->assertSame(['other', 'shop'], array_keys($skills->all()));
        $this->assertSame(['shop' => ['description' => 'Orders', 'body' => 'Rules', 'tools' => [], 'dependsOn' => ['contact']]], $skills->forAgent(Models::make(AgentModel::class, ['skills' => serialize(['shop', 'pending'])]), $this->toolbox($db)), 'no tool of a connection this assistant may use');
        $this->assertSame([], $skills->forAgent(Models::make(AgentModel::class, ['skills' => null]), $this->toolbox($db)));
        $this->assertSame([], (new InstalledSkills(TestDatabase::create()))->all(), 'before the database update');
    }

    public function testTemplatesOfTheProjectAreReadAndInvalidOnesLeftOut(): void
    {
        $this->project = sys_get_temp_dir().'/neo-project-'.bin2hex(random_bytes(6));
        $dir = $this->project.'/config/superagent/mcp-templates';

        foreach (['booking' => ['name' => 'Booking', 'url' => 'https://mcp.example.com/mcp'], 'apaleo' => ['name' => 'Own apaleo', 'url' => 'https://apaleo.example.com/mcp'], 'shop' => ['name' => 'Anchor shop', 'url' => 'https://shop.example.com/mcp'], 'broken' => ['name' => 'Broken', 'url' => 'http://x.example.com'], 'Bad_Id' => ['name' => 'Bad', 'url' => 'https://bad.example.com']] as $id => $data) {
            mkdir($dir.'/'.$id, 0777, true);
            file_put_contents($dir.'/'.$id.'/template.json', json_encode($data));
        }

        mkdir($dir.'/json');
        file_put_contents($dir.'/json/template.json', '{not json');

        $catalog = new TemplateCatalog($this->project, new NullLogger());

        $this->assertSame(['shop', 'booking', 'apaleo'], array_keys($catalog->all()), 'by name, with the shipped templates');
        $this->assertSame('Own apaleo', $catalog->get('apaleo')?->name, 'a project template replaces the shipped one of the same id');
        $this->assertInstanceOf(McpTemplate::class, $catalog->get('booking'));
        $this->assertNull($catalog->get(''));
        $this->assertNull($catalog->get('broken'));
    }

    private function toolbox(\Doctrine\DBAL\Connection $db): McpToolbox
    {
        $cipher = new SecretCipher('test-secret');
        $client = new McpClient(new MockHttpClient());
        $templates = new TemplateCatalog(sys_get_temp_dir().'/neo-no-project', new NullLogger());

        return new McpToolbox(new Connections($db, $cipher, $client, new OAuthFlow(new MockHttpClient(), $client, $cipher, $db, new RequestStack(), $templates), $templates), $client, new ToolLog($db), new PersonalDataGuard(), new NullLogger(), new GuestCheck('test-secret', new ArrayAdapter(), new RequestStack()), new Downloads(new ArrayAdapter()));
    }
}

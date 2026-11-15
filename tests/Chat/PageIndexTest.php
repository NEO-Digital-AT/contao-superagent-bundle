<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Chat;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\Model\Registry;
use Contao\PageModel;
use Neo\SuperAgentBundle\Chat\GroundingProvider;
use Neo\SuperAgentBundle\Chat\PageIndex;
use Neo\SuperAgentBundle\Model\AgentModel;
use Neo\SuperAgentBundle\Tests\Support\Models;
use Neo\SuperAgentBundle\Tests\Support\TestDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/** Nothing outside the index can be opened; the knowledge comes only from the chosen source. */
#[CoversClass(PageIndex::class)]
#[CoversClass(GroundingProvider::class)]
final class PageIndexTest extends TestCase
{
    protected function tearDown(): void
    {
        Registry::getInstance()->reset();
    }

    public function testOnlyVisibleRegularPagesBelowTheStartPage(): void
    {
        $db = TestDatabase::create();
        $now = time();
        $pages = [
            ['id' => 2, 'pid' => 1, 'sorting' => 1, 'title' => 'Home', 'description' => '<b>Welcome</b>'],
            ['id' => 3, 'pid' => 1, 'sorting' => 2, 'title' => 'Hidden from the menu but listed', 'pageTitle' => 'Imprint'],
            ['id' => 4, 'pid' => 1, 'sorting' => 3, 'title' => 'Members', 'protected' => '1'],
            ['id' => 5, 'pid' => 1, 'sorting' => 4, 'title' => 'Draft', 'published' => ''],
            ['id' => 6, 'pid' => 1, 'sorting' => 5, 'title' => 'Not yet', 'start' => (string) ($now + 3600)],
            ['id' => 7, 'pid' => 1, 'sorting' => 6, 'title' => 'Over', 'stop' => (string) ($now - 3600)],
            ['id' => 8, 'pid' => 1, 'sorting' => 7, 'title' => 'No index', 'robots' => 'noindex,follow'],
            ['id' => 9, 'pid' => 1, 'sorting' => 8, 'title' => 'Folder', 'type' => 'folder'],
            ['id' => 10, 'pid' => 9, 'sorting' => 1, 'title' => 'In the folder'],
            ['id' => 11, 'pid' => 4, 'sorting' => 1, 'title' => 'Below a protected page'],
            ['id' => 12, 'pid' => 99, 'sorting' => 1, 'title' => 'Other website'],
        ];
        TestDatabase::addPages($db, $pages);

        foreach ($pages as $page) {
            Models::register(PageModel::class, ['id' => $page['id'], 'alias' => 'p'.$page['id']]);
        }

        $urls = $this->createMock(ContentUrlGenerator::class);
        $urls->method('generate')->willReturnCallback(static fn (PageModel $page): string => 'https://www.example.com/'.$page->alias);
        $cache = new ArrayAdapter();
        $index = new PageIndex($db, $this->createMock(ContaoFramework::class), $urls, $cache);

        $found = $index->forRoot(1);
        $this->assertSame([2, 3, 10], array_keys($found));
        $this->assertSame(['title' => 'Home', 'url' => 'https://www.example.com/p2', 'path' => '/p2', 'description' => 'Welcome'], $found[2]);
        $this->assertSame('Imprint', $found[3]['description']);

        $db->executeStatement('DELETE FROM tl_page');
        $this->assertSame($found, $index->forRoot(1), 'cached');
        $this->assertSame([], $index->forRoot(0));
    }

    public function testTheKnowledgeSource(): void
    {
        $project = sys_get_temp_dir().'/neo-grounding-'.bin2hex(random_bytes(6));
        mkdir($project.'/public', 0777, true);
        file_put_contents($project.'/public/llms.txt', "# Example\nWe rent bikes.\n");

        try {
            $grounding = new GroundingProvider($this->createMock(ContaoFramework::class), $project);
            $agent = static fn (array $row): AgentModel => Models::make(AgentModel::class, $row);

            $this->assertSame('Own text', $grounding->forAgent($agent(['grounding_source' => 'text', 'grounding_text' => ' Own text '])));
            $this->assertSame("# Example\nWe rent bikes.", $grounding->forAgent($agent(['grounding_source' => 'llms'])));
            $this->assertSame("# Example\nWe rent bikes.\n\nOwn text", $grounding->forAgent($agent(['grounding_source' => 'llms_text', 'grounding_text' => 'Own text'])));
            $this->assertSame('', $grounding->forAgent($agent(['grounding_source' => 'file', 'grounding_file' => ''])));
            $this->assertSame('', $grounding->forAgent($agent(['grounding_source' => 'unknown', 'grounding_text' => 'x'])));

            file_put_contents($project.'/public/llms-full.txt', 'Full version');
            $this->assertSame('Full version', $grounding->forAgent($agent(['grounding_source' => 'llms'])), 'llms-full.txt first');
        } finally {
            array_map('unlink', glob($project.'/public/*') ?: []);
            rmdir($project.'/public');
            rmdir($project);
        }
    }
}

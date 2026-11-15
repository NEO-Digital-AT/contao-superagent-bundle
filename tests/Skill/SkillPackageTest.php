<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Skill;

use Neo\SuperAgentBundle\Chat\ChatHandler;
use Neo\SuperAgentBundle\Chat\ChatTools;
use Neo\SuperAgentBundle\Skill\SkillPackage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SkillPackage::class)]
final class SkillPackageTest extends TestCase
{
    private string $dir = '';

    protected function tearDown(): void
    {
        if ('' !== $this->dir) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);

            foreach ($files as $file) {
                $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
            }

            rmdir($this->dir);
        }
    }

    public function testTheShippedPackageIsValid(): void
    {
        $package = SkillPackage::load(ChatHandler::skillDir(), ChatTools::NAMES);

        $this->assertNotSame('', $package->core());
        $this->assertLessThanOrEqual(SkillPackage::MAX_CORE, mb_strlen($package->core()));
        $this->assertSame(['navigation', 'contact', 'web-search', 'referral', 'medical', 'privacy'], array_keys($package->modules()));

        foreach ($package->modules() as $id => $module) {
            $this->assertNotSame('', $module['description'], $id);
            $this->assertLessThanOrEqual(SkillPackage::MAX_SKILL, mb_strlen($module['body']), $id);
        }

        $this->assertSame('navigation', $package->skillOfTool('open_page'), 'first in manifest order');
        $this->assertSame('contact', $package->skillOfTool('request_handoff'));
        $this->assertNull($package->skillOfTool('unknown'));
    }

    public function testDependenciesComeFirstAndOnce(): void
    {
        $package = $this->package([
            ['id' => 'a', 'dependsOn' => ['b', 'c']],
            ['id' => 'b', 'dependsOn' => ['c']],
            ['id' => 'c', 'dependsOn' => []],
        ]);

        $this->assertSame(['c', 'b', 'a'], $package->resolve(['a', 'b', 'unknown']));
    }

    /** @return iterable<string, array{list<array<string, mixed>>, string}> */
    public static function invalidPackages(): iterable
    {
        yield 'unknown tool' => [[['id' => 'a', 'tools' => ['shell']]], 'names the unknown tool "shell"'];
        yield 'unknown dependency' => [[['id' => 'a', 'dependsOn' => ['b']]], 'depends on the unknown skill "b"'];
        yield 'cycle' => [[['id' => 'a', 'dependsOn' => ['b']], ['id' => 'b', 'dependsOn' => ['a']]], 'dependency cycle: a → b → a'];
        yield 'duplicate id' => [[['id' => 'a'], ['id' => 'a']], 'invalid or duplicate skill id "a"'];
        yield 'invalid id' => [[['id' => '../a']], 'invalid or duplicate skill id "../a"'];
        yield 'too long' => [[['id' => 'a', 'body' => str_repeat('x', SkillPackage::MAX_SKILL + 1)]], 'at most 2800 are allowed'];
        yield 'wrong name' => [[['id' => 'a', 'name' => 'b']], 'name "b" does not match its directory'];
        yield 'extra field' => [[['id' => 'a', 'run' => 'x']], 'fields must be id, version'];
    }

    /** @param list<array<string, mixed>> $modules */
    #[DataProvider('invalidPackages')]
    public function testAnInvalidPackageIsRefusedAsAWhole(array $modules, string $reason): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($reason);

        $this->package($modules);
    }

    public function testASkillThatIsNotListedIsRefused(): void
    {
        $this->expectExceptionMessage('skills/extra is not listed in manifest.json');

        $this->package([['id' => 'a']], ['extra']);
    }

    public function testInstalledSkillsJoinOnlyWithAFreeNameAndKnownDependencies(): void
    {
        $package = $this->package([['id' => 'a']]);
        $skipped = [];
        $module = static fn (array $dependsOn = [], string $body = 'Rules'): array => ['description' => 'Installed', 'body' => $body, 'tools' => [], 'dependsOn' => $dependsOn];

        $with = $package->withInstalled([
            'a' => $module(),
            'core' => $module(),
            'shop' => $module(['a']),
            'orphan' => $module(['missing']),
            'needs-orphan' => $module(['orphan']),
            'long' => $module([], str_repeat('x', SkillPackage::MAX_SKILL + 1)),
            'x' => $module(['y']),
            'y' => $module(['x']),
        ], static function (string $id) use (&$skipped): void {
            $skipped[] = $id;
        });

        $this->assertSame(['a', 'shop'], array_keys($with->modules()));
        $this->assertSame('Shipped rules', $with->modules()['a']['body'], 'the shipped skill stays unchanged');
        $this->assertEqualsCanonicalizing(['a', 'core', 'long', 'orphan', 'needs-orphan', 'x', 'y'], $skipped);
        $this->assertSame(['a'], array_keys($package->modules()), 'the package itself is not changed');
    }

    /**
     * @param list<array<string, mixed>> $modules id, dependsOn, tools, body, name, further manifest fields
     * @param list<string>               $unlisted directories with a SKILL.md that the manifest does not list
     */
    private function package(array $modules, array $unlisted = []): SkillPackage
    {
        $this->dir = sys_get_temp_dir().'/neo-skills-'.bin2hex(random_bytes(6));
        mkdir($this->dir.'/core', 0777, true);
        file_put_contents($this->dir.'/core/SKILL.md', "---\nname: core\ndescription: \"Core\"\n---\nCore rules\n");
        $entries = [];

        foreach ($modules as $module) {
            $id = $module['id'];
            $entries[] = ['id' => $id, 'version' => 1, 'purpose' => 'test', 'dependsOn' => $module['dependsOn'] ?? [], 'tools' => $module['tools'] ?? []] + array_diff_key($module, array_flip(['id', 'dependsOn', 'tools', 'body', 'name']));

            if (!is_dir($this->dir.'/'.$id) && 1 === preg_match('/^[a-z]+$/', $id)) {
                mkdir($this->dir.'/'.$id);
                file_put_contents($this->dir.'/'.$id.'/SKILL.md', sprintf("---\nname: %s\ndescription: \"Skill %s\"\n---\n%s\n", $module['name'] ?? $id, $id, $module['body'] ?? 'Shipped rules'));
            }
        }

        foreach ($unlisted as $id) {
            mkdir($this->dir.'/'.$id);
            file_put_contents($this->dir.'/'.$id.'/SKILL.md', "---\nname: $id\ndescription: \"x\"\n---\nx\n");
        }

        file_put_contents($this->dir.'/manifest.json', json_encode(['schemaVersion' => 1, 'core' => ['id' => 'core', 'version' => 1], 'modules' => $entries]));

        return SkillPackage::load($this->dir, ChatTools::NAMES);
    }
}

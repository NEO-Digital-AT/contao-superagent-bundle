<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Skill;

use Neo\SuperAgentBundle\Skill\SkillFile;
use Neo\SuperAgentBundle\Skill\SkillPackage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SkillFile::class)]
final class SkillFileTest extends TestCase
{
    public function testFrontmatterAndText(): void
    {
        $file = SkillFile::parse("\u{FEFF}---\r\nname: opening-hours\r\ndescription: >\r\n  When the visitor asks\r\n  about opening hours\r\ntools: [get_hours, get_hours, '']\r\ndepends: navigation, contact\r\nlicense: MIT\r\n---\r\nAnswer with the hours of the shop.\r\n");

        $this->assertSame([
            'name' => 'opening-hours',
            'description' => 'When the visitor asks about opening hours',
            'body' => 'Answer with the hours of the shop.',
            'tools' => ['get_hours'],
            'depends' => ['navigation', 'contact'],
        ], $file->toArray());
        $this->assertSame(['errors' => [], 'warnings' => [['keys_ignored', 'license']]], $file->check(['core'], ['navigation', 'contact'], ['get_hours']));
    }

    public function testWithoutFrontmatterOrWithBrokenYamlNothingIsInstalled(): void
    {
        $this->assertSame(['errors' => [['frontmatter']], 'warnings' => []], SkillFile::parse('Just text')->check([], [], []));
        $this->assertSame(['errors' => [['frontmatter']], 'warnings' => []], SkillFile::parse("---\nname: [unclosed\n---\nText")->check([], [], []));
    }

    public function testWhatStopsTheInstallation(): void
    {
        $file = SkillFile::parse("---\nname: Navigation\ndescription: ''\ndepends: [missing, navigation]\n---\n");
        $this->assertSame([['name', 'Navigation'], ['description'], ['body'], ['depends', 'missing']], $file->check([], ['navigation'], [])['errors']);

        $taken = SkillFile::parse("---\nname: navigation\ndescription: Pages\n---\nText");
        $this->assertSame([['name_taken', 'navigation']], $taken->check(['navigation'], ['navigation'], [])['errors']);

        $self = SkillFile::parse("---\nname: loop\ndescription: Itself\ndepends: loop\n---\nText");
        $this->assertSame([['depends', 'loop']], $self->check([], ['loop'], [])['errors'], 'no dependency on itself');

        $long = SkillFile::parse("---\nname: long\ndescription: ".str_repeat('d', SkillFile::MAX_DESCRIPTION + 1)."\n---\n".str_repeat('x', SkillPackage::MAX_SKILL + 1));
        $this->assertSame([['description_long', SkillFile::MAX_DESCRIPTION + 1, SkillFile::MAX_DESCRIPTION], ['too_long', SkillPackage::MAX_SKILL + 1, SkillPackage::MAX_SKILL]], $long->check([], [], [])['errors']);
    }

    public function testAnUnknownToolIsAWarningOnly(): void
    {
        $file = SkillFile::parse("---\nname: shop\ndescription: Orders\ntools:\n  - list_orders\n---\nText");

        $this->assertSame(['errors' => [], 'warnings' => [['tool_unknown', 'list_orders']]], $file->check([], [], ['other_tool']));
    }
}

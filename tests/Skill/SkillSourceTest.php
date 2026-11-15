<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Skill;

use Neo\SuperAgentBundle\Skill\SkillSource;
use Neo\SuperAgentBundle\Skill\SkillSourceException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(SkillSource::class)]
#[CoversClass(SkillSourceException::class)]
final class SkillSourceTest extends TestCase
{
    /** @var list<string> */
    private array $files = [];

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            @unlink($file);
        }
    }

    /** @return iterable<string, array{string, string}> */
    public static function addresses(): iterable
    {
        yield 'repository' => ['https://github.com/example/skills', 'https://raw.githubusercontent.com/example/skills/HEAD/SKILL.md'];
        yield 'folder in a branch' => ['https://github.com/example/skills/tree/main/opening-hours', 'https://raw.githubusercontent.com/example/skills/main/opening-hours/SKILL.md'];
        yield 'file' => ['https://github.com/example/skills.git/blob/v1/hours/SKILL.md', 'https://raw.githubusercontent.com/example/skills/v1/hours/SKILL.md'];
        yield 'raw file' => ['https://raw.githubusercontent.com/example/skills/main/a%20b/README.md', 'https://raw.githubusercontent.com/example/skills/main/a%20b/README.md'];
        yield 'not GitHub' => ['https://example.com/example/skills', ''];
        yield 'not HTTPS' => ['http://github.com/example/skills', ''];
        yield 'path back' => ['https://github.com/example/skills/tree/main/../../other', ''];
        yield 'owner only' => ['https://github.com/example', ''];
        yield 'other page of the repository' => ['https://github.com/example/skills/issues', ''];
        yield 'invalid owner' => ['https://github.com/ex%20ample/skills', ''];
    }

    #[DataProvider('addresses')]
    public function testOnlyGithubAddressesOverHttps(string $url, string $raw): void
    {
        $this->assertSame($raw, SkillSource::rawUrl($url));
    }

    public function testASkillIsReadFromGithub(): void
    {
        $requested = '';
        $client = new MockHttpClient(static function (string $method, string $url) use (&$requested): MockResponse {
            $requested = $url;

            return new MockResponse("---\nname: hours\n---\nText");
        });

        $this->assertSame(['content' => "---\nname: hours\n---\nText", 'files' => []], (new SkillSource($client))->fromGithub('https://github.com/example/skills/tree/main/hours'));
        $this->assertSame('https://raw.githubusercontent.com/example/skills/main/hours/SKILL.md', $requested);
    }

    public function testErrorsOfTheDownloadHaveACode(): void
    {
        $this->assertCode('github_url', fn () => (new SkillSource(new MockHttpClient()))->fromGithub('https://example.com/a/b'));
        $this->assertCode('not_found', fn () => (new SkillSource(new MockHttpClient(new MockResponse('', ['http_code' => 404]))))->fromGithub('https://github.com/example/skills'));
        $this->assertCode('http', fn () => (new SkillSource(new MockHttpClient(new MockResponse('', ['http_code' => 500]))))->fromGithub('https://github.com/example/skills'));
        $this->assertCode('too_big', fn () => (new SkillSource(new MockHttpClient(new MockResponse(str_repeat('x', 200_001)))))->fromGithub('https://github.com/example/skills'));
    }

    public function testAMarkdownFileOrAZipWithOneSkill(): void
    {
        $source = new SkillSource(new MockHttpClient());
        $md = $this->file('md', "---\nname: a\n---\nText");

        $this->assertSame(['content' => "---\nname: a\n---\nText", 'files' => []], $source->fromFile($md));

        $zip = $this->zip(['hours/SKILL.md' => 'Skill', 'hours/reference.md' => 'More', '__MACOSX/hours/._SKILL.md' => 'x', 'hours/.hidden' => 'x']);
        $this->assertSame(['content' => 'Skill', 'files' => ['hours/reference.md']], $source->fromFile($zip));
    }

    public function testZipFilesThatAreRefused(): void
    {
        $source = new SkillSource(new MockHttpClient());

        $this->assertCode('zip_many', fn () => $source->fromFile($this->zip(['SKILL.md' => 'a', 'b/SKILL.md' => 'b'])));
        $this->assertCode('zip_none', fn () => $source->fromFile($this->zip(['a/b/SKILL.md' => 'too deep', 'README.md' => 'x'])));
        $this->assertCode('too_big', fn () => $source->fromFile($this->zip(['SKILL.md' => str_repeat('x', 200_001)])));
        $this->assertCode('zip_broken', fn () => $source->fromFile($this->file('zip', 'not a zip')));
        $this->assertCode('file_type', fn () => $source->fromFile($this->file('php', '<?php')));
        $this->assertCode('file_missing', fn () => $source->fromFile('/nonexistent/SKILL.md'));
    }

    private function assertCode(string $code, callable $call): void
    {
        try {
            $call();
        } catch (SkillSourceException $e) {
            $this->assertSame($code, $e->reason);

            return;
        }

        $this->fail('No error with the code '.$code);
    }

    private function file(string $extension, string $content): string
    {
        $path = sys_get_temp_dir().'/neo-skill-'.bin2hex(random_bytes(6)).'.'.$extension;
        file_put_contents($path, $content);
        $this->files[] = $path;

        return $path;
    }

    /** @param array<string, string> $entries */
    private function zip(array $entries): string
    {
        $path = $this->file('zip', '');
        unlink($path);
        $zip = new \ZipArchive();
        $zip->open($path, \ZipArchive::CREATE);

        foreach ($entries as $name => $content) {
            $zip->addFromString($name, $content);
        }

        $zip->close();

        return $path;
    }
}

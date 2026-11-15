<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Skill;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * A SKILL.md installed by an administrator (edition Plus): frontmatter with
 * name and description, optionally the tools of the connections it uses
 * (tools: names as the MCP server lists them) and the skills it builds on
 * (depends:), then the text the model gets.
 *
 * check() lists what stops the installation (errors) and what the
 * administrator should know (warnings), as codes with values for the back-end
 * texts (TL_LANG.tl_neo_agent_skill.check_<code>). Only the text is used;
 * nothing in it is run.
 */
final class SkillFile
{
    /** Longest description shown in the catalogue of the model. */
    public const MAX_DESCRIPTION = 300;

    /** Frontmatter keys this extension uses; others are ignored and named. */
    private const KEYS = ['name', 'description', 'tools', 'depends'];

    /**
     * @param list<string> $tools
     * @param list<string> $depends
     * @param list<string> $ignoredKeys
     */
    private function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly string $body,
        public readonly array $tools,
        public readonly array $depends,
        public readonly array $ignoredKeys,
        public readonly string $parseError,
    ) {
    }

    public static function parse(string $content): self
    {
        $content = str_replace("\r\n", "\n", ltrim($content, "\u{FEFF}"));

        if (1 !== preg_match('/\A---\n(.*?)\n---\n?(.*)\z/s', $content, $m)) {
            return new self('', '', trim($content), [], [], [], 'frontmatter');
        }

        try {
            $meta = Yaml::parse($m[1]);
        } catch (ParseException) {
            return new self('', '', trim($m[2]), [], [], [], 'yaml');
        }

        $meta = \is_array($meta) ? $meta : [];

        return new self(
            trim((string) (\is_scalar($meta['name'] ?? null) ? $meta['name'] : '')),
            trim(preg_replace('/\s+/', ' ', (string) (\is_scalar($meta['description'] ?? null) ? $meta['description'] : '')) ?? ''),
            trim($m[2]),
            self::names($meta['tools'] ?? []),
            self::names($meta['depends'] ?? []),
            array_values(array_diff(array_map('strval', array_keys($meta)), self::KEYS)),
            '',
        );
    }

    /**
     * Errors stop the installation, warnings are shown.
     *
     * @param list<string> $taken      names that are not free: the skills shipped with the bundle and the other installed skills
     * @param list<string> $installable names a dependency may point to: shipped and installed skills
     * @param list<string> $knownTools  tool names of the connections (as the servers list them)
     *
     * @return array{errors: list<array{0: string, 1?: int|string, 2?: int|string}>, warnings: list<array{0: string, 1?: int|string, 2?: int|string}>}
     */
    public function check(array $taken, array $installable, array $knownTools): array
    {
        $errors = [];
        $warnings = [];

        if ('' !== $this->parseError) {
            $errors[] = ['frontmatter'];

            return ['errors' => $errors, 'warnings' => $warnings];
        }

        if (1 !== preg_match('/^[a-z][a-z0-9-]{0,63}$/', $this->name)) {
            $errors[] = ['name', $this->name];
        } elseif (\in_array($this->name, $taken, true)) {
            $errors[] = ['name_taken', $this->name];
        }

        if ('' === $this->description) {
            $errors[] = ['description'];
        } elseif (mb_strlen($this->description) > self::MAX_DESCRIPTION) {
            $errors[] = ['description_long', mb_strlen($this->description), self::MAX_DESCRIPTION];
        }

        if ('' === $this->body) {
            $errors[] = ['body'];
        } elseif (mb_strlen($this->body) > SkillPackage::MAX_SKILL) {
            $errors[] = ['too_long', mb_strlen($this->body), SkillPackage::MAX_SKILL];
        }

        foreach ($this->depends as $dependency) {
            if ($dependency === $this->name || !\in_array($dependency, $installable, true)) {
                $errors[] = ['depends', $dependency];
            }
        }

        foreach ($this->tools as $tool) {
            if (!\in_array($tool, $knownTools, true)) {
                $warnings[] = ['tool_unknown', $tool];
            }
        }

        if ([] !== $this->ignoredKeys) {
            $warnings[] = ['keys_ignored', implode(', ', $this->ignoredKeys)];
        }

        return ['errors' => $errors, 'warnings' => $warnings];
    }

    /** @return array{name: string, description: string, body: string, tools: list<string>, depends: list<string>} */
    public function toArray(): array
    {
        return ['name' => $this->name, 'description' => $this->description, 'body' => $this->body, 'tools' => $this->tools, 'depends' => $this->depends];
    }

    /** @return list<string> a YAML list, or a comma-separated string */
    private static function names(mixed $value): array
    {
        if (\is_string($value)) {
            $value = explode(',', $value);
        }

        if (!\is_array($value)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(static fn ($v): string => \is_scalar($v) ? trim((string) $v) : '', $value))));
    }
}

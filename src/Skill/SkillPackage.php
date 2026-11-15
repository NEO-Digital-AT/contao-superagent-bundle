<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Skill;

/**
 * The assistant's rules as files: skills/manifest.json, skills/core/SKILL.md and
 * one SKILL.md per area. Read and checked once per process; an invalid package
 * throws, so the chat never runs with half its rules.
 *
 * Checked: manifest schema, a SKILL.md for every listed id and no unlisted one,
 * frontmatter name = directory and a description, size limits, known tool names,
 * known dependencies, no dependency cycles.
 */
final class SkillPackage
{
    /** A task skill longer than this is no longer followed reliably (operator rule). */
    public const MAX_SKILL = 2800;

    public const MAX_CORE = 4000;

    /** @var array<string, self> */
    private static array $loaded = [];

    /**
     * @param array<string, array{description: string, body: string, tools: list<string>, dependsOn: list<string>, version: int}> $modules
     */
    private function __construct(
        private readonly string $core,
        private readonly array $modules,
    ) {
    }

    /**
     * @param list<string> $knownTools tool names the application can run
     */
    public static function load(string $dir, array $knownTools): self
    {
        $key = realpath($dir) ?: $dir;

        return self::$loaded[$key] ??= self::read($dir, $knownTools);
    }

    public function core(): string
    {
        return $this->core;
    }

    /** @return array<string, array{description: string, body: string, tools: list<string>, dependsOn: list<string>, version: int}> */
    public function modules(): array
    {
        return $this->modules;
    }

    /**
     * The given skills with their dependencies, dependencies first, each once.
     *
     * @param list<string> $ids
     *
     * @return list<string>
     */
    public function resolve(array $ids): array
    {
        $out = [];

        foreach ($ids as $id) {
            $this->collect($id, $out);
        }

        return $out;
    }

    /**
     * This package plus skills installed by an administrator (edition Plus).
     * An installed skill joins only when its name is free and its dependencies
     * are known; its text was checked when it was installed. The shipped skills
     * stay first and unchanged.
     *
     * @param array<string, array{description: string, body: string, tools: list<string>, dependsOn: list<string>}> $installed
     * @param callable(string): void|null                                                                        $skipped called with the name of a skill left out
     */
    public function withInstalled(array $installed, ?callable $skipped = null): self
    {
        $modules = $this->modules;

        foreach ($installed as $id => $module) {
            if (isset($modules[$id]) || 'core' === $id || mb_strlen($module['body']) > self::MAX_SKILL) {
                null !== $skipped && $skipped((string) $id);

                continue;
            }

            $modules[$id] = $module + ['version' => 1];
        }

        // A dependency on a skill that is not there, or a cycle: leave the installed
        // skill out; repeat, since that may leave another one without its dependency.
        do {
            $removed = false;

            foreach (array_keys($installed) as $id) {
                if (!isset($modules[$id]) || isset($this->modules[$id])) {
                    continue;
                }

                try {
                    foreach ($modules[$id]['dependsOn'] as $dependency) {
                        if (!isset($modules[$dependency])) {
                            throw self::invalid('unknown dependency');
                        }
                    }

                    self::assertNoCycle($modules, (string) $id, []);
                } catch (\RuntimeException) {
                    unset($modules[$id]);
                    null !== $skipped && $skipped((string) $id);
                    $removed = true;
                }
            }
        } while ($removed);

        return new self($this->core, $modules);
    }

    /** The skill whose tools include this tool (first in manifest order), or null. */
    public function skillOfTool(string $tool): ?string
    {
        foreach ($this->modules as $id => $module) {
            if (\in_array($tool, $module['tools'], true)) {
                return $id;
            }
        }

        return null;
    }

    /** @param list<string> $out */
    private function collect(string $id, array &$out): void
    {
        if (!isset($this->modules[$id]) || \in_array($id, $out, true)) {
            return;
        }

        foreach ($this->modules[$id]['dependsOn'] as $dependency) {
            $this->collect($dependency, $out);
        }

        $out[] = $id;
    }

    /** @param list<string> $knownTools */
    private static function read(string $dir, array $knownTools): self
    {
        $manifest = json_decode((string) @file_get_contents($dir.'/manifest.json'), true);

        if (!\is_array($manifest) || 1 !== ($manifest['schemaVersion'] ?? null) || !\is_array($manifest['core'] ?? null) || !\is_array($manifest['modules'] ?? null)) {
            throw self::invalid('manifest.json is missing or not schema version 1');
        }

        $unknownKeys = array_diff(array_keys($manifest), ['schemaVersion', 'core', 'modules']);

        if ([] !== $unknownKeys) {
            throw self::invalid('unknown manifest keys: '.implode(', ', $unknownKeys));
        }

        if ('core' !== ($manifest['core']['id'] ?? null)) {
            throw self::invalid('the core id must be "core"');
        }

        $core = self::readSkill($dir, 'core', self::MAX_CORE)['body'];
        $modules = [];

        foreach ($manifest['modules'] as $entry) {
            $id = (string) ($entry['id'] ?? '');

            if (1 !== preg_match('/^[a-z][a-z0-9-]*$/', $id) || 'core' === $id || isset($modules[$id])) {
                throw self::invalid('invalid or duplicate skill id "'.$id.'"');
            }

            $extra = array_diff(array_keys($entry), ['id', 'version', 'purpose', 'dependsOn', 'tools']);

            if ([] !== $extra || !\is_int($entry['version'] ?? null) || !\is_array($entry['dependsOn'] ?? null) || !\is_array($entry['tools'] ?? null)) {
                throw self::invalid('skill "'.$id.'": fields must be id, version (integer), purpose, dependsOn, tools');
            }

            foreach ($entry['tools'] as $tool) {
                if (!\in_array($tool, $knownTools, true)) {
                    throw self::invalid('skill "'.$id.'" names the unknown tool "'.$tool.'"');
                }
            }

            $skill = self::readSkill($dir, $id, self::MAX_SKILL);
            $modules[$id] = [
                'description' => $skill['description'],
                'body' => $skill['body'],
                'tools' => array_values(array_map('strval', $entry['tools'])),
                'dependsOn' => array_values(array_map('strval', $entry['dependsOn'])),
                'version' => $entry['version'],
            ];
        }

        foreach ($modules as $id => $module) {
            foreach ($module['dependsOn'] as $dependency) {
                if (!isset($modules[$dependency])) {
                    throw self::invalid('skill "'.$id.'" depends on the unknown skill "'.$dependency.'"');
                }
            }

            self::assertNoCycle($modules, $id, []);
        }

        foreach (glob($dir.'/*/SKILL.md') ?: [] as $file) {
            $id = basename(\dirname($file));

            if ('core' !== $id && !isset($modules[$id])) {
                throw self::invalid('skills/'.$id.' is not listed in manifest.json');
            }
        }

        return new self($core, $modules);
    }

    /** @return array{description: string, body: string} */
    private static function readSkill(string $dir, string $id, int $limit): array
    {
        $text = @file_get_contents($dir.'/'.$id.'/SKILL.md');

        if (false === $text) {
            throw self::invalid('skills/'.$id.'/SKILL.md is missing');
        }

        if (1 !== preg_match('/\A---\nname: ([a-z][a-z0-9-]*)\ndescription: ("(?:[^"\\\\]|\\\\.)*")\n---\n(.*)\z/s', str_replace("\r\n", "\n", $text), $m)) {
            throw self::invalid('skills/'.$id.'/SKILL.md: frontmatter must be name and a quoted description');
        }

        if ($m[1] !== $id) {
            throw self::invalid('skills/'.$id.'/SKILL.md: name "'.$m[1].'" does not match its directory');
        }

        $description = json_decode($m[2]);
        $body = trim($m[3]);

        if (!\is_string($description) || '' === trim($description) || '' === $body) {
            throw self::invalid('skills/'.$id.'/SKILL.md: description or body is empty');
        }

        if (mb_strlen($body) > $limit) {
            throw self::invalid('skills/'.$id.'/SKILL.md has '.mb_strlen($body).' characters, at most '.$limit.' are allowed');
        }

        return ['description' => $description, 'body' => $body];
    }

    /**
     * @param array<string, array{dependsOn: list<string>}> $modules
     * @param list<string>                                  $path
     */
    private static function assertNoCycle(array $modules, string $id, array $path): void
    {
        if (\in_array($id, $path, true)) {
            throw self::invalid('dependency cycle: '.implode(' → ', [...$path, $id]));
        }

        foreach ($modules[$id]['dependsOn'] as $dependency) {
            self::assertNoCycle($modules, $dependency, [...$path, $id]);
        }
    }

    private static function invalid(string $reason): \RuntimeException
    {
        return new \RuntimeException('SuperAgent skill package invalid: '.$reason);
    }
}

<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Mcp;

use Psr\Log\LoggerInterface;

/**
 * The templates for connections: those shipped with the bundle (mcp-templates/)
 * and those of the project (config/superagent/mcp-templates/, e.g. an agency's
 * own). One folder per template with a template.json and, optionally, its
 * SKILL.md. A project template with the id of a shipped one replaces it. An
 * invalid template is left out and logged.
 */
final class TemplateCatalog
{
    /** @var array<string, McpTemplate>|null */
    private array|null $templates = null;

    public function __construct(
        private readonly string $projectDir,
        private readonly LoggerInterface $logger,
    ) {
    }

    /** @return array<string, McpTemplate> id => template, by name */
    public function all(): array
    {
        if (null === $this->templates) {
            $this->templates = [];

            foreach ([\dirname(__DIR__, 2).'/mcp-templates', $this->projectDir.'/config/superagent/mcp-templates'] as $dir) {
                foreach (glob($dir.'/*/template.json') ?: [] as $file) {
                    $id = basename(\dirname($file));

                    if (1 !== preg_match('/^[a-z][a-z0-9-]{0,31}$/', $id)) {
                        $this->logger->warning(sprintf('SuperAgent: MCP template folder "%s" ignored (id: lower-case letters, digits, hyphens).', $id));

                        continue;
                    }

                    try {
                        $data = json_decode((string) file_get_contents($file), true, 16, JSON_THROW_ON_ERROR);
                        $this->templates[$id] = McpTemplate::fromArray($id, \is_array($data) ? $data : [], \dirname($file));
                    } catch (\JsonException|\InvalidArgumentException $e) {
                        $this->logger->warning(sprintf('SuperAgent: MCP template "%s" ignored: %s', $id, $e->getMessage()));
                    }
                }
            }

            uasort($this->templates, static fn (McpTemplate $a, McpTemplate $b): int => strcasecmp($a->name, $b->name));
        }

        return $this->templates;
    }

    public function get(string $id): McpTemplate|null
    {
        return '' === $id ? null : ($this->all()[$id] ?? null);
    }
}

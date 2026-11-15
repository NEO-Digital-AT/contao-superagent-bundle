<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Chat;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FilesModel;
use Neo\SuperAgentBundle\Model\AgentModel;

/**
 * Resolves an agent's grounding (the verified facts the model must stay within):
 * either entered text or a file from the Contao file manager.
 */
final class GroundingProvider
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly string $projectDir,
    ) {
    }

    public function forAgent(AgentModel $agent): string
    {
        return match ($agent->grounding_source) {
            'text' => trim((string) $agent->grounding_text),
            'file' => $this->readFile((string) $agent->grounding_file),
            'llms' => $this->readLlmsTxt(),
            'llms_text' => trim(implode("\n\n", array_filter([
                $this->readLlmsTxt(),
                trim((string) $agent->grounding_text),
            ]))),
            default => '',
        };
    }

    /**
     * Reads llms.txt / llms-full.txt from the web root (public/). That is what the
     * file is for: a machine-readable summary of the website for AI systems.
     * llms-full.txt takes precedence (more detailed); otherwise llms.txt.
     */
    private function readLlmsTxt(): string
    {
        foreach (['llms-full.txt', 'llms.txt'] as $name) {
            $path = $this->projectDir.'/public/'.$name;

            if (is_file($path)) {
                $content = trim((string) file_get_contents($path));

                if ('' !== $content) {
                    return $content;
                }
            }
        }

        return '';
    }

    private function readFile(string $uuid): string
    {
        if ('' === $uuid) {
            return '';
        }

        $this->framework->initialize();

        $filesAdapter = $this->framework->getAdapter(FilesModel::class);
        $file = $filesAdapter->findByUuid($uuid);

        if (null === $file) {
            return '';
        }

        $path = $this->projectDir.'/'.$file->path;

        return is_file($path) ? trim((string) file_get_contents($path)) : '';
    }
}

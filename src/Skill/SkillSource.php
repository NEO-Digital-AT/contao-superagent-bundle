<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Skill;

use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Fetches the SKILL.md of a skill to install: from a GitHub repository (the
 * file itself from raw.githubusercontent.com, no GitHub API) or from a file of
 * the file manager (SKILL.md, or a ZIP with one SKILL.md). Called by an
 * administrator in the back end only; the chat never fetches anything.
 *
 * Accepted GitHub addresses: https://github.com/<owner>/<repo>[/tree|blob/<ref>/<path>]
 * and https://raw.githubusercontent.com/<owner>/<repo>/<ref>/<path>. Without a
 * ref the default branch is used (HEAD); a commit hash pins a version.
 */
final class SkillSource
{
    private const MAX_BYTES = 200_000;

    private const MAX_ZIP = 5_000_000;

    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    /**
     * The address of the SKILL.md for a GitHub address; '' when it is none.
     */
    public static function rawUrl(string $url): string
    {
        $url = trim($url);
        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));
        $segments = array_values(array_filter(explode('/', (string) ($parts['path'] ?? '')), static fn (string $s): bool => '' !== $s));

        if ('https' !== strtolower((string) ($parts['scheme'] ?? '')) || \count($segments) < 2) {
            return '';
        }

        foreach ($segments as $segment) {
            if ('..' === $segment || '.' === $segment) {
                return '';
            }
        }

        [$owner, $repo] = $segments;
        $repo = preg_replace('/\.git$/', '', $repo) ?? $repo;

        if (1 !== preg_match('/^[A-Za-z0-9_.-]+$/', $owner.$repo)) {
            return '';
        }

        if ('raw.githubusercontent.com' === $host) {
            $rest = \array_slice($segments, 2);
        } elseif ('github.com' === $host || 'www.github.com' === $host) {
            $kind = $segments[2] ?? '';
            $rest = \in_array($kind, ['tree', 'blob'], true) ? \array_slice($segments, 3) : ('' === $kind ? ['HEAD'] : []);

            if ([] === $rest) {
                return '';
            }
        } else {
            return '';
        }

        $path = implode('/', array_map('rawurlencode', array_map('rawurldecode', $rest)));

        if (!str_ends_with(strtolower($path), '.md')) {
            $path .= '/SKILL.md';
        }

        return sprintf('https://raw.githubusercontent.com/%s/%s/%s', rawurlencode($owner), rawurlencode($repo), $path);
    }

    /** @return array{content: string, files: list<string>} */
    public function fromGithub(string $url): array
    {
        $raw = self::rawUrl($url);

        if ('' === $raw) {
            throw new SkillSourceException('github_url');
        }

        try {
            $response = $this->httpClient->request('GET', $raw, ['headers' => ['Accept' => 'text/plain']]);
            $status = $response->getStatusCode();
            $content = 200 === $status ? $response->getContent() : '';
        } catch (ExceptionInterface $e) {
            throw new SkillSourceException('unreachable', $e->getMessage());
        }

        if (404 === $status) {
            throw new SkillSourceException('not_found', $raw);
        }

        if (200 !== $status) {
            throw new SkillSourceException('http', (string) $status);
        }

        if (\strlen($content) > self::MAX_BYTES) {
            throw new SkillSourceException('too_big');
        }

        return ['content' => $content, 'files' => []];
    }

    /** @return array{content: string, files: list<string>} */
    public function fromFile(string $path): array
    {
        if (!is_file($path)) {
            throw new SkillSourceException('file_missing');
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ('md' === $extension) {
            if (filesize($path) > self::MAX_BYTES) {
                throw new SkillSourceException('too_big');
            }

            return ['content' => (string) file_get_contents($path), 'files' => []];
        }

        if ('zip' !== $extension) {
            throw new SkillSourceException('file_type');
        }

        if (!class_exists(\ZipArchive::class)) {
            throw new SkillSourceException('no_zip');
        }

        if (filesize($path) > self::MAX_ZIP) {
            throw new SkillSourceException('too_big');
        }

        $zip = new \ZipArchive();

        if (true !== $zip->open($path, \ZipArchive::RDONLY)) {
            throw new SkillSourceException('zip_broken');
        }

        $skill = null;
        $files = [];

        for ($i = 0; $i < $zip->numFiles; ++$i) {
            $name = (string) $zip->getNameIndex($i);

            if (str_ends_with($name, '/') || str_starts_with(basename($name), '.') || str_starts_with($name, '__MACOSX/')) {
                continue;
            }

            // SKILL.md at the top or in the one folder of the archive.
            if ('SKILL.md' === basename($name) && substr_count(trim($name, '/'), '/') <= 1) {
                if (null !== $skill) {
                    $zip->close();

                    throw new SkillSourceException('zip_many');
                }

                $skill = $i;
            } else {
                $files[] = $name;
            }
        }

        if (null === $skill) {
            $zip->close();

            throw new SkillSourceException('zip_none');
        }

        $stat = $zip->statIndex($skill);

        if (false === $stat || $stat['size'] > self::MAX_BYTES) {
            $zip->close();

            throw new SkillSourceException('too_big');
        }

        $content = (string) $zip->getFromIndex($skill);
        $zip->close();

        return ['content' => $content, 'files' => \array_slice($files, 0, 50)];
    }
}

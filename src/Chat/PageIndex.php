<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Chat;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ArrayParameterType;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * The pages the assistant may open (navigation, edition "basic" on): regular
 * pages below one start page that are published, not protected and not
 * excluded from search engines. Pages hidden from the menu are included
 * (legal notice, privacy policy). Nothing outside this list can be opened.
 * Cached for ten minutes per start page.
 */
final class PageIndex
{
    private const MAX = 300;
    private const TTL = 600;

    public function __construct(
        private readonly Connection $db,
        private readonly ContaoFramework $framework,
        private readonly ContentUrlGenerator $urls,
        private readonly CacheItemPoolInterface $cache,
    ) {
    }

    /** @return array<int, array{title: string, url: string, path: string, description: string}> page id => entry */
    public function forRoot(int $rootId): array
    {
        if ($rootId < 1) {
            return [];
        }

        $item = $this->cache->getItem('neo_agent_pages_'.$rootId);

        if ($item->isHit()) {
            return (array) $item->get();
        }

        $this->framework->initialize();
        $now = time();
        $pages = [];
        $parents = [$rootId];

        while ([] !== $parents && \count($pages) < self::MAX) {
            $rows = $this->db->fetchAllAssociative(
                "SELECT id, type, title, pageTitle, description, robots, protected, published, start, stop FROM tl_page WHERE pid IN (?) ORDER BY sorting",
                [$parents],
                [ArrayParameterType::INTEGER],
            );
            $parents = [];

            foreach ($rows as $row) {
                // Flags are 0/1 or ''/'1' depending on the Contao version; times are 0 or ''.
                $visible = (bool) $row['published'] && !$row['protected'] && ((int) $row['start'] <= $now) && (0 === (int) $row['stop'] || (int) $row['stop'] > $now);

                if (!$visible) {
                    continue;
                }

                $parents[] = (int) $row['id'];

                if ('regular' !== $row['type'] || str_contains((string) $row['robots'], 'noindex')) {
                    continue;
                }

                $page = PageModel::findById((int) $row['id']);

                if (null === $page) {
                    continue;
                }

                try {
                    $url = $this->urls->generate($page, [], UrlGeneratorInterface::ABSOLUTE_URL);
                } catch (\Throwable) {
                    continue;
                }

                $pages[(int) $row['id']] = [
                    'title' => trim((string) $row['title']),
                    'url' => $url,
                    'path' => (string) (parse_url($url, PHP_URL_PATH) ?: '/'),
                    'description' => mb_substr(trim(strip_tags((string) ($row['description'] ?: $row['pageTitle']))), 0, 160),
                ];

                if (\count($pages) >= self::MAX) {
                    break;
                }
            }
        }

        $item->set($pages)->expiresAfter(self::TTL);
        $this->cache->save($item);

        return $pages;
    }
}

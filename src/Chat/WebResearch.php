<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Chat;

use Contao\StringUtil;
use Neo\BasicBundle\Access\AccessSettings;
use Neo\SuperAgentBundle\Model\AgentModel;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Web research for the assistant, strictly limited to the sources the operator
 * approved.
 *
 * A source can be:
 *  - a website (e.g. example.com): the page is read, and when the answer is not
 *    there, matching subpages are followed;
 *  - an llms.txt / llms-full.txt: the machine-readable summary of a site;
 *  - a JSON endpoint (e.g. the catalogue of a marketplace): its entries are
 *    searched one by one instead of reading the page as running text.
 *
 * Why: many sites load their content in the browser. A catalogue page may ship
 * only an empty frame in its HTML and fetch the entries later; reading only the
 * HTML finds nothing although the entry exists.
 *
 * The approved list is a HARD limit: even when a visitor tries to talk the
 * assistant into reading another address, it is dropped. Private and local
 * addresses are blocked as well (SSRF protection).
 */
final class WebResearch
{
    private const TTL = 3600;
    /** Total character budget sent to the model. */
    private const MAX_CHARS = 14000;
    /** Maximum number of subpages followed per source. */
    private const MAX_SUBPAGES = 2;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CacheItemPoolInterface $cache,
        private readonly AccessSettings $access,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function isEnabled(AgentModel $agent): bool
    {
        return (bool) $agent->web_search && [] !== $this->sources($agent);
    }

    /**
     * Answers a search query from the approved sources.
     *
     * @return array{text: string, sources: list<string>}
     */
    public function search(AgentModel $agent, string $query): array
    {
        $query = trim($query);

        if ('' === $query || !$this->isEnabled($agent)) {
            return ['text' => '', 'sources' => []];
        }

        $limit = max(1, min(5, (int) ($agent->web_search_max ?: 3)));
        $words = $this->words($query);

        // Without a SerpAPI key the approved sources are read directly, as in mode "sources".
        $urls = 'serpapi' === $agent->web_search_mode && '' !== ($key = $this->access->secret('superagent_serpapi_key'))
            ? $this->serpApiUrls($agent, $query, $limit, $key)
            : $this->rankSources($agent, $words);

        $chunks = [];
        $used = [];
        $budget = self::MAX_CHARS;

        foreach ($urls as $url) {
            if ($budget <= 500 || \count($used) >= $limit) {
                break;
            }

            if (!$this->isAllowed($agent, $url)) {
                continue;
            }

            $target = $this->preferLlmsTxt($agent, $url);
            $doc = $this->document($target);
            $text = $this->relevantPart($doc, $words, (int) ($budget / max(1, $limit - \count($used))));

            // Nothing matching on the page? Then follow the subpages that match the
            // question, as a person would, instead of giving up.
            if ('' === $text && 'html' === $doc['type']) {
                foreach ($this->subpages($agent, $target, $doc, $words) as $sub) {
                    $subDoc = $this->document($sub);
                    $subText = $this->relevantPart($subDoc, $words, 4000);

                    if ('' !== $subText) {
                        $chunks[] = '### Source: '.$sub."\n".$subText;
                        $used[] = $sub;
                        $budget -= mb_strlen($subText);
                        break;
                    }
                }

                continue;
            }

            if ('' === $text) {
                continue;
            }

            $chunks[] = '### Source: '.$target."\n".$text;
            $used[] = $target;
            $budget -= mb_strlen($text);
        }

        return [
            'text' => implode("\n\n", $chunks),
            'sources' => $used,
        ];
    }

    /**
     * The approved addresses. A line may read:
     *   https://example.com
     *   https://example.com/api/... | keyword1 keyword2
     * The part after "|" holds keywords that tell what the source covers; needed
     * for addresses that do not show it (API endpoints).
     *
     * @return list<string>
     */
    public function sources(AgentModel $agent): array
    {
        return array_map(static fn (array $s): string => $s['url'], $this->parsedSources($agent));
    }

    /**
     * @return list<array{url: string, keywords: string}>
     */
    private function parsedSources(AgentModel $agent): array
    {
        $rows = StringUtil::deserialize($agent->web_search_sources, true);
        $out = [];
        $seen = [];

        foreach ($rows as $row) {
            $line = trim(\is_array($row) ? (string) reset($row) : (string) $row);

            if ('' === $line) {
                continue;
            }

            [$url, $keywords] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');

            if ('' === $url) {
                continue;
            }

            if (!preg_match('#^https?://#i', $url)) {
                $url = 'https://'.ltrim($url, '/');
            }

            $host = parse_url($url, PHP_URL_HOST);

            if (!\is_string($host) || '' === $host || $this->isPrivateHost($host) || isset($seen[$url])) {
                continue;
            }

            $seen[$url] = true;
            $out[] = ['url' => $url, 'keywords' => mb_strtolower($keywords)];
        }

        return $out;
    }

    /**
     * Orders the sources by how well they match the question. Hits in the address
     * AND in the keywords of the source count.
     *
     * @param list<string> $words
     *
     * @return list<string>
     */
    private function rankSources(AgentModel $agent, array $words): array
    {
        $scored = [];

        foreach ($this->parsedSources($agent) as $i => $src) {
            $haystack = mb_strtolower($src['url']).' '.$src['keywords'];
            $score = 0;

            foreach ($words as $w) {
                if (str_contains($haystack, $w)) {
                    ++$score;
                }
            }

            $scored[] = ['url' => $src['url'], 'score' => $score, 'pos' => $i];
        }

        usort($scored, static fn (array $a, array $b): int => [$b['score'], $a['pos']] <=> [$a['score'], $b['pos']]);

        return array_map(static fn (array $r): string => $r['url'], $scored);
    }

    /**
     * A fetched document: either running text (HTML) or a list of entries
     * (JSON catalogue). Cached.
     *
     * @return array{type: string, text: string, entries: list<string>, links: array<string, string>}
     */
    private function document(string $url): array
    {
        $item = $this->cache->getItem('neo_agent_doc_'.hash('sha256', $url));

        if ($item->isHit()) {
            return (array) $item->get();
        }

        $empty = ['type' => 'none', 'text' => '', 'entries' => [], 'links' => []];

        try {
            $response = $this->httpClient->request('GET', $url, [
                'headers' => ['Accept' => 'application/json,text/html,text/plain,*/*'],
                'max_redirects' => 3,
            ]);

            if ($response->getStatusCode() >= 400) {
                return $empty;
            }

            $type = strtolower((string) ($response->getHeaders(false)['content-type'][0] ?? ''));
            $body = $response->getContent(false);
        } catch (\Throwable $e) {
            $this->logger->info('SuperAgent: source not reachable ('.$url.'): '.$e->getMessage());

            return $empty;
        }

        $doc = str_contains($type, 'json')
            ? ['type' => 'json', 'text' => '', 'entries' => $this->jsonEntries($body), 'links' => []]
            : ['type' => str_contains($type, 'html') ? 'html' : 'text', 'text' => $this->toText($body), 'entries' => [], 'links' => $this->links($body, $url)];

        $item->set($doc)->expiresAfter(self::TTL);
        $this->cache->save($item);

        return $doc;
    }

    /**
     * The part of a document that matches the question.
     *  - JSON catalogue: the entries that contain the keywords.
     *  - Text/HTML: the passages around the hits (instead of the first
     *    12,000 characters, which are mostly navigation and preamble).
     *
     * @param array{type: string, text: string, entries: list<string>, links: array<string, string>} $doc
     * @param list<string>                                                                           $words
     */
    private function relevantPart(array $doc, array $words, int $budget): string
    {
        $budget = max(800, $budget);

        if ('json' === $doc['type']) {
            $hits = [];

            foreach ($doc['entries'] as $i => $entry) {
                $low = mb_strtolower($entry);
                $score = 0;

                foreach ($words as $w) {
                    if (str_contains($low, $w)) {
                        ++$score;
                    }
                }

                if ($score > 0) {
                    $hits[] = ['text' => $entry, 'score' => $score, 'pos' => $i];
                }
            }

            // Sort by the number of matching keywords. Otherwise the real hits drown:
            // when almost every entry of a catalogue contains the vendor's name, only
            // a few also contain the second, decisive word.
            usort($hits, static fn (array $a, array $b): int => [$b['score'], $a['pos']] <=> [$a['score'], $b['pos']]);

            // No hit: return an overview so the model at least knows what exists,
            // instead of claiming "nothing found".
            $out = [] !== $hits
                ? array_map(static fn (array $h): string => $h['text'], $hits)
                : \array_slice($doc['entries'], 0, 60);

            return mb_substr(implode("\n", $out), 0, $budget);
        }

        $text = $doc['text'];

        if ('' === $text) {
            return '';
        }

        $low = mb_strtolower($text);
        $windows = [];

        foreach ($words as $w) {
            $pos = 0;

            while (false !== ($pos = mb_stripos($low, $w, $pos))) {
                $windows[] = [max(0, $pos - 400), 900];
                $pos += mb_strlen($w);

                if (\count($windows) >= 8) {
                    break 2;
                }
            }
        }

        if ([] === $windows) {
            return '';
        }

        $parts = [];

        foreach ($windows as [$start, $len]) {
            $parts[] = trim(mb_substr($text, $start, $len));
        }

        return mb_substr(implode("\n…\n", array_unique($parts)), 0, $budget);
    }

    /**
     * Subpages of the same domain whose address or link text matches the question.
     *
     * @param array{links: array<string, string>} $doc
     * @param list<string>                        $words
     *
     * @return list<string>
     */
    private function subpages(AgentModel $agent, string $url, array $doc, array $words): array
    {
        $scored = [];

        foreach ($doc['links'] as $href => $label) {
            if ($href === $url || !$this->isAllowed($agent, $href)) {
                continue;
            }

            $haystack = mb_strtolower($href.' '.$label);
            $score = 0;

            foreach ($words as $w) {
                if (str_contains($haystack, $w)) {
                    ++$score;
                }
            }

            if ($score > 0) {
                $scored[$href] = $score;
            }
        }

        arsort($scored);

        return \array_slice(array_keys($scored), 0, self::MAX_SUBPAGES);
    }

    /**
     * Turns a JSON catalogue into short, searchable lines ("Name — description").
     * An entry is recognised by having a name or title.
     *
     * @return list<string>
     */
    private function jsonEntries(string $body): array
    {
        $data = json_decode($body, true);

        if (!\is_array($data)) {
            return [];
        }

        $lines = [];
        $walk = static function ($node) use (&$walk, &$lines): void {
            if (!\is_array($node)) {
                return;
            }

            $name = null;

            foreach (['name', 'title', 'headline', 'label'] as $key) {
                if (isset($node[$key]) && \is_string($node[$key]) && '' !== trim($node[$key])) {
                    $name = trim($node[$key]);
                    break;
                }
            }

            if (null !== $name) {
                $desc = '';

                foreach (['shortDescription', 'description', 'summary', 'subtitle', 'tagline'] as $key) {
                    if (isset($node[$key]) && \is_string($node[$key]) && '' !== trim($node[$key])) {
                        $desc = trim(preg_replace('/\s+/u', ' ', $node[$key]) ?? '');
                        break;
                    }
                }

                $lines[] = '' !== $desc ? $name.' — '.mb_substr($desc, 0, 220) : $name;
            }

            foreach ($node as $child) {
                if (\is_array($child)) {
                    $walk($child);
                }
            }
        };

        $walk($data);

        return array_values(array_unique($lines));
    }

    /**
     * Links of a page (made absolute) → [URL => link text].
     *
     * @return array<string, string>
     */
    private function links(string $html, string $base): array
    {
        if (!preg_match_all('~<a\b[^>]*href=["\']([^"\'#]+)["\'][^>]*>(.*?)</a>~is', $html, $m, PREG_SET_ORDER)) {
            return [];
        }

        $origin = $this->origin($base);
        $out = [];

        foreach ($m as $hit) {
            $href = trim($hit[1]);

            if ('' === $href || str_starts_with($href, 'mailto:') || str_starts_with($href, 'javascript:')) {
                continue;
            }

            if (str_starts_with($href, '/')) {
                $href = $origin.$href;
            }

            if (!preg_match('#^https?://#i', $href)) {
                continue;
            }

            $label = trim(html_entity_decode(strip_tags($hit[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $out[$href] = mb_substr($label, 0, 120);

            if (\count($out) >= 300) {
                break;
            }
        }

        return $out;
    }

    /** HTML → running text, including the data from embedded JSON blocks. */
    private function toText(string $html): string
    {
        $data = $this->embeddedJson($html);

        $html = preg_replace('#<(script|style|noscript|svg)\b[^>]*>.*?</\1>#is', ' ', $html) ?? $html;
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[ \t\x{00A0}]+/u', ' ', $text) ?? $text;
        $text = trim(preg_replace('/\n\s*\n\s*\n+/', "\n\n", $text) ?? $text);

        if ('' !== $data) {
            $text = trim($text."\n\n".$data);
        }

        return mb_substr($text, 0, 60000);
    }

    /** Readable strings from embedded JSON blocks (Next.js, JSON-LD). */
    private function embeddedJson(string $html): string
    {
        if (!preg_match_all('#<script[^>]*type=["\'](?:application/json|application/ld\+json)["\'][^>]*>(.*?)</script>#is', $html, $m)) {
            return '';
        }

        $lines = [];

        foreach ($m[1] as $raw) {
            foreach ($this->jsonEntries(trim($raw)) as $line) {
                $lines[$line] = true;
            }
        }

        return [] === $lines ? '' : "Entries of the page:\n".implode("\n", \array_slice(array_keys($lines), 0, 300));
    }

    /**
     * Checks whether the domain offers an llms.txt and uses it instead of the HTML page.
     */
    private function preferLlmsTxt(AgentModel $agent, string $url): string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);

        // Already a text file or an API call with parameters? Then keep it.
        if (preg_match('/\.(txt|md|json)$/i', $path) || '' !== (string) parse_url($url, PHP_URL_QUERY)) {
            return $url;
        }

        $origin = $this->origin($url);

        if ('' === $origin || '/' !== ($path ?: '/')) {
            return $url;
        }

        $item = $this->cache->getItem('neo_agent_llms_'.hash('sha256', $origin));

        if ($item->isHit()) {
            $hit = (string) $item->get();

            return '' !== $hit ? $hit : $url;
        }

        $found = '';

        foreach (['/llms-full.txt', '/llms.txt'] as $candidatePath) {
            $candidate = $origin.$candidatePath;

            if (!$this->isAllowed($agent, $candidate)) {
                continue;
            }

            try {
                $response = $this->httpClient->request('GET', $candidate, ['max_redirects' => 2]);
                $type = strtolower((string) ($response->getHeaders(false)['content-type'][0] ?? ''));

                // Some servers return their 404 page with status 200, so also check
                // that the response is text and not HTML.
                if ($response->getStatusCode() < 400 && !str_contains($type, 'html')) {
                    $found = $candidate;
                    break;
                }
            } catch (\Throwable) {
                // next candidate
            }
        }

        $item->set($found)->expiresAfter(self::TTL * 6);
        $this->cache->save($item);

        return '' !== $found ? $found : $url;
    }

    /** @return list<string> */
    private function words(string $query): array
    {
        $stop = ['und', 'oder', 'die', 'der', 'das', 'für', 'fuer', 'mit', 'gibt', 'gibts', 'eine', 'einen', 'ein', 'ist', 'sind', 'was', 'wie', 'auch', 'von', 'bei', 'kann', 'man', 'the', 'and', 'for', 'with'];

        $words = array_filter(
            preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($query)) ?: [],
            static fn (string $w): bool => mb_strlen($w) >= 3 && !\in_array($w, $stop, true),
        );

        return array_values(array_unique($words));
    }

    private function origin(string $url): string
    {
        $scheme = (string) parse_url($url, PHP_URL_SCHEME);
        $host = (string) parse_url($url, PHP_URL_HOST);

        if ('' === $scheme || '' === $host) {
            return '';
        }

        $port = parse_url($url, PHP_URL_PORT);

        return $scheme.'://'.$host.(\is_int($port) ? ':'.$port : '');
    }

    /** Allowed hosts (including subdomains) from the approved list. */
    private function isAllowed(AgentModel $agent, string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (!\is_string($host) || '' === $host || $this->isPrivateHost($host)) {
            return false;
        }

        $host = strtolower($host);

        foreach ($this->parsedSources($agent) as $src) {
            $allowed = strtolower((string) parse_url($src['url'], PHP_URL_HOST));

            if ('' === $allowed) {
                continue;
            }

            if ($host === $allowed || str_ends_with($host, '.'.$allowed)) {
                return true;
            }
        }

        return false;
    }

    /** Blocks localhost, private networks and non-hostnames (SSRF protection). */
    private function isPrivateHost(string $host): bool
    {
        $host = strtolower(trim($host, '.'));

        if ('' === $host || 'localhost' === $host || str_ends_with($host, '.local') || str_ends_with($host, '.internal')) {
            return true;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        }

        return !str_contains($host, '.');
    }

    /**
     * SerpAPI hits, limited to the allowed domains.
     *
     * @return list<string>
     */
    private function serpApiUrls(AgentModel $agent, string $query, int $limit, string $key): array
    {
        $hosts = [];

        foreach ($this->parsedSources($agent) as $src) {
            $host = (string) parse_url($src['url'], PHP_URL_HOST);

            if ('' !== $host) {
                $hosts['site:'.$host] = true;
            }
        }

        if ([] === $hosts) {
            return [];
        }

        $sites = \array_slice(array_keys($hosts), 0, 8);
        $item = $this->cache->getItem('neo_agent_serp_'.hash('sha256', $query.'|'.implode(',', $sites)));

        if ($item->isHit()) {
            return (array) $item->get();
        }

        try {
            $response = $this->httpClient->request('GET', 'https://serpapi.com/search.json', [
                'query' => [
                    'q' => $query.' ('.implode(' OR ', $sites).')',
                    'api_key' => $key,
                    'num' => max(5, $limit * 2),
                ],
            ]);

            $data = $response->toArray(false);
        } catch (\Throwable $e) {
            $this->logger->warning('SuperAgent: SerpAPI search failed: '.$e->getMessage());

            return [];
        }

        $urls = [];

        foreach ((array) ($data['organic_results'] ?? []) as $hit) {
            $link = (string) ($hit['link'] ?? '');

            if ('' !== $link && $this->isAllowed($agent, $link)) {
                $urls[] = $link;
            }
        }

        $item->set($urls)->expiresAfter(self::TTL);
        $this->cache->save($item);

        return $urls;
    }
}

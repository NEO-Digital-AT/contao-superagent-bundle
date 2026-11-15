<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Chat;

use Neo\BasicBundle\Access\AccessSettings;
use Neo\BasicBundle\Security\SecretCipher;
use Neo\SuperAgentBundle\Chat\WebResearch;
use Neo\SuperAgentBundle\Model\AgentModel;
use Neo\SuperAgentBundle\Tests\Support\Models;
use Neo\SuperAgentBundle\Tests\Support\TestDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/** The approved sources are a hard limit, whatever a visitor asks for. */
#[CoversClass(WebResearch::class)]
final class WebResearchTest extends TestCase
{
    /** @var list<string> */
    private array $requested = [];

    /** @var array<string, MockResponse|string> address => answer (a string is HTML) */
    private array $web = [];

    private AccessSettings $access;

    protected function setUp(): void
    {
        $this->access = new AccessSettings(TestDatabase::create('tl_neo_access'), new SecretCipher('test-secret'));
    }

    public function testOnlyPublicAddressesCountAsSources(): void
    {
        $agent = $this->agent(['docs.example.com', 'https://shop.example.org/api/items | bikes helmets', 'http://localhost/admin', 'https://10.0.0.5/', 'https://192.168.1.1/', 'https://intranet/', 'https://printer.local/', 'https://[::1]/', 'docs.example.com', '']);

        $this->assertSame(['https://docs.example.com', 'https://shop.example.org/api/items'], $this->research()->sources($agent));
        $this->assertTrue($this->research()->isEnabled($agent));
        $this->assertFalse($this->research()->isEnabled($this->agent(['docs.example.com'], ['web_search' => ''])));
        $this->assertFalse($this->research()->isEnabled($this->agent(['http://127.0.0.1/'])));
    }

    public function testTheMatchingPassageOfASourceGoesToTheModel(): void
    {
        $this->web['https://docs.example.com/'] = '<html><head><style>.x{}</style></head><body><nav>Menu</nav><p>'.str_repeat('Filler text. ', 200).'</p><p>Opening hours: Monday to Friday 9 to 17.</p><script>var secret = 1;</script></body></html>';

        $result = $this->research()->search($this->agent(['https://docs.example.com/']), 'What are the opening hours?');

        $this->assertSame(['https://docs.example.com/'], $result['sources']);
        $this->assertStringStartsWith("### Source: https://docs.example.com/\n", $result['text']);
        $this->assertStringContainsString('Opening hours: Monday to Friday 9 to 17.', $result['text']);
        $this->assertStringNotContainsString('var secret', $result['text']);
        $this->assertStringNotContainsString('.x{}', $result['text']);
    }

    public function testACatalogueIsSearchedEntryByEntry(): void
    {
        $this->web['https://shop.example.org/api/items'] = new MockResponse(json_encode(['items' => [
            ['name' => 'City bike', 'description' => 'For the town.'],
            ['name' => 'Helmet', 'shortDescription' => 'Bike helmet in three sizes.'],
            ['title' => 'Gift card'],
        ]]), ['response_headers' => ['content-type' => 'application/json']]);

        $result = $this->research()->search($this->agent(['https://shop.example.org/api/items | bikes']), 'bike helmet sizes');

        $this->assertSame("### Source: https://shop.example.org/api/items\nHelmet — Bike helmet in three sizes.\nCity bike — For the town.", $result['text']);
    }

    public function testSubpagesAreFollowedOnlyOnTheApprovedHosts(): void
    {
        // The page itself does not answer; its links do (by their address).
        $this->web['https://docs.example.com/'] = '<a href="https://evil.example.net/prices">Offers</a><a href="/prices">Our offers</a><a href="https://help.docs.example.com/prices-faq">Questions</a><a href="javascript:alert(\'prices\')">Script</a>';
        $this->web['https://docs.example.com/prices'] = '<p>Prices: from 10 euros.</p>';
        $this->web['https://help.docs.example.com/prices-faq'] = '<p>Prices FAQ</p>';

        $result = $this->research()->search($this->agent(['https://docs.example.com/']), 'prices');

        $this->assertNotContains('https://evil.example.net/prices', $this->requested);
        $this->assertCount(1, $result['sources']);
        $this->assertContains($result['sources'][0], ['https://docs.example.com/prices', 'https://help.docs.example.com/prices-faq']);
    }

    public function testSearchHitsOfOtherDomainsAreDropped(): void
    {
        $this->access->store(['superagent_serpapi_key' => 'serp-key'], ['superagent_serpapi_key']);
        $this->web['https://serpapi.com/search.json'] = new MockResponse(json_encode(['organic_results' => [
            ['link' => 'https://attacker.example.net/page'],
            ['link' => 'http://192.168.0.10/internal'],
            ['link' => 'https://docs.example.com/offer'],
        ]]), ['response_headers' => ['content-type' => 'application/json']]);
        $this->web['https://docs.example.com/offer'] = '<p>Our offer: bikes for rent.</p>';

        $result = $this->research()->search($this->agent(['https://docs.example.com/'], ['web_search_mode' => 'serpapi']), 'bikes for rent');

        $this->assertSame(['https://docs.example.com/offer'], $result['sources']);
        $this->assertStringStartsWith('https://serpapi.com/search.json?', $this->requested[0]);
        parse_str((string) parse_url($this->requested[0], PHP_URL_QUERY), $query);
        $this->assertSame('bikes for rent (site:docs.example.com)', $query['q']);
        $this->assertSame('serp-key', $query['api_key']);
        $this->assertNotContains('https://attacker.example.net/page', $this->requested);
        $this->assertNotContains('http://192.168.0.10/internal', $this->requested);
    }

    public function testTheLlmsTxtOfASiteIsPreferred(): void
    {
        $this->web['https://docs.example.com/llms-full.txt'] = new MockResponse('', ['http_code' => 404]);
        $this->web['https://docs.example.com/llms.txt'] = new MockResponse("# Example\nRental bikes from 15 euros a day.", ['response_headers' => ['content-type' => 'text/plain']]);

        $result = $this->research()->search($this->agent(['https://docs.example.com']), 'rental bikes');

        $this->assertSame(['https://docs.example.com/llms.txt'], $result['sources']);
        $this->assertStringContainsString('Rental bikes from 15 euros a day.', $result['text']);
    }

    public function testNoSearchWithoutQueryOrSource(): void
    {
        $this->assertSame(['text' => '', 'sources' => []], $this->research()->search($this->agent(['docs.example.com']), '  '));
        $this->assertSame(['text' => '', 'sources' => []], $this->research()->search($this->agent([]), 'bikes'));
        $this->assertSame([], $this->requested);
    }

    /**
     * @param list<string>         $sources
     * @param array<string, mixed> $row
     */
    private function agent(array $sources, array $row = []): AgentModel
    {
        return Models::make(AgentModel::class, $row + ['web_search' => '1', 'web_search_mode' => 'sources', 'web_search_max' => 3, 'web_search_sources' => serialize($sources)]);
    }

    private function research(): WebResearch
    {
        $client = new MockHttpClient(function (string $method, string $url): MockResponse {
            $this->requested[] = $url;
            $base = explode('?', $url)[0];
            $answer = $this->web[$url] ?? $this->web[$base] ?? new MockResponse('', ['http_code' => 404]);

            return \is_string($answer) ? new MockResponse($answer, ['response_headers' => ['content-type' => 'text/html; charset=utf-8']]) : $answer;
        });

        return new WebResearch($client, new ArrayAdapter(), $this->access, new NullLogger());
    }
}

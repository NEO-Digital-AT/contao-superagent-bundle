<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Mcp;

use Neo\SuperAgentBundle\Mcp\McpResult;
use Neo\SuperAgentBundle\Mcp\McpTemplate;
use Neo\SuperAgentBundle\Mcp\McpTool;
use Neo\SuperAgentBundle\Model\ConnectionModel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(McpResult::class)]
#[CoversClass(McpTool::class)]
#[CoversClass(McpTemplate::class)]
final class McpResultTest extends TestCase
{
    public function testTextIsKeptAndOtherContentNamed(): void
    {
        $result = McpResult::fromArray(['content' => [
            ['type' => 'text', 'text' => 'Two rooms free.'],
            ['type' => 'image', 'data' => 'iVBOR...', 'mimeType' => 'image/png'],
            ['type' => 'resource_link', 'name' => 'Offer', 'uri' => 'https://example.com/offer.pdf'],
            ['type' => 'resource', 'resource' => ['text' => 'Embedded text']],
            'not an item',
        ], 'isError' => false]);

        $this->assertSame("Two rooms free.\n\n[image content not passed on]\n\n[resource link: Offer https://example.com/offer.pdf]\n\nEmbedded text", $result->text);
        $this->assertFalse($result->isError);
        $this->assertFalse($result->truncated);
    }

    public function testStructuredContentWhenThereIsNoText(): void
    {
        $result = McpResult::fromArray(['content' => [['type' => 'text', 'text' => ' ']], 'structuredContent' => ['rooms' => 2, 'url' => 'https://example.com/a'], 'isError' => true]);

        $this->assertSame('{"rooms":2,"url":"https://example.com/a"}', $result->text);
        $this->assertTrue($result->isError);
    }

    public function testALongResultIsCutAndTheModelIsTold(): void
    {
        $result = McpResult::fromArray(['content' => [['type' => 'text', 'text' => str_repeat('ä', McpResult::MAX_CHARS + 10)]]]);

        $this->assertTrue($result->truncated);
        $this->assertStringStartsWith(str_repeat('ä', McpResult::MAX_CHARS)."\n\n[Result cut after 20000 characters.", $result->text);
    }

    public function testAPdfInTheResultIsTakenOutOfTheTextInEveryShape(): void
    {
        $pdf = "%PDF-1.7\n1 0 obj << /Type /Catalog >> endobj\n%%EOF";
        $b64 = base64_encode($pdf);

        $blob = McpResult::fromArray(['content' => [['type' => 'resource', 'resource' => ['uri' => 'https://example.com/files/Invoice 2026-17.pdf?x=1', 'mimeType' => 'application/pdf', 'blob' => $b64]]]]);
        $this->assertSame('[file: Invoice-2026-17.pdf, PDF, 1 KB]', $blob->text);
        $this->assertSame([['name' => 'Invoice-2026-17.pdf', 'type' => 'application/pdf', 'data' => $pdf]], $blob->files);

        $text = McpResult::fromArray(['content' => [['type' => 'text', 'text' => ' '.chunk_split($b64, 20, "\n")]]]);
        $this->assertSame('[file: document, PDF, 1 KB]', $text->text);
        $this->assertSame($pdf, $text->files[0]['data']);
        $this->assertSame('', $text->files[0]['name'], 'the toolbox names it');

        $json = McpResult::fromArray(['content' => [['type' => 'text', 'text' => json_encode(['invoice' => ['id' => 'INV-1', 'fileName' => 'INV-1.pdf', 'content' => 'data:application/pdf;base64,'.$b64]])]]]);
        $this->assertSame('{"invoice":{"id":"INV-1","fileName":"INV-1.pdf","content":"[file: INV-1.pdf, PDF, 1 KB]"}}', $json->text);
        $this->assertSame('INV-1.pdf', $json->files[0]['name']);
    }

    public function testOnlyRealPdfFilesAreTaken(): void
    {
        $notPdf = base64_encode('%PDF-');
        $this->assertSame([], McpResult::fromArray(['content' => [['type' => 'text', 'text' => 'JVBERi0 is how a PDF starts in Base64.']]])->files);
        $this->assertSame([], McpResult::fromArray(['content' => [['type' => 'resource', 'resource' => ['uri' => 'a.png', 'blob' => base64_encode("\x89PNG")]]]])->files);
        $this->assertSame('[resource content not passed on]', McpResult::fromArray(['content' => [['type' => 'resource', 'resource' => ['uri' => 'a.png', 'blob' => base64_encode("\x89PNG")]]]])->text);
        $this->assertSame(['%PDF-'], array_column(McpResult::fromArray(['content' => [['type' => 'text', 'text' => $notPdf]]])->files, 'data'), 'the magic number is enough');
        $this->assertSame([], McpResult::fromArray(['content' => [['type' => 'text', 'text' => 'JVBERi0!!']]])->files, 'no valid Base64');
    }

    public function testTheKindOfAToolFromItsAnnotations(): void
    {
        $this->assertSame(McpTool::READ, McpTool::fromArray(['name' => 'a', 'annotations' => ['readOnlyHint' => true, 'destructiveHint' => true]])->kind());
        $this->assertSame(McpTool::DELETE, McpTool::fromArray(['name' => 'a', 'annotations' => ['destructiveHint' => true]])->kind());
        $this->assertSame(McpTool::CHANGE, McpTool::fromArray(['name' => 'a', 'annotations' => ['readOnlyHint' => false]])->kind());
        $this->assertSame(McpTool::CHANGE, McpTool::fromArray(['name' => 'a', 'annotations' => ['destructiveHint' => false]])->kind());
        $this->assertSame(McpTool::UNMARKED, McpTool::fromArray(['name' => 'a'])->kind(), 'may change or delete data by the specification');
        $this->assertSame(McpTool::UNMARKED, McpTool::fromArray(['name' => 'a', 'annotations' => ['readOnlyHint' => 'yes']])->kind());
    }

    public function testAToolAsTheServerListsIt(): void
    {
        $tool = McpTool::fromArray(['name' => ' book_room ', 'description' => ' Books a room. ', 'annotations' => ['title' => 'Book'], 'inputSchema' => ['properties' => ['email' => ['type' => 'string'], 'nights' => ['type' => 'integer']]]]);

        $this->assertSame(['name' => 'book_room', 'title' => 'Book', 'description' => 'Books a room.', 'inputSchema' => ['properties' => ['email' => ['type' => 'string'], 'nights' => ['type' => 'integer']], 'type' => 'object'], 'annotations' => ['title' => 'Book']], $tool->toArray());
        $this->assertTrue($tool->asksForPersonalData());
        $this->assertFalse(McpTool::fromArray(['name' => 'list_rooms', 'inputSchema' => ['properties' => ['date' => []]]])->asksForPersonalData());
        $this->assertNull(McpTool::fromArray(['name' => ' ']));
    }

    public function testOnlyInputsAboutAPersonCountAsPersonalData(): void
    {
        $tool = static fn (array $properties, string $description = ''): McpTool => McpTool::fromArray(['name' => 'tool', 'description' => $description, 'inputSchema' => ['properties' => $properties]]);

        // open_meteo_geocode of the Open-Meteo server, as tools/list delivers it: a place is no personal data.
        $geocode = McpTool::fromArray([
            'name' => 'open_meteo_geocode',
            'description' => '"What are the coordinates of [city]" / "lat lng for [place]" / "find [town] location" — resolve a place name (city, village, region) to lat/lng so the other Open-Meteo tools can use them.',
            'inputSchema' => ['type' => 'object', 'properties' => [
                'name' => ['type' => 'string', 'description' => 'Place name (any language)'],
                'count' => ['type' => 'number', 'description' => 'Max results, 1-100 (default 10)'],
                'language' => ['type' => 'string', 'description' => 'ISO-639 lang for returned names (default en)'],
            ], 'required' => ['name']],
        ]);
        $this->assertFalse($geocode->asksForPersonalData());
        $this->assertFalse($tool(['address' => ['description' => 'Street and town to find on the map']])->asksForPersonalData());
        $this->assertFalse($tool(['name' => ['type' => 'string']], 'Finds a hotel by name.')->asksForPersonalData());

        // Inputs that always belong to a person.
        $this->assertTrue($tool(['first_name' => [], 'nights' => []])->asksForPersonalData());
        $this->assertTrue($tool(['email' => ['description' => 'Where the confirmation goes']])->asksForPersonalData());
        $this->assertTrue($tool(['phoneNumber' => []])->asksForPersonalData());

        // "name" or "address" of a person: by the description of the input, without one by that of the tool.
        $this->assertTrue($tool(['name' => ['description' => 'Full name of the guest']])->asksForPersonalData());
        $this->assertTrue($tool(['address' => ['description' => 'Postal address of the customer']])->asksForPersonalData());
        $this->assertTrue($tool(['name' => []], 'Looks up a guest by name.')->asksForPersonalData());
    }

    public function testTemplateRulesOverrideTheServer(): void
    {
        $template = McpTemplate::fromArray('booking', [
            'name' => 'Example booking system',
            'url' => 'https://mcp.example.com/mcp',
            'auth' => ConnectionModel::AUTH_OAUTH,
            'oauth' => ['authorization_endpoint' => 'https://id.example.com/authorize', 'token_endpoint' => 'https://id.example.com/token', 'registration' => false, 'scope' => ' offline_access '],
            'tools' => ['kinds' => ['List*' => 'read', 'Delete*' => 'delete'], 'locked' => ['Delete*', 'RunNightAudit'], 'customer_data' => ['GetReservation'], 'recommended' => ['ListOffers', ['nested']]],
        ], sys_get_temp_dir());

        $this->assertSame(McpTool::READ, $template->kind(new McpTool('ListOffers', '', '', [], ['destructiveHint' => true])));
        $this->assertSame(McpTool::UNMARKED, $template->kind(new McpTool('BookRoom', '', '', [])));
        $this->assertTrue($template->isLocked('DeleteBooking'));
        $this->assertTrue($template->isLocked('GetReservation'), 'customer data is locked when it first appears');
        $this->assertTrue($template->isCustomerData('GetReservation'));
        $this->assertFalse($template->isLocked('ListOffers'));
        $this->assertTrue($template->isRecommended('ListOffers'));
        $this->assertSame(['ListOffers'], $template->recommended);
        $this->assertSame(['authorization_endpoint' => 'https://id.example.com/authorize', 'token_endpoint' => 'https://id.example.com/token', 'registration' => false, 'scope' => 'offline_access', 'resource' => true, 'app_help' => ''], $template->oauth);
    }

    public function testATemplateCanFreeToolsThatChangeSomething(): void
    {
        $template = McpTemplate::fromArray('booking', [
            'name' => 'Example booking system',
            'url' => 'https://mcp.example.com/mcp',
            'tools' => ['locked' => ['Delete*'], 'unlocked' => ['UpdateReservation', 'GetReservation', 'DeleteBooking'], 'customer_data' => ['GetReservation', 'ListReservations']],
        ], sys_get_temp_dir());

        $this->assertTrue($template->isUnlocked('UpdateReservation'));
        $this->assertFalse($template->isLocked('GetReservation'), 'customer data the template unlocks is free');
        $this->assertTrue($template->isCustomerData('GetReservation'), 'and still marked');
        $this->assertTrue($template->isLocked('ListReservations'), 'customer data not unlocked stays locked');
        $this->assertTrue($template->isLocked('DeleteBooking'), 'locked wins over unlocked');
        $this->assertFalse($template->isUnlocked('DeleteBooking'));
        $this->assertFalse($template->isUnlocked('CreateBooking'));
    }

    public function testSeveralSkillsAndTheSetupStepsPerLanguage(): void
    {
        $dir = sys_get_temp_dir().'/nsa-template-'.bin2hex(random_bytes(4));
        mkdir($dir.'/skills/verification', 0777, true);
        mkdir($dir.'/skills/booking', 0777, true);
        touch($dir.'/skills/verification/SKILL.md');
        touch($dir.'/skills/booking/SKILL.md');

        try {
            $template = McpTemplate::fromArray('hotel', [
                'name' => 'Hotel system',
                'url' => 'https://mcp.example.com/mcp',
                'skills' => ['skills/verification/SKILL.md', 'skills/booking/SKILL.md'],
                'setup' => ['en' => ['Create an app.', 'Enter {redirect_uri}.'], 'de' => ['App anlegen.']],
            ], $dir);

            $this->assertSame(['hotel/verification', 'hotel/booking'], array_keys($template->skillFiles));
            $this->assertSame(realpath($dir.'/skills/booking/SKILL.md'), $template->skillFileFor('hotel/booking'));
            $this->assertSame('', $template->skillFileFor('hotel'));
            $this->assertSame(['App anlegen.'], $template->setupSteps('de-AT'));
            $this->assertSame(['Create an app.', 'Enter {redirect_uri}.'], $template->setupSteps('fr'), 'English for another language');
        } finally {
            unlink($dir.'/skills/verification/SKILL.md');
            unlink($dir.'/skills/booking/SKILL.md');
            rmdir($dir.'/skills/verification');
            rmdir($dir.'/skills/booking');
            rmdir($dir.'/skills');
            rmdir($dir);
        }
    }

    public function testInvalidTemplatesAreRefused(): void
    {
        $base = ['name' => 'Shop', 'url' => 'https://mcp.example.com/mcp'];
        $cases = [
            'unknown keys: command' => $base + ['command' => 'x'],
            'name and an https url are required' => ['name' => 'Shop', 'url' => 'http://mcp.example.com/mcp'],
            'auth must be none, key, headers or oauth' => $base + ['auth' => 'password'],
            'oauth needs both endpoints or none' => $base + ['oauth' => ['authorization_endpoint' => 'https://id.example.com/authorize']],
            'oauth endpoints must be https' => $base + ['oauth' => ['authorization_endpoint' => 'http://id.example.com/a', 'token_endpoint' => 'https://id.example.com/t']],
            'tools.kinds: X has the unknown kind "run"' => $base + ['tools' => ['kinds' => ['X' => 'run']]],
            'skill must be a SKILL.md inside the template folder' => $base + ['skill' => '../../etc/passwd'],
            'setup: a two-letter language with a list of steps, e.g. {"en": ["…"]}' => $base + ['setup' => ['english' => ['x']]],
        ];

        foreach ($cases as $reason => $data) {
            try {
                McpTemplate::fromArray('shop', $data, sys_get_temp_dir());
                $this->fail('Accepted: '.$reason);
            } catch (\InvalidArgumentException $e) {
                $this->assertSame($reason, $e->getMessage());
            }
        }
    }
}

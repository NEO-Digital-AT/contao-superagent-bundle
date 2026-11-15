<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Mcp;

use Contao\Model\Registry;
use Neo\SuperAgentBundle\Mcp\GuestCheck;
use Neo\SuperAgentBundle\Mcp\McpResult;
use Neo\SuperAgentBundle\Mcp\McpTemplate;
use Neo\SuperAgentBundle\Model\ConnectionModel;
use Neo\SuperAgentBundle\Tests\Support\Models;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The guest check with a booking system shaped like the recorded answers of a
 * hotel PMS: a reservation with primary guest and booker, two rooms in one
 * booking, folios of a reservation.
 */
#[CoversClass(GuestCheck::class)]
final class GuestCheckTest extends TestCase
{
    private ArrayAdapter $cache;

    private ConnectionModel $connection;

    private McpTemplate $template;

    /** @var list<array{string, array<string, mixed>}> */
    private array $lookups = [];

    protected function setUp(): void
    {
        Registry::getInstance()->reset();
        $this->cache = new ArrayAdapter();
        $this->connection = Models::register(ConnectionModel::class, ['id' => 7, 'name' => 'Hotel system']);
        $this->template = McpTemplate::fromArray('hotel', [
            'name' => 'Hotel system',
            'url' => 'https://mcp.example.com/mcp',
            'guest_check' => [
                'lookups' => [
                    ['tool' => 'GetReservation', 'arguments' => ['id' => '{reference}', 'expand' => ['booker']]],
                    ['tool' => 'ListReservations', 'arguments' => ['bookingIds' => ['{reference}'], 'expand' => ['booker']]],
                ],
                'group' => 'bookingId',
                'persons' => ['primaryGuest', 'booker', 'additionalGuests'],
                'group_person' => 'booker',
                'arguments' => ['id', 'reservationId', 'reservationIds', 'bookingId', 'bookingIds', 'folioId', 'folioIds'],
                'protected' => ['GetReservation', 'ListReservations', 'ListFolios', 'GetFolio', 'Update*'],
                'creates' => ['CreateBooking'],
            ],
        ], sys_get_temp_dir());
    }

    protected function tearDown(): void
    {
        Registry::getInstance()->reset();
    }

    public function testTheGuestWithNameAndEmailOfTheSamePersonIsVerifiedForTheReservationOnly(): void
    {
        $check = $this->check();
        $answer = $check->verify($this->connection, $this->template, ['reference' => 'ABCDEF-1', 'name' => 'Anna Gast', 'contact' => 'ANNA@example.com'], $this->booking(...));

        $this->assertStringStartsWith('VERIFIED: the visitor is the primaryGuest of ABCDEF-1.', $answer);
        $this->assertStringNotContainsString('anna@example.com', $answer, 'no stored data in the answer');
        $this->assertSame([['GetReservation', ['id' => 'ABCDEF-1', 'expand' => ['booker']]]], $this->lookups);
        $this->assertNull($check->refuse($this->connection, $this->template, ['id' => 'ABCDEF-1']));
        $this->assertStringStartsWith('TOOL NOT RUN: ABCDEF-2 does not belong', (string) $check->refuse($this->connection, $this->template, ['id' => 'ABCDEF-2']));
        $this->assertStringStartsWith('TOOL NOT RUN: ABCDEF does not belong', (string) $check->refuse($this->connection, $this->template, ['bookingIds' => ['ABCDEF']]), 'the guest of one room does not get the booking');
    }

    public function testTheBookerGetsTheWholeBooking(): void
    {
        $check = $this->check();
        $answer = $check->verify($this->connection, $this->template, ['reference' => 'ABCDEF', 'name' => 'Berta Bucher', 'contact' => '+43 660 1234567'], $this->booking(...));

        $this->assertStringStartsWith('VERIFIED: the visitor is the booker of ABCDEF-1, ABCDEF, ABCDEF-2.', $answer);
        $this->assertSame('ListReservations', $this->lookups[1][0], 'a booking number is looked up as a booking after the reservation lookup found nothing');
        $this->assertNull($check->refuse($this->connection, $this->template, ['bookingIds' => ['ABCDEF']]));
        $this->assertNull($check->refuse($this->connection, $this->template, ['id' => 'ABCDEF-2']));
    }

    public function testNameAndContactOfDifferentPersonsOrAWrongContactAreNoProof(): void
    {
        $check = $this->check();

        $this->assertStringStartsWith('NOT VERIFIED', $check->verify($this->connection, $this->template, ['reference' => 'ABCDEF-1', 'name' => 'Anna Gast', 'contact' => '+43 660 1234567'], $this->booking(...)), "the guest's name with the booker's phone");
        $this->assertStringStartsWith('NOT VERIFIED', $check->verify($this->connection, $this->template, ['reference' => 'ABCDEF-1', 'name' => 'Anna Gast', 'contact' => 'other@example.com'], $this->booking(...)));
        $this->assertStringStartsWith('NOT VERIFIED', $check->verify($this->connection, $this->template, ['reference' => 'ZZZZZZ-1', 'name' => 'Anna Gast', 'contact' => 'anna@example.com'], $this->booking(...)), 'an unknown number says the same');
        $this->assertStringStartsWith('TOOL NOT RUN: this tool shows or changes', (string) $check->refuse($this->connection, $this->template, ['id' => 'ABCDEF-1']));
        $this->assertSame('', $check->token());
    }

    public function testSpellingOfNamesAndPhoneNumbersDoesNotMatter(): void
    {
        $check = $this->check();

        $this->assertStringStartsWith('VERIFIED', $check->verify($this->connection, $this->template, ['reference' => 'ABCDEF-2', 'name' => 'jürgen  MÜLLER-Lüdenscheidt', 'contact' => '0043 (660) 765 43 21'], $this->booking(...)));
        $this->assertStringStartsWith('VERIFIED', $this->check()->verify($this->connection, $this->template, ['reference' => 'ABCDEF-2', 'name' => 'Muller Ludenscheidt', 'contact' => '0660 7654321'], $this->booking(...)), 'the surname alone, without accents');
    }

    public function testFiveMissesLockTheCheckFor24Hours(): void
    {
        for ($i = 0; $i < 5; ++$i) {
            $this->check()->verify($this->connection, $this->template, ['reference' => 'ABCDEF-1', 'name' => 'Anna Gast', 'contact' => 'guess'.$i.'@example.com'], $this->booking(...));
        }

        $this->assertStringStartsWith('LOCKED', $this->check()->verify($this->connection, $this->template, ['reference' => 'ABCDEF-1', 'name' => 'Anna Gast', 'contact' => 'anna@example.com'], $this->booking(...)), 'even the right details, once locked');
        $this->assertStringStartsWith('VERIFIED', $this->check('192.0.2.9')->verify($this->connection, $this->template, ['reference' => 'ABCDEF-1', 'name' => 'Anna Gast', 'contact' => 'anna@example.com'], $this->booking(...)), 'per IP address');
    }

    public function testTheTokenCarriesTheProofToTheNextMessageAndCannotBeForged(): void
    {
        $check = $this->check();
        $check->verify($this->connection, $this->template, ['reference' => 'ABCDEF-1', 'name' => 'Anna Gast', 'contact' => 'anna@example.com'], $this->booking(...));
        $token = $check->token();

        $next = $this->check();
        $next->load(3, $token);
        $this->assertNull($next->refuse($this->connection, $this->template, ['id' => 'ABCDEF-1']));

        [$payload, $signature] = explode('.', $token);
        $forged = rtrim(strtr(base64_encode((string) json_encode(['a' => 3, 's' => [7 => ['i' => ['ABCDEF-1', 'ABCDEF-2'], 'e' => time() + 3600]]])), '+/', '-_'), '=');

        foreach ([$forged.'.'.$signature, $payload.'.x', 'nonsense'] as $bad) {
            $other = $this->check();
            $other->load(3, $bad);
            $this->assertNotNull($other->refuse($this->connection, $this->template, ['id' => 'ABCDEF-1']), 'refused: '.$bad);
        }

        $otherAgent = $this->check();
        $otherAgent->load(4, $token);
        $this->assertNotNull($otherAgent->refuse($this->connection, $this->template, ['id' => 'ABCDEF-1']), 'bound to the assistant');
    }

    public function testResultsMustBelongToTheReservationAndBringTheirFolios(): void
    {
        $check = $this->check();
        $check->verify($this->connection, $this->template, ['reference' => 'ABCDEF-1', 'name' => 'Anna Gast', 'contact' => 'anna@example.com'], $this->booking(...));

        $folios = json_encode(['data' => ['folios' => [['id' => 'ABCDEF-1-1', 'reservation' => ['id' => 'ABCDEF-1'], 'balance' => ['amount' => 0]], ['id' => 'ABCDEF-1-2', 'reservation' => ['id' => 'ABCDEF-1']]]]]);
        $this->assertNull($check->withhold($this->connection, $this->template, (string) $folios));
        $this->assertNull($check->refuse($this->connection, $this->template, ['folioId' => 'ABCDEF-1-2']), 'a folio of the reservation is usable afterwards');

        $other = json_encode(['data' => ['reservations' => [['id' => 'XYZ-1', 'primaryGuest' => ['lastName' => 'Fremd']]]]]);
        $this->assertStringStartsWith('TOOL RESULT WITHHELD', (string) $check->withhold($this->connection, $this->template, (string) $other));
        $this->assertStringStartsWith('TOOL RESULT WITHHELD', (string) $check->withhold($this->connection, $this->template, 'not json'));
        $this->assertStringStartsWith('TOOL NOT RUN: name the verified record', (string) $check->refuse($this->connection, $this->template, ['textSearch' => 'Gast']), 'no searches across reservations');
    }

    public function testRecordsNamedDeepInTheArgumentsAndArgumentsTheToolDoesNotKnow(): void
    {
        $check = $this->check();
        $check->verify($this->connection, $this->template, ['reference' => 'ABCDEF-1', 'name' => 'Anna Gast', 'contact' => 'anna@example.com'], $this->booking(...));

        $this->assertStringStartsWith('TOOL NOT RUN: XYZ-9 does not belong', (string) $check->refuse($this->connection, $this->template, ['body' => ['folioId' => 'XYZ-9', 'languageCode' => 'de']]), 'a record in the body of a call');
        $this->assertStringStartsWith('TOOL NOT RUN: XYZ-9 does not belong', (string) $check->refuse($this->connection, $this->template, ['id' => 'ABCDEF-1', 'body' => ['items' => [['folioId' => 'XYZ-9']]]]), 'also in a list');
        $this->assertNull($check->refuse($this->connection, $this->template, ['body' => ['folioId' => 'ABCDEF-1', 'languageCode' => 'de']]));

        $this->assertSame('TOOL NOT RUN: text_search is not an argument of this tool.', $check->refuse($this->connection, $this->template, ['id' => 'ABCDEF-1', 'text_search' => 'Gast'], ['id', 'expand']), 'a verified id must not stand next to a filter the tool ignores');
        $this->assertNull($check->refuse($this->connection, $this->template, ['id' => 'ABCDEF-1', 'expand' => ['booker']], ['id', 'expand']));
    }

    public function testAResultMayNotShowAnotherGuestsRecordEvenNextToTheVerifiedOne(): void
    {
        $check = $this->check();
        $check->verify($this->connection, $this->template, ['reference' => 'ABCDEF-1', 'name' => 'Anna Gast', 'contact' => 'anna@example.com'], $this->booking(...));

        $mixed = json_encode(['reservations' => [['id' => 'ABCDEF-1', 'primaryGuest' => ['lastName' => 'Gast']], ['id' => 'XYZ-1', 'primaryGuest' => ['lastName' => 'Fremd']]]]);
        $this->assertStringStartsWith('TOOL RESULT WITHHELD', (string) $check->withhold($this->connection, $this->template, (string) $mixed));

        $offers = json_encode(['services' => [['service' => ['id' => 'SRV-1', 'name' => 'Breakfast'], 'totalAmount' => ['amount' => 18]]]]);
        $this->assertNull($check->withhold($this->connection, $this->template, (string) $offers), 'offers for the reservation name no guest and need not repeat its number');
    }

    public function testWhatTheVisitorBooksInTheChatCountsAsVerified(): void
    {
        $check = $this->check();
        $this->assertTrue($check->isCreating($this->template, 'CreateBooking'));
        $this->assertFalse($check->isCreating($this->template, 'GetReservation'));

        $check->created($this->connection, $this->template, (string) json_encode(['id' => 'NEWBK', 'reservationIds' => [['id' => 'NEWBK-1']]]));

        $this->assertNull($check->refuse($this->connection, $this->template, ['id' => 'NEWBK-1']));
        $this->assertNull($check->refuse($this->connection, $this->template, ['bookingIds' => ['NEWBK']]));
        $this->assertNotNull($check->refuse($this->connection, $this->template, ['id' => 'ABCDEF-1']), 'nothing else');
        $this->assertTrue($check->changed());

        $next = $this->check();
        $next->load(3, $check->token());
        $this->assertNull($next->refuse($this->connection, $this->template, ['id' => 'NEWBK-1']), 'kept for the next message');
    }

    public function testATemplateNeedsTheWholeConfiguration(): void
    {
        $this->expectExceptionMessage('guest_check needs lookups, persons, arguments and protected');

        McpTemplate::fromArray('hotel', ['name' => 'Hotel', 'url' => 'https://mcp.example.com/mcp', 'guest_check' => ['lookups' => [['tool' => 'GetReservation']]]], sys_get_temp_dir());
    }

    private function check(string $ip = '198.51.100.7'): GuestCheck
    {
        $requests = new RequestStack();
        $requests->push(Request::create('https://www.example.com/api/chat', 'POST', server: ['REMOTE_ADDR' => $ip]));
        $check = new GuestCheck('test-secret', $this->cache, $requests);
        $check->load(3, '');

        return $check;
    }

    /**
     * The booking system: ABCDEF-1 (primary guest Anna Gast) and ABCDEF-2
     * (Jürgen Müller-Lüdenscheidt), booked by Berta Bucher.
     *
     * @param array<string, mixed> $arguments
     */
    private function booking(string $tool, array $arguments): McpResult
    {
        $this->lookups[] = [$tool, $arguments];
        $booker = ['firstName' => 'Berta', 'lastName' => 'Bucher', 'email' => 'berta@example.com', 'phone' => '+43 660 1234567'];
        $reservations = [
            'ABCDEF-1' => ['id' => 'ABCDEF-1', 'bookingId' => 'ABCDEF', 'primaryGuest' => ['firstName' => 'Anna', 'lastName' => 'Gast', 'email' => 'anna@example.com'], 'booker' => $booker],
            'ABCDEF-2' => ['id' => 'ABCDEF-2', 'bookingId' => 'ABCDEF', 'primaryGuest' => ['firstName' => 'Jürgen', 'lastName' => 'Müller-Lüdenscheidt', 'phone' => '+43 660 7654321'], 'booker' => $booker],
        ];

        if ('GetReservation' === $tool) {
            $found = $reservations[$arguments['id']] ?? null;

            return McpResult::fromArray(null === $found ? ['content' => [['type' => 'text', 'text' => 'Not found']], 'isError' => true] : ['content' => [['type' => 'text', 'text' => json_encode(['data' => $found])]]]);
        }

        $list = array_values(array_filter($reservations, static fn (array $r): bool => \in_array($r['bookingId'], (array) $arguments['bookingIds'], true)));

        return McpResult::fromArray(['content' => [['type' => 'text', 'text' => json_encode(['data' => ['reservations' => $list, 'count' => \count($list)]])]]]);
    }
}

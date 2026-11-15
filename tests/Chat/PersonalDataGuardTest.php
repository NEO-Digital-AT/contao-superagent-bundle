<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Chat;

use Neo\SuperAgentBundle\Chat\PersonalDataGuard;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Privacy mode: identifiers that never reach the AI service. */
#[CoversClass(PersonalDataGuard::class)]
final class PersonalDataGuardTest extends TestCase
{
    /** @return iterable<string, array{string, string, list<string>}> */
    public static function identifiers(): iterable
    {
        yield 'e-mail address' => ['Write to max.mustermann@example.com please', 'Write to [removed: e-mail address] please', ['email']];
        yield 'IBAN with spaces' => ['My IBAN is AT61 1904 3002 3457 3201.', 'My IBAN is [removed: IBAN].', ['iban']];
        yield 'IBAN followed by a word' => ['IBAN DE89370400440532013000 BITTE prüfen', 'IBAN [removed: IBAN] BITTE prüfen', ['iban']];
        yield 'card number' => ['Card 4111 1111 1111 1111 expires soon', 'Card [removed: card number] expires soon', ['card']];
        yield 'social security number' => ['SVNR 1237 010180', 'SVNR [removed: social security number]', ['svnr']];
        yield 'date of birth before the word' => ['12.03.1985 is my date of birth', '[removed: date of birth] is my date of birth', ['birthdate']];
        yield 'date of birth after the word' => ['Ich bin geboren am 12.03.1985 in Musterstadt', 'Ich bin geboren am [removed: date of birth] in Musterstadt', ['birthdate']];
        yield 'phone international' => ['Call +43 664 1234567 today', 'Call [removed: phone number] today', ['phone']];
        yield 'phone national' => ['Tel. 0664/1234567', 'Tel. [removed: phone number]', ['phone']];
        yield 'several' => ['max@example.com, +49 30 1234567', '[removed: e-mail address], [removed: phone number]', ['email', 'phone']];
    }

    /** @param list<string> $types */
    #[DataProvider('identifiers')]
    public function testIdentifiersAreReplacedByAMarker(string $text, string $expected, array $types): void
    {
        $guard = new PersonalDataGuard();

        $this->assertSame(['text' => $expected, 'types' => $types], $guard->redact($text));
        $this->assertSame($types[0], $guard->find($text));
    }

    /** @return iterable<string, array{string}> */
    public static function noIdentifiers(): iterable
    {
        yield 'question' => ['When are you open on Saturday?'];
        yield 'a date that is no birth date' => ['Is the 12.03.2027 still free?'];
        yield 'IBAN with a wrong check digit' => ['AT61 1904 3002 3457 3202'];
        yield 'number that fails the card check' => ['Order 4111 1111 1111 1112'];
        yield 'repeated digits' => ['0000 0000 0000 0000'];
        yield 'short number' => ['Room 0123'];
        yield 'price' => ['It costs 1.250,00 euros'];
        yield 'invalid social security number' => ['1234 010180'];
        // A place or an address without a name is no personal data (weather, directions).
        yield 'place with postcode' => ['What will the weather be tomorrow in 1010 Vienna?'];
        yield 'address' => ['How far is it from Main Street 12, 10115 Berlin to the hotel?'];
        yield 'country and city' => ['We come from Germany, postcode 80331 Munich'];
        yield 'stay with a child' => ['Arrival 15.11.2026, 2 adults and a child (7 years)'];
    }

    #[DataProvider('noIdentifiers')]
    public function testOtherTextStaysAsItIs(string $text): void
    {
        $guard = new PersonalDataGuard();

        $this->assertSame(['text' => $text, 'types' => []], $guard->redact($text));
        $this->assertNull($guard->find($text));
    }
}

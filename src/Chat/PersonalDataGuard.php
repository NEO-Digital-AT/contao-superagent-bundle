<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Chat;

/**
 * Privacy mode, first line: finds personal identifiers in a visitor message
 * before anything is sent to the AI service and replaces them with a marker. The
 * identifier never reaches the AI service; the model answers the rest of the
 * message (skill privacy).
 *
 * Found are e-mail addresses, phone numbers, IBANs, payment card numbers,
 * Austrian social security numbers and dates of birth (a date next to a word
 * like "born"). Everything that cannot be told by its form (names, addresses,
 * health information in free text) is left to the rule for the model.
 */
final class PersonalDataGuard
{
    /** Marker text per type, for the model (the privacy skill explains the markers). */
    private const LABELS = [
        'email' => 'e-mail address',
        'phone' => 'phone number',
        'iban' => 'IBAN',
        'card' => 'card number',
        'svnr' => 'social security number',
        'birthdate' => 'date of birth',
    ];

    /** Words that turn a date into a date of birth, in the eleven shipped languages. */
    private const BIRTH_WORDS = 'geboren|geb\.|geburtsdatum|geburtstag|born|birthday|date of birth|dob|né|née|naissance|geboortedatum|nato|nata|data di nascita|urodzon[ay]|data urodzenia|narozen[aá]?|datum narození|született|születési|rojen[a]?|datum rojstva|rođen[a]?|datum rođenja|nacid[oa]|fecha de nacimiento';

    /** Type of the first identifier found ("email", "phone", "iban", "card", "svnr", "birthdate"), or null. */
    public function find(string $text): ?string
    {
        return $this->redact($text)['types'][0] ?? null;
    }

    /**
     * The text with every identifier found replaced by a marker such as
     * "[removed: e-mail address]", and the types removed, in order of the checks.
     *
     * @return array{text: string, types: list<string>}
     */
    public function redact(string $text): array
    {
        $types = [];
        $mark = static function (string $type) use (&$types): string {
            $types[] = $type;

            return '[removed: '.self::LABELS[$type].']';
        };

        $text = (string) preg_replace_callback('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', static fn (): string => $mark('email'), $text);

        $text = (string) preg_replace_callback('/\b[A-Z]{2}\d{2}(?:[ ]?[A-Z0-9]){11,30}\b/i', static function (array $m) use ($mark): string {
            // The match may run into the next word ("… 3201 BITTE"): find the longest valid prefix.
            $candidate = $m[0];
            $compact = strtoupper(str_replace(' ', '', $candidate));

            for ($length = min(34, \strlen($compact)); $length >= 15; --$length) {
                if (self::validIban(substr($compact, 0, $length))) {
                    $cut = 0;

                    for ($seen = 0; $seen < $length; ++$cut) {
                        if (' ' !== $candidate[$cut]) {
                            ++$seen;
                        }
                    }

                    return $mark('iban').substr($candidate, $cut);
                }
            }

            return $candidate;
        }, $text);

        $text = (string) preg_replace_callback('/(?<![\d])(?:\d[ -]?){12,18}\d(?![\d])/', static fn (array $m): string => self::luhn((string) preg_replace('/\D/', '', $m[0])) ? $mark('card') : $m[0], $text);

        $text = (string) preg_replace_callback('/(?<![\d])\d{4} ?\d{6}(?![\d])/', static fn (array $m): string => self::validSvnr((string) preg_replace('/\D/', '', $m[0])) ? $mark('svnr') : $m[0], $text);

        $date = '\d{1,2}[.\/-]\s?\d{1,2}[.\/-]\s?(?:19|20)?\d{2}';
        $text = (string) preg_replace_callback(
            '/(?:'.self::BIRTH_WORDS.')\W{0,20}(?:\w+\W+){0,3}'.$date.'|'.$date.'\W{0,5}(?:\w+\W+){0,2}(?:'.self::BIRTH_WORDS.')/iu',
            static fn (array $m): string => (string) preg_replace_callback('/'.$date.'/u', static fn (): string => $mark('birthdate'), $m[0], 1),
            $text,
        );

        // Phone: starts with +, 00 or 0, 8 to 15 digits with the usual separators; dates are not phones.
        $text = (string) preg_replace_callback('/(?<![\w.,])(?:\+|0)[\d][\d \/().-]{5,}\d(?![\w])/u', static function (array $m) use ($mark, $date): string {
            $digits = \strlen((string) preg_replace('/\D/', '', $m[0]));

            return $digits >= 8 && $digits <= 15 && 1 !== preg_match('/^'.$date.'$/', trim($m[0])) ? $mark('phone') : $m[0];
        }, $text);

        return ['text' => $text, 'types' => $types];
    }

    private static function validIban(string $iban): bool
    {
        $iban = str_replace(' ', '', $iban);

        if (\strlen($iban) < 15 || \strlen($iban) > 34) {
            return false;
        }

        $digits = '';

        foreach (str_split(substr($iban, 4).substr($iban, 0, 4)) as $char) {
            $digits .= ctype_alpha($char) ? (string) (\ord($char) - 55) : $char;
        }

        $rest = 0;

        foreach (str_split($digits, 7) as $chunk) {
            $rest = (int) ($rest.$chunk) % 97;
        }

        return 1 === $rest;
    }

    private static function luhn(string $number): bool
    {
        if (\strlen($number) < 13 || \strlen($number) > 19 || 1 === preg_match('/^(\d)\1+$/', $number)) {
            return false;
        }

        $sum = 0;
        $double = false;

        for ($i = \strlen($number) - 1; $i >= 0; --$i) {
            $d = (int) $number[$i];

            if ($double && ($d *= 2) > 9) {
                $d -= 9;
            }

            $sum += $d;
            $double = !$double;
        }

        return 0 === $sum % 10;
    }

    /** Austrian social security number: 3 digits, check digit, date of birth DDMMYY. */
    private static function validSvnr(string $n): bool
    {
        if (10 !== \strlen($n) || '0' === $n[0]) {
            return false;
        }

        $day = (int) substr($n, 4, 2);
        $month = (int) substr($n, 6, 2);

        if ($day < 1 || $day > 31 || $month < 1 || $month > 12) {
            return false;
        }

        $weights = [3, 7, 9, 0, 5, 8, 4, 2, 1, 6];
        $sum = 0;

        foreach ($weights as $i => $w) {
            $sum += $w * (int) $n[$i];
        }

        return $sum % 11 === (int) $n[3];
    }
}

<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Chat;

use Neo\SuperAgentBundle\Chat\PhoneCodes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PhoneCodes::class)]
final class PhoneCodesTest extends TestCase
{
    public function testNumbersInE164Form(): void
    {
        $this->assertTrue(PhoneCodes::isE164('+436641234567'));
        $this->assertTrue(PhoneCodes::isE164('+12025550123'));
        $this->assertTrue(PhoneCodes::isE164('+3851234567'));

        $this->assertFalse(PhoneCodes::isE164('06641234567'), 'without country code');
        $this->assertFalse(PhoneCodes::isE164('+43 664 1234567'), 'with separators');
        $this->assertFalse(PhoneCodes::isE164('+0123456789'));
        $this->assertFalse(PhoneCodes::isE164('+43123'), 'too short');
        $this->assertFalse(PhoneCodes::isE164('+4366412345678901'), 'too long');
        $this->assertFalse(PhoneCodes::isE164('+8091234567'), 'no country has the code 8, 80 or 809');
    }

    public function testThePreselectedCountry(): void
    {
        $this->assertSame('CH', PhoneCodes::defaultCountry('CH', 'de-AT'), 'the setting of the assistant');
        $this->assertSame('AT', PhoneCodes::defaultCountry('', 'de-AT'), 'the region of the page');
        $this->assertSame('AT', PhoneCodes::defaultCountry('', 'de_AT'));
        $this->assertSame('DE', PhoneCodes::defaultCountry('', 'de'), 'the language of the page');
        $this->assertSame('SI', PhoneCodes::defaultCountry('XX', 'sl'));
        $this->assertSame('DE', PhoneCodes::defaultCountry('', 'pt'), 'otherwise');
    }

    public function testTrunkPrefixesBelongToKnownCountries(): void
    {
        foreach (array_keys(PhoneCodes::TRUNK) as $country) {
            $this->assertArrayHasKey($country, PhoneCodes::DIAL);
        }
    }
}

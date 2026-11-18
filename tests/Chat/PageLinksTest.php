<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Chat;

use Neo\SuperAgentBundle\Chat\PageLinks;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PageLinks::class)]
final class PageLinksTest extends TestCase
{
    private const KNOWLEDGE = "Pages:\n- Contact: https://www.example.com/en/contact\n- Prices: see /en/prices.\nBook via https://booking.example.net/hotel.";

    public function testALinkToAPageOfTheWebsiteThatIsNotInTheKnowledgeLeavesItsLabel(): void
    {
        $this->assertSame('You find it on the page Offer.', PageLinks::limit('You find it on the page [Offer](/en/offer).', 'www.example.com', self::KNOWLEDGE));
        $this->assertSame("Here:\nOffer", PageLinks::limit("Here:\n[Offer](https://www.example.com/en/offer)", 'www.example.com', self::KNOWLEDGE), 'a link alone in its line would be a button');
        $this->assertSame('Offer', PageLinks::limit('[Offer](https://example.com/en/offer/)', 'www.example.com', self::KNOWLEDGE), 'with or without www and trailing slash');
    }

    public function testALinkWhoseAddressIsInTheKnowledgeStays(): void
    {
        foreach (['[Contact](https://www.example.com/en/contact)', '[Contact](/en/contact)', '[Contact](https://example.com/en/contact/)', '[Prices](/en/prices)'] as $reply) {
            $this->assertSame($reply, PageLinks::limit($reply, 'www.example.com', self::KNOWLEDGE), $reply);
        }
    }

    public function testBareAddressesOfTheWebsiteBecomeText(): void
    {
        $this->assertSame('See www.example.com/en/offer or <https://www.example.com/en/contact>.', PageLinks::limit('See https://www.example.com/en/offer or <https://www.example.com/en/contact>.', 'www.example.com', self::KNOWLEDGE));
        $this->assertSame('See www.example.com/en/offer.', PageLinks::limit('See <https://www.example.com/en/offer>.', 'www.example.com', self::KNOWLEDGE));
    }

    public function testOtherLinksAreLeftAsTheyAre(): void
    {
        $reply = "[Booking](https://booking.example.net/hotel), [Partner](https://partner.example.org/x), [Mail](mailto:info@example.com), [Call](tel:+43123456), https://other.example.org/page";

        $this->assertSame($reply, PageLinks::limit($reply, 'www.example.com', self::KNOWLEDGE));
    }

    public function testAnAddressInTheLabelOrInTextDoesNotUnlockAPage(): void
    {
        // Only the knowledge counts, not what the reply itself says.
        $this->assertSame('Offer (www.example.com/en/offer is the page)', PageLinks::limit('[Offer](/en/offer) (https://www.example.com/en/offer is the page)', 'www.example.com', self::KNOWLEDGE));
    }

    public function testWithoutHostNothingChanges(): void
    {
        $this->assertSame('[Offer](/en/offer)', PageLinks::limit('[Offer](/en/offer)', '', self::KNOWLEDGE));
    }
}

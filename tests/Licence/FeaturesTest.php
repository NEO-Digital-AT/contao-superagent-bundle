<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Licence;

use Neo\SuperAgentBundle\Licence\FeatureResolver;
use Neo\SuperAgentBundle\Licence\Features;
use Neo\SuperAgentBundle\Tests\Support\Licences;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Features::class)]
#[CoversClass(FeatureResolver::class)]
final class FeaturesTest extends TestCase
{
    /** @return iterable<string, array{string, array<string, bool>}> */
    public static function editions(): iterable
    {
        $none = ['navigation' => false, 'contact' => false, 'webSearch' => false, 'voice' => false, 'mcp' => false, 'skills' => false];

        yield 'without licence' => ['', $none];
        yield 'unknown edition' => ['gold', $none];
        yield 'basic' => ['basic', ['navigation' => true, 'contact' => true, 'webSearch' => true] + $none];
        yield 'plus' => ['plus', ['navigation' => true, 'contact' => true, 'webSearch' => true, 'voice' => false, 'mcp' => true, 'skills' => true]];
        yield 'pro' => ['pro', ['navigation' => true, 'contact' => true, 'webSearch' => true, 'voice' => true, 'mcp' => true, 'skills' => true]];
    }

    /** @param array<string, bool> $expected */
    #[DataProvider('editions')]
    public function testEachEditionIncludesTheOnesBelow(string $edition, array $expected): void
    {
        $features = Features::forEdition($edition);

        $this->assertSame($edition, $features->edition);
        $this->assertSame($expected, $features->toArray());
    }

    public function testWithoutLicenceOnlyTheChat(): void
    {
        $resolver = new FeatureResolver(Licences::service(), Licences::terms());

        $this->assertSame('', $resolver->for('www.example.com')->edition);
        $this->assertSame('', $resolver->licensed('')->edition);
        $this->assertTrue($resolver->canPublish(), 'the terms for all editions are confirmed');
        $this->assertNull($resolver->pendingPassage());
    }

    public function testTheLicenceCountsOnlyAsFarAsItsTermsAreConfirmed(): void
    {
        $licences = Licences::service('plus');

        $this->assertSame('plus', (new FeatureResolver($licences, Licences::terms('pro')))->for('www.example.com')->edition);
        $this->assertSame('plus', (new FeatureResolver($licences, Licences::terms('plus')))->for('www.example.com')->edition);
        $this->assertSame('basic', (new FeatureResolver($licences, Licences::terms('basic')))->for('www.example.com')->edition);
        $this->assertSame('', (new FeatureResolver($licences, Licences::terms('all')))->for('www.example.com')->edition, 'the first passage unlocks no paid edition');
        $this->assertSame('', (new FeatureResolver($licences, Licences::terms('')))->for('www.example.com')->edition);
    }

    public function testTheBackEndSeesTheLicenceBeforeItsTerms(): void
    {
        $resolver = new FeatureResolver(Licences::service('pro'), Licences::terms('basic'));

        $this->assertSame('pro', $resolver->licensed('')->edition);
        $this->assertSame('basic', $resolver->for('')->edition);
        $this->assertSame('plus', $resolver->pendingPassage());
    }

    public function testNothingIsPublishedBeforeTheFirstPassageIsConfirmed(): void
    {
        $resolver = new FeatureResolver(Licences::service(), Licences::terms(''));

        $this->assertFalse($resolver->canPublish());
        $this->assertSame('all', $resolver->pendingPassage());
        $this->assertTrue((new FeatureResolver(Licences::service('basic'), Licences::terms('all')))->canPublish());
    }

    public function testThePendingPassageFollowsTheLicensedEdition(): void
    {
        $this->assertSame('basic', (new FeatureResolver(Licences::service('basic'), Licences::terms('all')))->pendingPassage());
        $this->assertNull((new FeatureResolver(Licences::service('basic'), Licences::terms('basic')))->pendingPassage());
        $this->assertSame('pro', (new FeatureResolver(Licences::service('pro'), Licences::terms('plus')))->pendingPassage());
    }
}

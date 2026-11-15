<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Backend;

use Contao\DataContainer;
use Neo\SuperAgentBundle\Backend\PublishCallbacks;
use Neo\SuperAgentBundle\Licence\FeatureResolver;
use Neo\SuperAgentBundle\Security\PreviewAccess;
use Neo\SuperAgentBundle\Tests\Support\Licences;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/** "All visitors" only once the terms for all editions are confirmed. */
#[CoversClass(PublishCallbacks::class)]
final class PublishCallbacksTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['TL_LANG']['tl_neo_agent'] = ['visibility_locked' => 'Confirm the terms first', 'preview_badge' => 'Preview <only>'];
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TL_LANG']['tl_neo_agent']);
    }

    public function testPublishingNeedsTheTerms(): void
    {
        $unconfirmed = new PublishCallbacks(new FeatureResolver(Licences::service(), Licences::terms('')));

        $this->assertSame(PreviewAccess::PREVIEW, $unconfirmed->checkVisibility(PreviewAccess::PREVIEW, $this->dc('preview')));
        $this->assertSame('public', $unconfirmed->checkVisibility('public', $this->dc('public')), 'visible already: stays so');

        $this->expectExceptionObject(new \RuntimeException('Confirm the terms first'));
        $unconfirmed->checkVisibility('public', $this->dc('preview'));
    }

    public function testWithConfirmedTermsAnyChoice(): void
    {
        $confirmed = new PublishCallbacks(new FeatureResolver(Licences::service(), Licences::terms('all')));

        $this->assertSame('public', $confirmed->checkVisibility('public', $this->dc('preview')));
        $this->assertSame('', $confirmed->note($this->dc('preview')));
    }

    public function testPreviewAssistantsAreMarkedInTheList(): void
    {
        $callbacks = new PublishCallbacks(new FeatureResolver(Licences::service(), Licences::terms()));

        $this->assertSame('Bot <span class="label-info" style="margin-left:6px">Preview &lt;only&gt;</span>', $callbacks->label(['visibility' => 'preview'], 'Bot'));
        $this->assertSame('Bot', $callbacks->label(['visibility' => 'public'], 'Bot'));
    }

    private function dc(string $visibility): DataContainer
    {
        $dc = $this->createMock(DataContainer::class);
        $dc->method('getCurrentRecord')->willReturn(['visibility' => $visibility]);

        return $dc;
    }
}

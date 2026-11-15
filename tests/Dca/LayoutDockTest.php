<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Dca;

use Neo\BasicBundle\Dock\DockPalette;
use PHPUnit\Framework\TestCase;

/**
 * The chat button takes part in the buttons at the edge of the screen of
 * NEO Basic, so the page layout shows their arrangement; a Basic without them
 * leaves the page layout as it is.
 */
final class LayoutDockTest extends TestCase
{
    private const PALETTE = '{title_legend},name,type;{modules_legend},modules;{script_legend},scripts';

    protected function tearDown(): void
    {
        unset($GLOBALS['TL_DCA']);
    }

    public function testThePageLayoutShowsTheArrangementOfTheButtons(): void
    {
        $GLOBALS['TL_DCA']['tl_layout']['palettes'] = ['__selector__' => ['type'], 'default' => self::PALETTE];

        include \dirname(__DIR__, 2).'/contao/dca/tl_layout.php';

        if (!class_exists(DockPalette::class)) {
            $this->assertSame(self::PALETTE, $GLOBALS['TL_DCA']['tl_layout']['palettes']['default']);

            return;
        }

        $this->assertSame('{title_legend},name,type;{modules_legend},modules;{neo_dock_legend},neo_dock;{script_legend},scripts', $GLOBALS['TL_DCA']['tl_layout']['palettes']['default']);
    }
}

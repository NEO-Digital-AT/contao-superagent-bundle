<?php

declare(strict_types=1);

use Neo\BasicBundle\Dock\DockPalette;

// The chat button takes part in the buttons at the edge of the screen of NEO
// Basic: their arrangement is a setting of the page layout. A Basic without
// them leaves the page layout as it is.
if (class_exists(DockPalette::class)) {
    DockPalette::apply();
}

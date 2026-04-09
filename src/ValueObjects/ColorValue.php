<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\ValueObjects;

use InvalidArgumentException;

final readonly class ColorValue
{
    /**
     * Create a new ColorValue instance.
     *
     * Expects RGB/RGBA color model with:
     * - c1, c2, c3: RGB components (0-255)
     * - c4: Alpha channel (0-100, optional)
     * or CMYK color model with:
     * - c1, c2, c3, c4: CMYK components (0-100)
     * or Grayscale color model with:
     * - c1: Grayscale component (0-100)
     * - c2, c3, c4: Ignored
     */
    public function __construct(
        public int $c1,
        public int $c2,
        public int $c3,
        public ?int $c4 = null,
    ) {
        if ($c1 < 0 || $c1 > 255) {
            throw new InvalidArgumentException('RGB values must be between 0 and 255, got '.$c1);
        }

        if ($c2 < 0 || $c2 > 255) {
            throw new InvalidArgumentException('RGB values must be between 0 and 255, got '.$c2);
        }

        if ($c3 < 0 || $c3 > 255) {
            throw new InvalidArgumentException('RGB values must be between 0 and 255, got '.$c3);
        }

        if ($c4 !== null && ($c4 < 0 || $c4 > 100)) {
            throw new InvalidArgumentException('Alpha values must be between 0 and 100, got '.$c4);
        }
    }
}

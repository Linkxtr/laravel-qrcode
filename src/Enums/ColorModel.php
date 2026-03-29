<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Enums;

use Linkxtr\QrCode\Enums\Concerns\EnumHelper;

/**
 * Represents different color models supported for QR code generation.
 */
enum ColorModel: string
{
    use EnumHelper;

    /**
     * Red, Green, Blue
     */
    case RGB = 'rgb';

    /**
     * Cyan, Magenta, Yellow, Black
     */
    case CMYK = 'cmyk';

    /**
     * Grayscale
     */
    case GRAY = 'gray';
}

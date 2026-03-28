<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Enums;

use Linkxtr\QrCode\Enums\Concerns\EnumHelper;

enum ColorModel: string
{
    use EnumHelper;

    case RGB = 'rgb';
    case CMYK = 'cmyk';
    case GRAY = 'gray';
}

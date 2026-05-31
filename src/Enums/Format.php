<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Enums;

use Linkxtr\QrCode\Enums\Concerns\EnumHelper;

enum Format: string
{
    use EnumHelper;

    case PNG = 'png'; // requires imagick or gd
    case SVG = 'svg'; // no requires
    case EPS = 'eps'; // no requires
    case WEBP = 'webp'; // requires imagick
}

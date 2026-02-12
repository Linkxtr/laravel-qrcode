<?php

namespace Linkxtr\QrCode\Enums;

use Linkxtr\QrCode\Enums\Concerns\EnumHelper;

enum Format: string
{
    use EnumHelper;
    case PNG = 'png';
    case SVG = 'svg';
    case EPS = 'eps';
    case WEBP = 'webp';
}

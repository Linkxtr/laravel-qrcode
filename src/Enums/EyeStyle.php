<?php

namespace Linkxtr\QrCode\Enums;

use Linkxtr\QrCode\Enums\Concerns\EnumHelper;

enum EyeStyle: string
{
    use EnumHelper;
    case SQUARE = 'square';
    case CIRCLE = 'circle';
}

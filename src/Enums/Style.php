<?php

namespace Linkxtr\QrCode\Enums;

use Linkxtr\QrCode\Enums\Concerns\EnumHelper;

enum Style: string
{
    use EnumHelper;
    case SQUARE = 'square';
    case DOT = 'dot';
    case ROUND = 'round';
}

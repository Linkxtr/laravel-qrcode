<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Enums;

use BaconQrCode\Common\ErrorCorrectionLevel as BaconErrorCorrectionLevel;
use Linkxtr\QrCode\Enums\Concerns\EnumHelper;

enum ErrorCorrectionLevel: string
{
    use EnumHelper;

    case L = 'L'; // 7% loss.
    case M = 'M'; // 15% loss.
    case Q = 'Q'; // 25% loss.
    case H = 'H'; // 30% loss.

    public function toBaconErrorCorrectionLevel(): BaconErrorCorrectionLevel
    {
        return match ($this) {
            self::L => BaconErrorCorrectionLevel::L(),
            self::M => BaconErrorCorrectionLevel::M(),
            self::Q => BaconErrorCorrectionLevel::Q(),
            self::H => BaconErrorCorrectionLevel::H(),
        };
    }
}

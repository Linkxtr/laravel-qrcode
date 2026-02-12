<?php

namespace Linkxtr\QrCode\Enums;

use BaconQrCode\Common\ErrorCorrectionLevel as BaconErrorCorrectionLevel;
use Linkxtr\QrCode\Enums\Concerns\EnumHelper;

enum ErrorCorrectionLevel: string
{
    use EnumHelper;

    case L = 'L';
    case M = 'M';
    case Q = 'Q';
    case H = 'H';

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

<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Enums;

use BaconQrCode\Renderer\RendererStyle\GradientType as BaconGradientType;
use Linkxtr\QrCode\Enums\Concerns\EnumHelper;

enum GradientType: string
{
    use EnumHelper;
    case VERTICAL = 'vertical';
    case HORIZONTAL = 'horizontal';
    case DIAGONAL = 'diagonal';
    case INVERSE_DIAGONAL = 'inverse_diagonal';
    case RADIAL = 'radial';

    public function toBaconGradientType(): BaconGradientType
    {
        return match ($this) {
            self::VERTICAL => BaconGradientType::VERTICAL(),
            self::HORIZONTAL => BaconGradientType::HORIZONTAL(),
            self::DIAGONAL => BaconGradientType::DIAGONAL(),
            self::INVERSE_DIAGONAL => BaconGradientType::INVERSE_DIAGONAL(),
            self::RADIAL => BaconGradientType::RADIAL(),
        };
    }
}

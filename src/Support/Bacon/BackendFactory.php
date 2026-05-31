<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Support\Bacon;

use BaconQrCode\Renderer\Image\EpsImageBackEnd;
use BaconQrCode\Renderer\Image\ImageBackEndInterface;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use Linkxtr\QrCode\Enums\Format;

final class BackendFactory
{
    public static function make(Format $format): ImageBackEndInterface
    {
        return match ($format) {
            Format::PNG => new ImagickImageBackEnd('png'),
            Format::WEBP => new ImagickImageBackEnd('webp'),
            Format::EPS => new EpsImageBackEnd,
            Format::SVG => new SvgImageBackEnd,
        };
    }
}

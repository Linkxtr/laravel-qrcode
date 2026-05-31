<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Contracts;

use BaconQrCode\Renderer\Color\ColorInterface as BaconColorInterface;
use Linkxtr\QrCode\ValueObjects\Colors\Cmyk;
use Linkxtr\QrCode\ValueObjects\Colors\Gray;
use Linkxtr\QrCode\ValueObjects\Colors\Rgb;

interface ColorInterface
{
    /**
     * Return the alpha channel using BaconQrCode's 0-100 convention.
     */
    public function getAlpha(): int;

    public function toBaconColor(): BaconColorInterface;

    public function toRgb(): Rgb;

    public function toCmyk(): Cmyk;

    public function toGray(): Gray;
}

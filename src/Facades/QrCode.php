<?php

namespace Linkxtr\QrCode\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Linkxtr\QrCode\QrCode size(int $size)
 * @method static \Linkxtr\QrCode\QrCode margin(int $margin)
 * @method static \Linkxtr\QrCode\QrCode color(int $red, int $green, int $blue, int $alpha = 0)
 * @method static \Linkxtr\QrCode\QrCode backgroundColor(int $red, int $green, int $blue, int $alpha = 0)
 * @method static \Linkxtr\QrCode\QrCode errorCorrection(string $level)
 * @method static \Linkxtr\QrCode\QrCode encoding(string $encoding)
 * @method static \Linkxtr\QrCode\QrCode format(string $format)
 * @method static \Linkxtr\QrCode\QrCode merge(string $path, float $percentage = 0.2, bool $absolute = false)
 * @method static string generate(string $text, ?string $filename = null)
 *
 * @see \Linkxtr\QrCode\QrCode
 */
class QrCode extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'qrcode';
    }
}

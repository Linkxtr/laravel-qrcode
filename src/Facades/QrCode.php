<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Facades;

use Illuminate\Support\Facades\Facade;
use Linkxtr\QrCode\Generator;

/**
 * @method static \Linkxtr\QrCode\Generator size(int $size)
 * @method static \Linkxtr\QrCode\Generator margin(int $margin)
 * @method static \Linkxtr\QrCode\Generator color(int $red, int $green, int $blue, ?int $alpha = null)
 * @method static \Linkxtr\QrCode\Generator backgroundColor(int $red, int $green, int $blue, ?int $alpha = null)
 * @method static \Linkxtr\QrCode\Generator errorCorrection(string $level)
 * @method static \Linkxtr\QrCode\Generator encoding(string $encoding)
 * @method static \Linkxtr\QrCode\Generator format(string $format)
 * @method static \Linkxtr\QrCode\Generator merge(string $path, float $percentage = 0.2, bool $absolute = false)
 * @method static \Illuminate\Support\HtmlString|void generate(string $text, ?string $filename = null)
 *
 * @see \Linkxtr\QrCode\Generator
 */
class QrCode extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor()
    {
        return Generator::class;
    }
}

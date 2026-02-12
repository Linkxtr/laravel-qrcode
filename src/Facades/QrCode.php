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
 * @method static \Linkxtr\QrCode\Generator eyeColor(int $eyeNumber, int $innerRed, int $innerGreen, int $innerBlue, int $outterRed = 0, int $outterGreen = 0, int $outterBlue = 0)
 * @method static \Linkxtr\QrCode\Generator gradient(int $startRed, int $startGreen, int $startBlue, int $endRed, int $endGreen, int $endBlue, string $type)
 * @method static \Linkxtr\QrCode\Generator eye(string $style)
 * @method static \Linkxtr\QrCode\Generator style(string $style, float $size = 0.5)
 * @method static \Linkxtr\QrCode\Generator errorCorrection(string $errorCorrection)
 * @method static \Linkxtr\QrCode\Generator encoding(string $encoding)
 * @method static \Linkxtr\QrCode\Generator format(string $format)
 * @method static \Linkxtr\QrCode\Generator merge(string $filePath, float $percentage = 0.2, bool $absolute = false)
 * @method static \Linkxtr\QrCode\Generator mergeString(string $content, float $percentage = 0.2)
 * @method static \Illuminate\Support\HtmlString generate(string $text, ?string $filename = null)
 *
 * @see Generator
 */
final class QrCode extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor()
    {
        return Generator::class;
    }
}

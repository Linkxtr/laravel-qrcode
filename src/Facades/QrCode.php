<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\HtmlString;
use Linkxtr\QrCode\Enums\ErrorCorrectionLevel;
use Linkxtr\QrCode\Enums\EyeStyle;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Enums\GradientType;
use Linkxtr\QrCode\Enums\Style;
use Linkxtr\QrCode\Generator;

/**
 * @method static Generator size(int $size)
 * @method static Generator margin(int $margin)
 * @method static Generator color(int $red, int $green, int $blue, ?int $alpha = null)
 * @method static Generator backgroundColor(int $red, int $green, int $blue, ?int $alpha = null)
 * @method static Generator eyeColor(int $eyeNumber, int $innerRed, int $innerGreen, int $innerBlue, int $outerRed = 0, int $outerGreen = 0, int $outerBlue = 0)
 * @method static Generator gradient(int $startRed, int $startGreen, int $startBlue, int $endRed, int $endGreen, int $endBlue, string|GradientType $type)
 * @method static Generator eye(string|EyeStyle $style)
 * @method static Generator style(string|Style $style, float $size = 0.5)
 * @method static Generator errorCorrection(string|ErrorCorrectionLevel $errorCorrection)
 * @method static Generator encoding(string $encoding)
 * @method static Generator format(string|Format $format)
 * @method static Generator merge(string $filePath, float $percentage = 0.2, bool $absolute = false)
 * @method static Generator mergeString(string $content, float $percentage = 0.2)
 * @method static HtmlString generate(string $text, ?string $filename = null)
 *
 * @see Generator
 */
final class QrCode extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return Generator::class;
    }
}

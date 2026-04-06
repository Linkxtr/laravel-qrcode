<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DTOs;

use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\ColorInterface;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\RendererStyle\EyeFill;
use BaconQrCode\Renderer\RendererStyle\Gradient;
use InvalidArgumentException;
use Linkxtr\QrCode\Enums\ColorModel;
use Linkxtr\QrCode\Enums\ErrorCorrectionLevel;
use Linkxtr\QrCode\Enums\EyeStyle;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Enums\GradientType;
use Linkxtr\QrCode\Enums\Style;
use Linkxtr\QrCode\ValueObjects\ColorValue;

final class Config
{
    /**
     * The output format.
     * See `Format` enum for possible values.
     */
    private Format $format = Format::SVG;

    /**
     * The error correction level.
     * See `ErrorCorrectionLevel` enum for possible values.
     */
    private ErrorCorrectionLevel $errorCorrectionLevel = ErrorCorrectionLevel::M;

    /**
     * The style of the blocks within the QR code.
     * See `Style` enum for possible values.
     */
    private Style $style = Style::SQUARE;

    /**
     * The size of the selected style between 0 and 1.
     * Only applicable to 'dot' and 'round' styles.
     */
    private float $styleSize = 0.5;

    /**
     * The style to apply to the eyes of the QR code.
     * See `EyeStyle` enum for possible values.
     */
    private ?EyeStyle $eyeStyle = null;

    /**
     * The internal style to apply to the eyes of the QR code.
     * Only applies when a composite eye is desired.
     */
    private ?EyeStyle $internalEyeStyle = null;

    /**
     * The size of the QR code in pixels.
     */
    private int $size = 400;

    /**
     * The margin around the QR code.
     */
    private int $margin = 4;

    /**
     * The encoding mode. Possible values are
     * ISO-8859-2, ISO-8859-3, ISO-8859-4, ISO-8859-5, ISO-8859-6,
     * ISO-8859-7, ISO-8859-8, ISO-8859-9, ISO-8859-10, ISO-8859-11,
     * ISO-8859-12, ISO-8859-13, ISO-8859-14, ISO-8859-15, ISO-8859-16,
     * SHIFT-JIS, WINDOWS-1250, WINDOWS-1251, WINDOWS-1252, WINDOWS-1256,
     * UTF-16BE, UTF-8, ASCII, GBK, EUC-KR.
     */
    private string $encoding = 'UTF-8';

    /**
     * The color model used to build colors.
     */
    private ColorModel $colorModel = ColorModel::RGB;

    /**
     * The foreground color value of the QR code.
     */
    private ?ColorValue $colorValue = null;

    /**
     * The background color value of the QR code.
     */
    private ?ColorValue $backgroundColorValue = null;

    /**
     * An array that holds EyeFills of the color of the eyes.
     *
     * @var array<int, EyeFill>
     */
    private array $eyeColors = [];

    /**
     * The gradient to apply to the QR code.
     */
    private ?Gradient $gradient = null;

    /**
     * Holds an image string that will be merged with the QR code.
     */
    private string $imageMerge = '';

    /**
     * The percentage that a merged image should take over the source image.
     */
    private float $imagePercentage = .2;

    /**
     * @param  array<mixed>  $config
     */
    public function __construct(array $config = [])
    {
        if (isset($config['size']) && is_int($config['size'])) {
            $this->size = $config['size'] > 0 ? $config['size'] : $this->size;
        }

        if (isset($config['margin']) && is_int($config['margin'])) {
            $this->margin = $config['margin'] >= 0 ? $config['margin'] : $this->margin;
        }

        if (isset($config['format']) && \is_string($config['format'])) {
            $format = Format::tryFrom(strtolower($config['format']));
            if ($format !== null) {
                $this->format = $format;
            }
        }

        if (isset($config['error_correction']) && \is_string($config['error_correction'])) {
            $level = ErrorCorrectionLevel::tryFrom(strtoupper($config['error_correction']));
            if ($level !== null) {
                $this->errorCorrectionLevel = $level;
            }
        }

        if (isset($config['encoding']) && \is_string($config['encoding'])) {
            $this->encoding = strtoupper($config['encoding']);
        }

        if (isset($config['color']) && is_array($config['color'])) {
            $this->colorValue = new ColorValue(...$this->readRgb($config['color'], 0));
        }

        if (isset($config['background_color']) && is_array($config['background_color'])) {
            $this->backgroundColorValue = new ColorValue(...$this->readRgb($config['background_color'], 255));
        }
    }

    public function setFormat(string|Format $format): void
    {
        if (is_string($format)) {
            $format = Format::tryFrom($format);
        }

        if (! $format) {
            throw new InvalidArgumentException('$format must be one of the following values: '.implode(', ', Format::toArray()));
        }

        $this->format = $format;
    }

    public function getFormat(): Format
    {
        return $this->format;
    }

    public function setErrorCorrectionLevel(string|ErrorCorrectionLevel $level): void
    {
        if (is_string($level)) {
            $level = ErrorCorrectionLevel::tryFrom(strtoupper($level));
        }

        if (! $level) {
            throw new InvalidArgumentException('$level must be one of the following values: '.implode(', ', ErrorCorrectionLevel::toArray()));
        }

        $this->errorCorrectionLevel = $level;
    }

    public function getErrorCorrectionLevel(): ErrorCorrectionLevel
    {
        return $this->errorCorrectionLevel;
    }

    public function setupStyle(string|Style $style, float $size): void
    {
        $this->setStyle($style);
        $this->setStyleSize($size);
    }

    public function getStyle(): Style
    {
        return $this->style;
    }

    public function getStyleSize(): float
    {
        return $this->styleSize;
    }

    public function setEyeStyle(string|EyeStyle $style): void
    {
        if (is_string($style)) {
            $style = EyeStyle::tryFrom($style);
        }

        if (! $style) {
            throw new InvalidArgumentException('$style must be one of the following values: '.implode(', ', EyeStyle::toArray()));
        }

        $this->eyeStyle = $style;
    }

    public function getEyeStyle(): ?EyeStyle
    {
        return $this->eyeStyle;
    }

    public function setInternalEyeStyle(string|EyeStyle $style): void
    {
        if (is_string($style)) {
            $style = EyeStyle::tryFrom($style);
        }

        if (! $style) {
            throw new InvalidArgumentException('$style must be one of the following values: '.implode(', ', EyeStyle::toArray()));
        }

        $this->internalEyeStyle = $style;
    }

    public function getInternalEyeStyle(): ?EyeStyle
    {
        return $this->internalEyeStyle;
    }

    public function setSize(int $size): void
    {
        if ($size <= 0) {
            throw new InvalidArgumentException(sprintf('Size must be greater than 0. %s given.', $size));
        }

        $this->size = $size;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setMargin(int $margin): void
    {
        if ($margin < 0) {
            throw new InvalidArgumentException(sprintf('Margin cannot be negative. %s given.', $margin));
        }

        $this->margin = $margin;
    }

    public function getMargin(): int
    {
        return $this->margin;
    }

    public function setEncoding(string $encoding): void
    {
        $this->encoding = strtoupper($encoding);
    }

    public function getEncoding(): string
    {
        return $this->encoding;
    }

    public function setColorModel(ColorModel $colorModel): void
    {
        $this->colorModel = $colorModel;
    }

    public function getColorModel(): ColorModel
    {
        return $this->colorModel;
    }

    public function setGrayscale(int $gray, ?int $backgroundGray = null): void
    {
        if ($gray < 0 || $gray > 100) {
            throw new InvalidArgumentException('Gray value must be between 0 and 100.');
        }

        if ($backgroundGray !== null && ($backgroundGray < 0 || $backgroundGray > 100)) {
            throw new InvalidArgumentException('Background gray value must be between 0 and 100.');
        }

        $this->setColorModel(ColorModel::GRAY);
        $this->setColorValue(new ColorValue($gray, 0, 0));
        $this->setBackgroundColorValue(new ColorValue($backgroundGray ?? 100, 0, 0));
    }

    public function setupColor(int $c1, int $c2, int $c3, ?int $c4 = null): void
    {
        $this->setColorValue(new ColorValue($c1, $c2, $c3, $c4));
    }

    public function setupBackgroundColor(int $c1, int $c2, int $c3, ?int $c4 = null): void
    {
        $this->setBackgroundColorValue(new ColorValue($c1, $c2, $c3, $c4));
    }

    public function getColorValue(): ?ColorValue
    {
        return $this->colorValue;
    }

    public function getBackgroundColorValue(): ?ColorValue
    {
        return $this->backgroundColorValue;
    }

    public function setupEyeColor(int $eyeNumber, int $innerRed, int $innerGreen, int $innerBlue, int $outerRed = 0, int $outerGreen = 0, int $outerBlue = 0): void
    {
        if ($eyeNumber < 0 || $eyeNumber > 2) {
            throw new InvalidArgumentException('Eye number must be 0, 1, or 2.');
        }

        $this->validateRgb($innerRed, $innerGreen, $innerBlue, $outerRed, $outerGreen, $outerBlue);

        $this->setEyeColor($eyeNumber, new EyeFill(
            $this->createColor($innerRed, $innerGreen, $innerBlue),
            $this->createColor($outerRed, $outerGreen, $outerBlue)
        ));
    }

    /**
     * @return array<int, EyeFill>
     */
    public function getEyeColors(): array
    {
        return $this->eyeColors;
    }

    public function setupGradient(int $startRed, int $startGreen, int $startBlue, int $endRed, int $endGreen, int $endBlue, string|GradientType $type): void
    {
        if (is_string($type)) {
            $type = GradientType::tryFrom(strtolower($type));
        }

        if (! $type instanceof GradientType) {
            throw new InvalidArgumentException(
                '$type must be one of the following values: '.implode(', ', GradientType::toArray())
            );
        }

        $this->validateRgb($startRed, $startGreen, $startBlue);
        $this->validateRgb($endRed, $endGreen, $endBlue);
        $this->setGradient(new Gradient(
            $this->createColor($startRed, $startGreen, $startBlue),
            $this->createColor($endRed, $endGreen, $endBlue),
            $type->toBaconGradientType()
        ));
    }

    public function getGradient(): ?Gradient
    {
        return $this->gradient;
    }

    public function setupMergePath(string $filepath, float $percentage, bool $absolute = false): void
    {
        if (function_exists('base_path') && ! $absolute) {
            $filepath = base_path().DIRECTORY_SEPARATOR.ltrim($filepath, '\\/');
        }

        $content = @file_get_contents($filepath);

        if ($content === false) {
            throw new InvalidArgumentException('Failed to read image file: '.$filepath);
        }

        $this->setupMergeString($content, $percentage);
    }

    public function setupMergeString(string $content, float $percentage): void
    {
        if ($percentage <= 0 || $percentage >= 1) {
            throw new InvalidArgumentException('Image merge percentage must be between 0 and 1 (exclusive).');
        }

        $this->imageMerge = $content;
        $this->imagePercentage = $percentage;
    }

    public function getImageMerge(): string
    {
        return $this->imageMerge;
    }

    public function getImagePercentage(): float
    {
        return $this->imagePercentage;
    }

    /**
     * Create a BaconQrCode color object.
     *
     * @param  int  $red  Red component (0-255)
     * @param  int  $green  Green component (0-255)
     * @param  int  $blue  Blue component (0-255)
     * @param  int|null  $alpha  Alpha/opacity (0-100, not 0-255)
     */
    public function createColor(int $red, int $green, int $blue, ?int $alpha = null): ColorInterface
    {
        if (is_null($alpha)) {
            return new Rgb($red, $green, $blue);
        }

        return new Alpha($alpha, new Rgb($red, $green, $blue));
    }

    private function setStyleSize(float $size): void
    {
        if ($size <= 0 || $size > 1) {
            throw new InvalidArgumentException(sprintf('Style size must be between 0 and 1. %s given.', $size));
        }

        $this->styleSize = $size;
    }

    private function setEyeColor(int $eyeNumber, EyeFill $eyeFill): void
    {
        $this->eyeColors[$eyeNumber] = $eyeFill;
    }

    private function setStyle(string|Style $style): void
    {
        if (is_string($style)) {
            $style = Style::tryFrom($style);
        }

        if (! $style) {
            throw new InvalidArgumentException('$style must be one of the following values: '.implode(', ', Style::toArray()));
        }

        $this->style = $style;
    }

    private function setColorValue(ColorValue $colorValue): void
    {
        $this->colorValue = $colorValue;
    }

    private function setBackgroundColorValue(ColorValue $colorValue): void
    {
        $this->backgroundColorValue = $colorValue;
    }

    private function setGradient(Gradient $gradient): void
    {
        $this->gradient = $gradient;
    }

    /**
     * Read a RGB colour from a config array that may use either a positional index
     * or a named key.  Returns $default when the value is absent or not an int.
     *
     * @param  array<mixed>  $raw
     * @return array{int, int, int, int|null}
     */
    private function readRgb(array $raw, int $default): array
    {
        $red = $raw['r'] ?? $raw[0] ?? $default;
        $green = $raw['g'] ?? $raw[1] ?? $default;
        $blue = $raw['b'] ?? $raw[2] ?? $default;
        $alpha = $raw['a'] ?? $raw[3] ?? null;

        if (! is_int($red)) {
            $red = $default;
        }

        if (! is_int($green)) {
            $green = $default;
        }

        if (! is_int($blue)) {
            $blue = $default;
        }

        if ($alpha !== null && ! is_int($alpha)) {
            $alpha = null;
        }

        return [$red, $green, $blue, $alpha];
    }

    private function validateRgb(int ...$colors): void
    {
        foreach ($colors as $color) {
            if ($color < 0 || $color > 255) {
                throw new InvalidArgumentException('RGB values must be between 0 and 255.');
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DTOs;

use BaconQrCode\Renderer\Color\Rgb as BaconRgb;
use BaconQrCode\Renderer\RendererStyle\EyeFill;
use BaconQrCode\Renderer\RendererStyle\Gradient;
use BaconQrCode\Renderer\RendererStyle\GradientType as BaconGradientType;
use DateInterval;
use DateTimeInterface;
use Illuminate\Support\Facades\File;
use Linkxtr\QrCode\Contracts\ColorInterface;
use Linkxtr\QrCode\Enums\ColorModel;
use Linkxtr\QrCode\Enums\ErrorCorrectionLevel;
use Linkxtr\QrCode\Enums\EyeStyle;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Enums\GradientType;
use Linkxtr\QrCode\Enums\Style;
use Linkxtr\QrCode\Exceptions\InvalidConfigurationException;
use Linkxtr\QrCode\Support\Environment;
use Linkxtr\QrCode\ValueObjects\Colors\Cmyk;
use Linkxtr\QrCode\ValueObjects\Colors\Gray;
use Linkxtr\QrCode\ValueObjects\Colors\Rgb;

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
    private ?ColorInterface $colorValue = null;

    /**
     * The background color value of the QR code.
     */
    private ?ColorInterface $backgroundColorValue = null;

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
     * Whether the cache is enabled.
     */
    private bool $cacheEnabled = false;

    /**
     * The cache time to live.
     */
    private DateTimeInterface|DateInterval|int|null $cacheTtl = null;

    /**
     * The cache store to use.
     */
    private ?string $cacheStore = null;

    /**
     * @return array{
     *     format: Format,
     *     errorCorrectionLevel: ErrorCorrectionLevel,
     *     style: Style,
     *     styleSize: float,
     *     eyeStyle: EyeStyle|null,
     *     internalEyeStyle: EyeStyle|null,
     *     size: int,
     *     margin: int,
     *     encoding: string,
     *     colorModel: ColorModel,
     *     colorValue: ColorInterface|null,
     *     backgroundColorValue: ColorInterface|null,
     *     eyeColors: array<int, EyeFill>,
     *     gradient: array{start: array{int, int, int}, end: array{int, int, int}, type: string}|null,
     *     imageMerge: string,
     *     imagePercentage: float
     * }
     */
    public function __serialize(): array
    {
        return [
            'format' => $this->format,
            'errorCorrectionLevel' => $this->errorCorrectionLevel,
            'style' => $this->style,
            'styleSize' => $this->styleSize,
            'eyeStyle' => $this->eyeStyle,
            'internalEyeStyle' => $this->internalEyeStyle,
            'size' => $this->size,
            'margin' => $this->margin,
            'encoding' => $this->encoding,
            'colorModel' => $this->colorModel,
            'colorValue' => $this->colorValue,
            'backgroundColorValue' => $this->backgroundColorValue,
            'eyeColors' => $this->eyeColors,
            'gradient' => $this->gradient instanceof Gradient ? [
                'start' => [
                    $this->gradient->getStartColor()->toRgb()->getRed(),
                    $this->gradient->getStartColor()->toRgb()->getGreen(),
                    $this->gradient->getStartColor()->toRgb()->getBlue(),
                ],
                'end' => [
                    $this->gradient->getEndColor()->toRgb()->getRed(),
                    $this->gradient->getEndColor()->toRgb()->getGreen(),
                    $this->gradient->getEndColor()->toRgb()->getBlue(),
                ],
                'type' => $this->gradient->getType()->name(),
            ] : null,
            'imageMerge' => $this->imageMerge,
            'imagePercentage' => $this->imagePercentage,
        ];
    }

    /**
     * @param  array{
     *     format: Format,
     *     errorCorrectionLevel: ErrorCorrectionLevel,
     *     style: Style,
     *     styleSize: float,
     *     eyeStyle: EyeStyle|null,
     *     internalEyeStyle: EyeStyle|null,
     *     size: int,
     *     margin: int,
     *     encoding: string,
     *     colorModel: ColorModel,
     *     colorValue: ColorInterface|null,
     *     backgroundColorValue: ColorInterface|null,
     *     eyeColors: array<int, EyeFill>,
     *     gradient: array{start: array{int, int, int}, end: array{int, int, int}, type: string}|null,
     *     imageMerge: string,
     *     imagePercentage: float
     * }  $data
     */
    public function __unserialize(array $data): void
    {
        $this->format = $data['format'];
        $this->errorCorrectionLevel = $data['errorCorrectionLevel'];
        $this->style = $data['style'];
        $this->styleSize = $data['styleSize'];
        $this->eyeStyle = $data['eyeStyle'];
        $this->internalEyeStyle = $data['internalEyeStyle'];
        $this->size = $data['size'];
        $this->margin = $data['margin'];
        $this->encoding = $data['encoding'];
        $this->colorModel = $data['colorModel'];
        $this->colorValue = $data['colorValue'];
        $this->backgroundColorValue = $data['backgroundColorValue'];
        $this->eyeColors = $data['eyeColors'];
        if ($data['gradient'] !== null) {
            $start = new BaconRgb(
                $data['gradient']['start'][0],
                $data['gradient']['start'][1],
                $data['gradient']['start'][2]
            );
            $end = new BaconRgb(
                $data['gradient']['end'][0],
                $data['gradient']['end'][1],
                $data['gradient']['end'][2]
            );
            $type = BaconGradientType::valueOf($data['gradient']['type']);
            $this->gradient = new Gradient($start, $end, $type);
        } else {
            $this->gradient = null;
        }

        $this->imageMerge = $data['imageMerge'];
        $this->imagePercentage = $data['imagePercentage'];
    }

    /**
     * @param  array<mixed>  $config
     */
    public static function fromArray(array $config): self
    {
        $instance = new self;

        if (isset($config['size']) && is_int($config['size'])) {
            $instance->size = $config['size'] > 0 ? $config['size'] : $instance->getSize();
        }

        if (isset($config['margin']) && is_int($config['margin'])) {
            $instance->margin = $config['margin'] >= 0 ? $config['margin'] : $instance->getMargin();
        }

        if (isset($config['format']) && is_string($config['format'])) {
            $format = Format::tryFrom(strtolower($config['format']));
            if ($format !== null) {
                $instance->format = $format;
            }
        }

        if (isset($config['error_correction']) && is_string($config['error_correction'])) {
            $level = ErrorCorrectionLevel::tryFrom(strtoupper($config['error_correction']));
            if ($level !== null) {
                $instance->errorCorrectionLevel = $level;
            }
        }

        if (isset($config['encoding']) && is_string($config['encoding'])) {
            $instance->encoding = strtoupper($config['encoding']);
        }

        if (isset($config['color']) && (is_string($config['color']) || is_array($config['color']))) {
            try {
                $instance->colorValue = Rgb::parse($config['color']);
            } catch (InvalidConfigurationException $exception) {
                throw new InvalidConfigurationException(
                    sprintf('Invalid qrcode.color (QR_CODE_COLOR): %s', $exception->getMessage()),
                    $exception->getCode(),
                    $exception
                );
            }
        }

        if (isset($config['background_color']) && (is_string($config['background_color']) || is_array($config['background_color']))) {
            try {
                $instance->backgroundColorValue = Rgb::parse($config['background_color']);
            } catch (InvalidConfigurationException $exception) {
                throw new InvalidConfigurationException(
                    sprintf('Invalid qrcode.background_color (QR_CODE_BACKGROUND_COLOR): %s', $exception->getMessage()),
                    $exception->getCode(),
                    $exception
                );
            }
        }

        if (isset($config['cache_enabled'])) {
            $instance->cacheEnabled = is_bool($config['cache_enabled'])
                ? $config['cache_enabled']
                : filter_var($config['cache_enabled'], FILTER_VALIDATE_BOOLEAN);
        }

        if (isset($config['cache_ttl']) && is_numeric($config['cache_ttl'])) {
            $instance->cacheTtl = (int) $config['cache_ttl'];
        }

        if (isset($config['cache_store']) && is_string($config['cache_store']) && trim($config['cache_store']) !== '') {
            $instance->cacheStore = trim($config['cache_store']);
        }

        return $instance;
    }

    public function setFormat(string|Format $format): void
    {
        $formatEnum = $format instanceof Format ? $format : Format::tryFrom(strtolower($format));

        if ($formatEnum === null) {
            throw InvalidConfigurationException::unsupportedFormat($format, Format::toArray());
        }

        $this->format = $formatEnum;
    }

    public function getFormat(): Format
    {
        return $this->format;
    }

    public function setErrorCorrectionLevel(string|ErrorCorrectionLevel $level): void
    {
        $levelEnum = $level instanceof ErrorCorrectionLevel ? $level : ErrorCorrectionLevel::tryFrom(strtoupper($level));

        if ($levelEnum === null) {
            throw InvalidConfigurationException::invalidErrorCorrectionLevel($level, ErrorCorrectionLevel::toArray());
        }

        $this->errorCorrectionLevel = $levelEnum;
    }

    public function getErrorCorrectionLevel(): ErrorCorrectionLevel
    {
        return $this->errorCorrectionLevel;
    }

    public function setupStyle(string|Style $style, ?float $size = null): void
    {
        $this->setStyle($style);

        if ($size !== null) {
            $this->setStyleSize($size);
        }
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
        $styleEnum = $style instanceof EyeStyle ? $style : EyeStyle::tryFrom($style);

        if ($styleEnum === null) {
            throw InvalidConfigurationException::invalidEyeStyle($style, EyeStyle::toArray());
        }

        $this->eyeStyle = $styleEnum;
    }

    public function getEyeStyle(): ?EyeStyle
    {
        return $this->eyeStyle;
    }

    public function setInternalEyeStyle(string|EyeStyle $style): void
    {
        $styleEnum = $style instanceof EyeStyle ? $style : EyeStyle::tryFrom($style);

        if ($styleEnum === null) {
            throw InvalidConfigurationException::invalidEyeStyle($style, EyeStyle::toArray());
        }

        $this->internalEyeStyle = $styleEnum;
    }

    public function getInternalEyeStyle(): ?EyeStyle
    {
        return $this->internalEyeStyle;
    }

    public function setSize(int $size): void
    {
        if ($size <= 0) {
            throw InvalidConfigurationException::invalidSize($size);
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
            throw InvalidConfigurationException::invalidMargin($margin);
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

        if ($this->colorValue instanceof ColorInterface) {
            $this->colorValue = match ($colorModel) {
                ColorModel::RGB => $this->colorValue->toRgb(),
                ColorModel::CMYK => $this->colorValue->toCmyk(),
                ColorModel::GRAY => $this->colorValue->toGray(),
            };
        }

        if ($this->backgroundColorValue instanceof ColorInterface) {
            $this->backgroundColorValue = match ($colorModel) {
                ColorModel::RGB => $this->backgroundColorValue->toRgb(),
                ColorModel::CMYK => $this->backgroundColorValue->toCmyk(),
                ColorModel::GRAY => $this->backgroundColorValue->toGray(),
            };
        }
    }

    public function getColorModel(): ColorModel
    {
        return $this->colorModel;
    }

    public function setGrayscale(int $gray, ?int $backgroundGray = null): void
    {
        if ($gray < 0 || $gray > 100) {
            throw InvalidConfigurationException::invalidGrayscale($gray);
        }

        if ($backgroundGray !== null && ($backgroundGray < 0 || $backgroundGray > 100)) {
            throw InvalidConfigurationException::invalidGrayscale($backgroundGray);
        }

        $this->setColorModel(ColorModel::GRAY);
        $this->setColorValue(new Gray($gray));
        $this->setBackgroundColorValue(new Gray($backgroundGray ?? 100));
    }

    public function setupColor(int $c1, int $c2, int $c3, ?int $c4 = null): void
    {
        $this->setColorValue(match ($this->colorModel) {
            ColorModel::RGB => new Rgb($c1, $c2, $c3, $c4 ?? 100),
            ColorModel::CMYK => new Cmyk($c1, $c2, $c3, $c4 ?? 100),
            ColorModel::GRAY => new Gray($c1, $c4 ?? 100),
        });
    }

    public function setupBackgroundColor(int $c1, int $c2, int $c3, ?int $c4 = null): void
    {
        $this->setBackgroundColorValue(match ($this->colorModel) {
            ColorModel::RGB => new Rgb($c1, $c2, $c3, $c4 ?? 100),
            ColorModel::CMYK => new Cmyk($c1, $c2, $c3, $c4 ?? 100),
            ColorModel::GRAY => new Gray($c1, $c4 ?? 100),
        });
    }

    public function getColorValue(): ColorInterface
    {
        return $this->colorValue ?? new Rgb(0, 0, 0);
    }

    public function getBackgroundColorValue(): ColorInterface
    {
        return $this->backgroundColorValue ?? new Rgb(255, 255, 255);
    }

    public function setupEyeColor(int $eyeNumber, Rgb $inner, ?Rgb $outer = null): void
    {
        if ($eyeNumber < 0 || $eyeNumber > 2) {
            throw InvalidConfigurationException::invalidEyeNumber($eyeNumber);
        }

        $this->setEyeColor($eyeNumber, new EyeFill(
            $inner->toBaconColor(),
            $outer?->toBaconColor() ?? null
        ));
    }

    /**
     * @return array<int, EyeFill>
     */
    public function getEyeColors(): array
    {
        return $this->eyeColors;
    }

    public function setupGradient(Rgb $start, Rgb $end, string|GradientType $type = GradientType::VERTICAL): void
    {
        $typeEnum = $type instanceof GradientType ? $type : GradientType::tryFrom(strtolower($type));

        if ($typeEnum === null) {
            throw InvalidConfigurationException::invalidGradientType($type, GradientType::toArray());
        }

        $this->setGradient(new Gradient(
            $start->toBaconColor(),
            $end->toBaconColor(),
            $typeEnum->toBaconGradientType()
        ));
    }

    public function getGradient(): ?Gradient
    {
        return $this->gradient;
    }

    public function setupMergePath(string $filepath): void
    {
        $isAbsolute = preg_match('~^(/|//|\\\\\\\\|[a-zA-Z]:[\\\\/])~', $filepath) === 1;

        $filepath = $isAbsolute ? $filepath : base_path($filepath);
        $realPath = realpath($filepath);

        if ($realPath === false || ! File::isFile($realPath)) {
            throw InvalidConfigurationException::imageDoesNotExist($filepath);
        }

        if (! $isAbsolute && ! $this->isPathInsideApplication($realPath)) {
            throw InvalidConfigurationException::imagePathOutsideApplication();
        }

        if (! File::isReadable($realPath)) {
            throw InvalidConfigurationException::imageFileNotReadable($realPath);
        }

        $content = file_get_contents($realPath);

        if ($content === false) {
            throw InvalidConfigurationException::imageFileNotReadable($realPath);
        }

        $this->imageMerge = $content;
    }

    public function setupMergeString(string $content): void
    {
        $this->imageMerge = $content;
    }

    public function setImagePercentage(float $percentage): void
    {
        if ($percentage <= 0 || $percentage >= 1) {
            throw InvalidConfigurationException::invalidImageMergePercentage($percentage);
        }

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

    public function setupCache(DateTimeInterface|DateInterval|int|null $ttl = null, ?string $store = null): void
    {
        $this->cacheEnabled = true;
        $this->cacheTtl = $ttl;
        if ($store !== null) {
            $this->setCacheStore(trim($store));
        }
    }

    public function disableCache(): void
    {
        $this->cacheEnabled = false;
    }

    public function getCacheTtl(): int|DateInterval|DateTimeInterface
    {
        return $this->cacheTtl ?? 86400;
    }

    public function getCacheStore(): ?string
    {
        return $this->cacheStore;
    }

    public function shouldCache(): bool
    {
        return $this->cacheEnabled;
    }

    private function setCacheStore(string $store): void
    {
        $this->cacheStore = $store !== '' ? $store : null;
    }

    private function isPathInsideApplication(string $resolvedPath): bool
    {
        $realBase = realpath(base_path()) ?: base_path();

        $normalizedPath = str_replace('\\', '/', $resolvedPath);
        $normalizedBase = rtrim(str_replace('\\', '/', $realBase), '/').'/';

        if (Environment::isWindows()) {
            $normalizedPath = strtolower($normalizedPath);
            $normalizedBase = strtolower($normalizedBase);
        }

        return str_starts_with($normalizedPath, $normalizedBase);
    }

    private function setStyleSize(float $size): void
    {
        if ($size <= 0 || $size > 1) {
            throw InvalidConfigurationException::invalidStyleSize($size);
        }

        $this->styleSize = $size;
    }

    private function setEyeColor(int $eyeNumber, EyeFill $eyeFill): void
    {
        $this->eyeColors[$eyeNumber] = $eyeFill;
    }

    private function setStyle(string|Style $style): void
    {
        $styleEnum = $style instanceof Style ? $style : Style::tryFrom(strtolower($style));

        if ($styleEnum === null) {
            throw InvalidConfigurationException::invalidStyle($style, Style::toArray());
        }

        $this->style = $styleEnum;
    }

    private function setColorValue(ColorInterface $color): void
    {
        $this->colorValue = $color;
    }

    private function setBackgroundColorValue(ColorInterface $color): void
    {
        $this->backgroundColorValue = $color;
    }

    private function setGradient(Gradient $gradient): void
    {
        $this->gradient = $gradient;
    }
}

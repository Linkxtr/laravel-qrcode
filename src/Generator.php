<?php

declare(strict_types=1);

namespace Linkxtr\QrCode;

use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\Cmyk;
use BaconQrCode\Renderer\Color\ColorInterface;
use BaconQrCode\Renderer\Color\Gray;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Eye\CompositeEye;
use BaconQrCode\Renderer\Eye\EyeInterface;
use BaconQrCode\Renderer\Eye\ModuleEye;
use BaconQrCode\Renderer\Eye\PointyEye;
use BaconQrCode\Renderer\Eye\SimpleCircleEye;
use BaconQrCode\Renderer\Eye\SquareEye;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Renderer\Image\EpsImageBackEnd;
use BaconQrCode\Renderer\Image\ImageBackEndInterface;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Module\DotsModule;
use BaconQrCode\Renderer\Module\ModuleInterface;
use BaconQrCode\Renderer\Module\RoundnessModule;
use BaconQrCode\Renderer\Module\SquareModule;
use BaconQrCode\Renderer\RendererInterface;
use BaconQrCode\Renderer\RendererStyle\EyeFill;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\Gradient;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use BadMethodCallException;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\Enums\ColorModel;
use Linkxtr\QrCode\Enums\ErrorCorrectionLevel;
use Linkxtr\QrCode\Enums\EyeStyle;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Enums\GradientType;
use Linkxtr\QrCode\Enums\Style;
use Linkxtr\QrCode\Mergers\EpsMerger;
use Linkxtr\QrCode\Mergers\ImagickMerger;
use Linkxtr\QrCode\Mergers\RasterMerger;
use Linkxtr\QrCode\Mergers\SvgMerger;
use Linkxtr\QrCode\Support\Image;
use Linkxtr\QrCode\ValueObjects\ColorValue;
use RuntimeException;
use Stringable;
use UnexpectedValueException;

use function is_array;
use function is_int;
use function strval;

/**
 * @method HtmlString btc(string $address, float $amount, array<mixed> $options = [])
 * @method HtmlString calendarEvent(array<mixed> $attributes)
 * @method HtmlString email(string $address, string $subject = '', string $body = '', string $cc = '', string $bcc = '')
 * @method HtmlString ethereum(string $address, ?float $amount = null)
 * @method HtmlString geo(float $latitude, float $longitude, string $name = '')
 * @method HtmlString meCard(string|array<mixed> $name, ?string $phone = null, ?string $email = null, ?string $note = null, ?string $birthday = null, ?string $address = null, ?string $url = null)
 * @method HtmlString phoneNumber(string $phoneNumber)
 * @method HtmlString sms(string $smsAddress = '', string $message = '')
 * @method HtmlString telegram(string|array<mixed> $username)
 * @method HtmlString vCard(array<mixed> $properties)
 * @method HtmlString whatsApp(string|array<mixed> $number, ?string $message = null)
 * @method HtmlString wiFi(array<mixed> $credentials)
 */
final class Generator
{
    use Macroable {
        __call as macroCall;
    }

    /**
     * The PNG compression level.
     * Only applicable to PNG format when using GDLibRenderer.
     */
    private const PNG_COMPRESSION_LEVEL = 9;

    /**
     * The output format.
     * See `Format` enum for possible values.
     */
    private Format $format = Format::SVG;

    /**
     * The error correction level.
     * See `ErrorCorrectionLevel` enum for possible values.
     */
    private ErrorCorrectionLevel $errorCorrectionLevel = ErrorCorrectionLevel::L;

    /**
     * The style of the blocks within the QR code.
     * See `Style` enum for possible values.
     */
    private Style $style = Style::SQUARE;

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
     * The size of the selected style between 0 and 1.
     * Only applicable to 'dot' and 'round' styles.
     */
    private float $styleSize = 0.5;

    /**
     * The size of the QR code in pixels.
     */
    private int $size = 100;

    /**
     * The margin around the QR code.
     */
    private int $margin = 0;

    /**
     * The encoding mode. Possible values are
     * ISO-8859-2, ISO-8859-3, ISO-8859-4, ISO-8859-5, ISO-8859-6,
     * ISO-8859-7, ISO-8859-8, ISO-8859-9, ISO-8859-10, ISO-8859-11,
     * ISO-8859-12, ISO-8859-13, ISO-8859-14, ISO-8859-15, ISO-8859-16,
     * SHIFT-JIS, WINDOWS-1250, WINDOWS-1251, WINDOWS-1252, WINDOWS-1256,
     * UTF-16BE, UTF-8, ASCII, GBK, EUC-KR.
     */
    private string $encoding = Encoder::DEFAULT_BYTE_MODE_ENCODING;

    /**
     * The foreground color value of the QR code.
     */
    private ?ColorValue $colorValue = null;

    /**
     * The background color value of the QR code.
     */
    private ?ColorValue $backgroundColorValue = null;

    /**
     * The color model used to build colors.
     */
    private ColorModel $colorModel = ColorModel::RGB;

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
     * Initialise the generator, optionally seeding defaults from the package config.
     *
     * @param  array<mixed>  $config  The resolved `config('qrcode')` array (or a subset of it).
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

    /**
     * @param  array<int, mixed>  $arguments
     */
    public function __call(string $method, array $arguments): HtmlString
    {
        if (self::hasMacro($method)) {
            $result = $this->macroCall($method, $arguments);

            if ($result instanceof HtmlString) {
                return $result;
            }

            if (is_string($result) || $result instanceof Stringable) {
                return $this->generate((string) $result);
            }

            throw new UnexpectedValueException(sprintf(
                'Macro "%s" must return a string, Stringable, or HtmlString. %s returned.',
                $method,
                get_debug_type($result)
            ));
        }

        $dataType = $this->createClass($method);
        $dataType->create($arguments);

        return $this->generate(strval($dataType));
    }

    public function generate(string $text, ?string $filename = null): HtmlString
    {
        $qrCode = $this->getWriter($this->getRenderer())->writeString($text, $this->encoding, $this->errorCorrectionLevel->toBaconErrorCorrectionLevel());

        if ($this->imageMerge !== '') {
            $qrCode = $this->mergeImage($qrCode);
        }

        if ($filename && file_put_contents($filename, $qrCode) === false) {
            throw new RuntimeException('Failed to write QR code to file: '.$filename);
        }

        return new HtmlString($qrCode);
    }

    public function merge(string $filepath, float $percentage = .2, bool $absolute = false): self
    {
        if (function_exists('base_path') && ! $absolute) {
            $filepath = base_path().DIRECTORY_SEPARATOR.$filepath;
        }

        $content = file_get_contents($filepath);

        if ($content === false) {
            throw new InvalidArgumentException('Failed to read image file: '.$filepath);
        }

        $this->imageMerge = $content;
        $this->imagePercentage = $percentage;

        return $this;
    }

    public function mergeString(string $content, float $percentage = .2): self
    {
        $this->imageMerge = $content;
        $this->imagePercentage = $percentage;

        return $this;
    }

    public function size(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function format(string|Format $format): self
    {
        if (is_string($format)) {
            $format = Format::tryFrom($format);
        }

        if (! $format) {
            throw new InvalidArgumentException('$format must be one of the following values: '.implode(', ', Format::toArray()));
        }

        $this->format = $format;

        return $this;
    }

    public function cmyk(): self
    {
        $this->colorModel = ColorModel::CMYK;

        return $this;
    }

    public function rgb(): self
    {
        $this->colorModel = ColorModel::RGB;

        return $this;
    }

    public function gray(int $gray, ?int $backgroundGray = null): self
    {
        if ($gray < 0 || $gray > 100) {
            throw new InvalidArgumentException('Gray value must be between 0 and 100.');
        }

        if ($backgroundGray !== null && ($backgroundGray < 0 || $backgroundGray > 100)) {
            throw new InvalidArgumentException('Background gray value must be between 0 and 100.');
        }

        $this->colorModel = ColorModel::GRAY;
        $this->colorValue = new ColorValue($gray, 0, 0);

        $this->backgroundColorValue = new ColorValue($backgroundGray ?? 100, 0, 0);

        return $this;
    }

    public function color(int $c1, int $c2, int $c3, ?int $c4 = null): self
    {
        $this->colorValue = new ColorValue($c1, $c2, $c3, $c4);

        return $this;
    }

    public function backgroundColor(int $c1, int $c2, int $c3, ?int $c4 = null): self
    {
        $this->backgroundColorValue = new ColorValue($c1, $c2, $c3, $c4);

        return $this;
    }

    public function eyeColor(int $eyeNumber, int $innerRed, int $innerGreen, int $innerBlue, int $outerRed = 0, int $outerGreen = 0, int $outerBlue = 0): self
    {
        if ($eyeNumber < 0 || $eyeNumber > 2) {
            throw new InvalidArgumentException(sprintf('$eyeNumber must be 0, 1, or 2.  %s is not valid.', $eyeNumber));
        }

        $this->eyeColors[$eyeNumber] = new EyeFill(
            $this->createColor($innerRed, $innerGreen, $innerBlue),
            $this->createColor($outerRed, $outerGreen, $outerBlue)
        );

        return $this;
    }

    public function gradient(int $startRed, int $startGreen, int $startBlue, int $endRed, int $endGreen, int $endBlue, string|GradientType $type): self
    {
        if (is_string($type)) {
            $type = GradientType::tryFrom($type);
        }

        if (! $type) {
            throw new InvalidArgumentException('$type must be one of the following values: '.implode(', ', GradientType::toArray()));
        }

        $this->gradient = new Gradient(
            $this->createColor($startRed, $startGreen, $startBlue),
            $this->createColor($endRed, $endGreen, $endBlue),
            $type->toBaconGradientType()
        );

        return $this;
    }

    public function eye(string|EyeStyle $style): self
    {
        if (is_string($style)) {
            $style = EyeStyle::tryFrom($style);
        }

        if (! $style) {
            throw new InvalidArgumentException('$style must be one of the following values: '.implode(', ', EyeStyle::toArray()));
        }

        $this->eyeStyle = $style;

        return $this;
    }

    public function internalEye(string|EyeStyle $style): self
    {
        if (is_string($style)) {
            $style = EyeStyle::tryFrom($style);
        }

        if (! $style) {
            throw new InvalidArgumentException('$style must be one of the following values: '.implode(', ', EyeStyle::toArray()));
        }

        $this->internalEyeStyle = $style;

        return $this;
    }

    public function style(string|Style $style, float $size = 0.5): self
    {
        if (is_string($style)) {
            $style = Style::tryFrom($style);
        }

        if (! $style) {
            throw new InvalidArgumentException('$style must be one of the following values: '.implode(', ', Style::toArray()));
        }

        if ($size <= 0 || $size > 1) {
            throw new InvalidArgumentException(sprintf('$size must be greater than 0 and less than or equal to 1. %s is not valid.', $size));
        }

        $this->style = $style;
        $this->styleSize = $size;

        return $this;
    }

    public function encoding(string $encoding): self
    {
        $this->encoding = strtoupper($encoding);

        return $this;
    }

    public function errorCorrection(string|ErrorCorrectionLevel $errorCorrection): self
    {
        if (is_string($errorCorrection)) {
            $errorCorrection = ErrorCorrectionLevel::tryFrom(strtoupper($errorCorrection));
        }

        if (! $errorCorrection) {
            throw new InvalidArgumentException('$errorCorrection must be one of the following values: '.implode(', ', ErrorCorrectionLevel::toArray()));
        }

        $this->errorCorrectionLevel = $errorCorrection;

        return $this;
    }

    public function margin(int $margin): self
    {
        $this->margin = $margin;

        return $this;
    }

    private function getWriter(RendererInterface $renderer): Writer
    {
        return new Writer($renderer);
    }

    private function getRenderer(): RendererInterface
    {
        if (! extension_loaded('imagick') && ! extension_loaded('gd')) {
            throw new RuntimeException('The imagick or gd extension is required to generate QR codes.');
        }

        if (extension_loaded('imagick')) {
            return new ImageRenderer(
                $this->getRendererStyle(),
                $this->getFormatter()
            );
        }

        if ($this->format !== Format::PNG) {
            throw new RuntimeException('The imagick extension is required to generate QR codes in '.$this->format->value.' format.');
        }

        return new GDLibRenderer(
            $this->size,
            $this->margin,
            $this->format->value,
            self::PNG_COMPRESSION_LEVEL,
            $this->getFill()
        );
    }

    private function getRendererStyle(): RendererStyle
    {
        return new RendererStyle($this->size, $this->margin, $this->getModule(), $this->getEye(), $this->getFill());
    }

    private function getModule(): ModuleInterface
    {
        if ($this->style === Style::DOT) {
            return new DotsModule($this->styleSize);
        }

        if ($this->style === Style::ROUND) {
            return new RoundnessModule($this->styleSize);
        }

        return SquareModule::instance();
    }

    private function getEye(): EyeInterface
    {
        $module = $this->getModule();
        $externalEye = $this->getEyeInstance($this->eyeStyle, $module);

        if ($this->internalEyeStyle instanceof EyeStyle) {
            $internalEye = $this->getEyeInstance($this->internalEyeStyle, $module);

            return new CompositeEye($externalEye, $internalEye);
        }

        return $externalEye;
    }

    private function getEyeInstance(?EyeStyle $eyeStyle, ModuleInterface $module): EyeInterface
    {
        return match ($eyeStyle) {
            EyeStyle::SQUARE => SquareEye::instance(),
            EyeStyle::CIRCLE => SimpleCircleEye::instance(),
            EyeStyle::POINTY => PointyEye::instance(),
            null => new ModuleEye($module),
        };
    }

    private function getFill(): Fill
    {
        $foregroundColor = $this->buildColor($this->colorValue) ?? new Rgb(0, 0, 0);
        $backgroundColor = $this->buildColor($this->backgroundColorValue) ?? new Rgb(255, 255, 255);
        $eye0 = $this->eyeColors[0] ?? EyeFill::inherit();
        $eye1 = $this->eyeColors[1] ?? EyeFill::inherit();
        $eye2 = $this->eyeColors[2] ?? EyeFill::inherit();

        if ($this->gradient instanceof Gradient) {
            return Fill::withForegroundGradient($backgroundColor, $this->gradient, $eye0, $eye1, $eye2);
        }

        return Fill::withForegroundColor($backgroundColor, $foregroundColor, $eye0, $eye1, $eye2);
    }

    private function buildColor(?ColorValue $colorValue): ?ColorInterface
    {
        if (! $colorValue instanceof ColorValue) {
            return null;
        }

        if ($this->colorModel === ColorModel::GRAY) {
            return new Gray($colorValue->c1);
        }

        if ($this->colorModel === ColorModel::CMYK) {
            return new Cmyk($colorValue->c1, $colorValue->c2, $colorValue->c3, $colorValue->c4 ?? 0);
        }

        return $this->createColor($colorValue->c1, $colorValue->c2, $colorValue->c3, $colorValue->c4);
    }

    private function createColor(int $red, int $green, int $blue, ?int $alpha = null): ColorInterface
    {
        if (is_null($alpha)) {
            return new Rgb($red, $green, $blue);
        }

        return new Alpha($alpha, new Rgb($red, $green, $blue));
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

    private function getFormatter(): ImageBackEndInterface
    {
        return match ($this->format) {
            Format::PNG => new ImagickImageBackEnd('png'),
            Format::WEBP => new ImagickImageBackEnd('webp'),
            Format::EPS => new EpsImageBackEnd,
            Format::SVG => new SvgImageBackEnd,
        };
    }

    private function mergeImage(string $qrCode): string
    {
        if ($this->format === Format::EPS) {
            $merger = new EpsMerger($qrCode, $this->imageMerge, $this->imagePercentage);

            return $merger->merge();
        }

        if ($this->format === Format::SVG) {
            $merger = new SvgMerger($qrCode, $this->imageMerge, $this->imagePercentage);

            return $merger->merge();
        }

        if (extension_loaded('imagick') && in_array($this->format, [Format::PNG, Format::WEBP], true)) {
            $merger = new ImagickMerger($qrCode, $this->imageMerge, $this->format->value, $this->imagePercentage);

            return $merger->merge();
        }

        $merger = new RasterMerger(new Image($qrCode), new Image($this->imageMerge), $this->format->value, $this->imagePercentage);

        return $merger->merge();
    }

    private function createClass(string $method): DataTypeInterface
    {
        $class = $this->formatClass($method);

        if (! class_exists($class)) {
            throw new BadMethodCallException;
        }

        $instance = new $class;

        if ($instance::class !== $class) {
            throw new BadMethodCallException;
        }

        if (! $instance instanceof DataTypeInterface) {
            throw new BadMethodCallException;
        }

        return $instance;
    }

    private function formatClass(string $method): string
    {
        return 'Linkxtr\\QrCode\\DataTypes\\'.$method;
    }
}

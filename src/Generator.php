<?php

declare(strict_types=1);

namespace Linkxtr\QrCode;

use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\ColorInterface;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Eye\EyeInterface;
use BaconQrCode\Renderer\Eye\ModuleEye;
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
use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\Enums\ErrorCorrectionLevel;
use Linkxtr\QrCode\Enums\EyeStyle;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Enums\GradientType;
use Linkxtr\QrCode\Enums\Style;
use Linkxtr\QrCode\Mergers\EpsMerger;
use Linkxtr\QrCode\Mergers\RasterMerger;
use Linkxtr\QrCode\Mergers\SvgMerger;
use Linkxtr\QrCode\Support\Image;
use ReflectionClass;
use RuntimeException;

final class Generator
{
    /**
     * The PNG compression level.
     */
    private const PNG_COMPRESSION_LEVEL = 9;

    /**
     * The output format.
     * ['svg', 'eps', 'png', 'webp']
     */
    private Format $format = Format::SVG;

    /**
     * The size of the QR code in pixels.
     */
    private int $size = 100;

    /**
     * The margin around the QR code.
     */
    private int $margin = 0;

    /**
     * The error correction level.
     * L: 7% loss.
     * M: 15% loss.
     * Q: 25% loss.
     * H: 30% loss.
     */
    private ErrorCorrectionLevel $errorCorrection = ErrorCorrectionLevel::L;

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
     * The style of the blocks within the QR code.
     * Possible values are 'square', 'dot' and 'round'.
     */
    private Style $style = Style::SQUARE;

    /**
     * The size of the selected style between 0 and 1.
     * Only applicable to 'dot' and 'round' styles.
     */
    private float $styleSize = 0.5;

    /**
     * The style to apply to the eyes of the QR code.
     * Possible values are circle and square.
     */
    private ?EyeStyle $eyeStyle = null;

    /**
     * The foreground color of the QR code.
     */
    private ?ColorInterface $color = null;

    /**
     * The background color of the QR code.
     */
    private ?ColorInterface $backgroundColor = null;

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
     * @param  array<int, mixed>  $arguments
     */
    public function __call(string $method, array $arguments): HtmlString
    {
        $dataType = $this->createClass($method);
        $dataType->create($arguments);

        return $this->generate(strval($dataType));
    }

    public function generate(string $text, ?string $filename = null): HtmlString
    {
        $qrCode = $this->getWriter($this->getRenderer())->writeString($text, $this->encoding, $this->errorCorrection->toBaconErrorCorrectionLevel());

        if ($this->imageMerge !== '') {
            $qrCode = $this->mergeImage($qrCode);
        }

        if ($filename && file_put_contents($filename, $qrCode) === false) {
            throw new RuntimeException("Failed to write QR code to file: {$filename}");
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
            throw new InvalidArgumentException("Failed to read image file: {$filepath}");
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

    public function color(int $red, int $green, int $blue, ?int $alpha = null): self
    {
        $this->color = $this->createColor($red, $green, $blue, $alpha);

        return $this;
    }

    public function backgroundColor(int $red, int $green, int $blue, ?int $alpha = null): self
    {
        $this->backgroundColor = $this->createColor($red, $green, $blue, $alpha);

        return $this;
    }

    public function eyeColor(int $eyeNumber, int $innerRed, int $innerGreen, int $innerBlue, int $outterRed = 0, int $outterGreen = 0, int $outterBlue = 0): self
    {
        if ($eyeNumber < 0 || $eyeNumber > 2) {
            throw new InvalidArgumentException("\$eyeNumber must be 0, 1, or 2.  {$eyeNumber} is not valid.");
        }

        $this->eyeColors[$eyeNumber] = new EyeFill(
            $this->createColor($innerRed, $innerGreen, $innerBlue),
            $this->createColor($outterRed, $outterGreen, $outterBlue)
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

    public function style(string|Style $style, float $size = 0.5): self
    {
        if (is_string($style)) {
            $style = Style::tryFrom($style);
        }

        if (! $style) {
            throw new InvalidArgumentException('$style must be one of the following values: '.implode(', ', Style::toArray()));
        }

        if ($size <= 0 || $size > 1) {
            throw new InvalidArgumentException("\$size must be greater than 0 and less than or equal to 1. {$size} is not valid.");
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

        $this->errorCorrection = $errorCorrection;

        return $this;
    }

    public function margin(int $margin): self
    {
        $this->margin = $margin;

        return $this;
    }

    public function getWriter(RendererInterface $renderer): Writer
    {
        return new Writer($renderer);
    }

    public function getRenderer(): RendererInterface
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

    public function getRendererStyle(): RendererStyle
    {
        return new RendererStyle($this->size, $this->margin, $this->getModule(), $this->getEye(), $this->getFill());
    }

    public function getModule(): ModuleInterface
    {
        if ($this->style === Style::DOT) {
            return new DotsModule($this->styleSize);
        }

        if ($this->style === Style::ROUND) {
            return new RoundnessModule($this->styleSize);
        }

        return SquareModule::instance();
    }

    public function getEye(): EyeInterface
    {
        if ($this->eyeStyle === EyeStyle::SQUARE) {
            return SquareEye::instance();
        }

        if ($this->eyeStyle === EyeStyle::CIRCLE) {
            return SimpleCircleEye::instance();
        }

        return new ModuleEye($this->getModule());
    }

    public function getFill(): Fill
    {
        $foregroundColor = $this->color ?? new Rgb(0, 0, 0);
        $backgroundColor = $this->backgroundColor ?? new Rgb(255, 255, 255);
        $eye0 = $this->eyeColors[0] ?? EyeFill::inherit();
        $eye1 = $this->eyeColors[1] ?? EyeFill::inherit();
        $eye2 = $this->eyeColors[2] ?? EyeFill::inherit();

        if ($this->gradient instanceof Gradient) {
            return Fill::withForegroundGradient($backgroundColor, $this->gradient, $eye0, $eye1, $eye2);
        }

        return Fill::withForegroundColor($backgroundColor, $foregroundColor, $eye0, $eye1, $eye2);
    }

    public function createColor(int $red, int $green, int $blue, ?int $alpha = null): ColorInterface
    {
        if (is_null($alpha)) {
            return new Rgb($red, $green, $blue);
        }

        return new Alpha($alpha, new Rgb($red, $green, $blue));
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

        $merger = new RasterMerger(new Image($qrCode), new Image($this->imageMerge), $this->format->value, $this->imagePercentage);

        return $merger->merge();
    }

    private function createClass(string $method): DataTypeInterface
    {
        $class = $this->formatClass($method);

        if (! class_exists($class)) {
            throw new BadMethodCallException;
        }

        $reflection = new ReflectionClass($class);

        if ($reflection->getShortName() !== $method) {
            throw new BadMethodCallException;
        }

        $instance = new $class;

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

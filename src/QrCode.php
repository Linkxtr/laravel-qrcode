<?php

namespace Linkxtr\QrCode;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\ColorInterface;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Eye\EyeInterface;
use BaconQrCode\Renderer\Eye\ModuleEye;
use BaconQrCode\Renderer\Eye\SimpleCircleEye;
use BaconQrCode\Renderer\Eye\SquareEye;
use BaconQrCode\Renderer\Image\EpsImageBackEnd;
use BaconQrCode\Renderer\Image\ImageBackEndInterface;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Module\DotsModule;
use BaconQrCode\Renderer\Module\ModuleInterface;
use BaconQrCode\Renderer\Module\RoundnessModule;
use BaconQrCode\Renderer\Module\SquareModule;
use BaconQrCode\Renderer\RendererStyle\EyeFill;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\Gradient;
use BaconQrCode\Renderer\RendererStyle\GradientType;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use BadMethodCallException;
use Illuminate\Support\HtmlString;
use InvalidArgumentException;
use Linkxtr\QrCode\DataTypes\DataTypeInterface;

class QrCode
{
    /**
     * The output format.
     */
    protected string $format = 'svg';

    /**
     * The size of the QR code in pixels.
     */
    protected int $size = 100;

    /**
     * The margin around the QR code.
     */
    protected int $margin = 0;

    /**
     * The error correction level.
     * L: 7% loss.
     * M: 15% loss.
     * Q: 25% loss.
     * H: 30% loss.
     */
    protected ?ErrorCorrectionLevel $errorCorrection = null;

    /**
     * The encoding mode. Possible values are
     * ISO-8859-2, ISO-8859-3, ISO-8859-4, ISO-8859-5, ISO-8859-6,
     * ISO-8859-7, ISO-8859-8, ISO-8859-9, ISO-8859-10, ISO-8859-11,
     * ISO-8859-12, ISO-8859-13, ISO-8859-14, ISO-8859-15, ISO-8859-16,
     * SHIFT-JIS, WINDOWS-1250, WINDOWS-1251, WINDOWS-1252, WINDOWS-1256,
     * UTF-16BE, UTF-8, ASCII, GBK, EUC-KR.
     */
    protected string $encoding = Encoder::DEFAULT_BYTE_MODE_ENCODING;

    /**
     * The style of the blocks within the QrCode.
     * Possible values are 'square', 'dot' and 'round'.
     */
    protected string $style = 'square';

    /**
     * The size of the selected style between 0 and 1.
     * Only applicable to 'dot' and 'round' styles.
     */
    protected float $styleSize = 0.5;

    /**
     * The style to apply to the eyes of the QR code.
     * Possible values are circle and square.
     */
    protected ?string $eyeStyle = null;

    /**
     * The foreground color of the QR code.
     */
    protected ?ColorInterface $color = null;

    /**
     * The background color of the QR code.
     */
    protected ?ColorInterface $backgroundColor = null;

    /**
     * An array that holds EyeFills of the color of the eyes.
     *
     * @var array<int, EyeFill>
     */
    protected array $eyeColors = [];

    /**
     * The gradient to apply to the QrCode.
     *
     * @var ?Gradient
     */
    protected $gradient = null;

    /**
     * Holds an image string that will be merged with the QrCode.
     */
    protected ?string $imageMerge = null;

    /**
     * The percentage that a merged image should take over the source image.
     */
    protected float $imagePercentage = 0.2;

    /**
     * @param  array<int, mixed>  $arguments
     * @return string|void|\Illuminate\Support\HtmlString
     */
    public function __call(string $method, array $arguments)
    {
        $dataType = $this->createClass($method);
        $dataType->create($arguments);

        return $this->generate(strval($dataType));
    }

    /**
     * @return string|void|\Illuminate\Support\HtmlString
     */
    public function generate(string $text, ?string $filename = null)
    {
        $qrCode = $this->getWriter($this->getRenderer())->writeString($text, $this->encoding, $this->errorCorrection);

        if ($this->imageMerge !== null && $this->format === 'png') {
            $merger = new ImageMerge(new Image($qrCode), new Image($this->imageMerge));
            $qrCode = $merger->merge($this->imagePercentage);
        }

        if ($filename) {
            file_put_contents($filename, $qrCode);

            return;
        }

        if (class_exists(HtmlString::class)) {
            return new HtmlString($qrCode);
        }

        return $qrCode;
    }

    public function merge(string $filepath, float $percentage = .2, bool $absolute = false): self
    {
        if (function_exists('base_path') && ! $absolute) {
            $filepath = base_path().$filepath;
        }

        $this->imageMerge = file_get_contents($filepath) ?: null;
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

    public function format(string $format): self
    {
        if (! in_array($format, ['svg', 'eps', 'png'])) {
            throw new InvalidArgumentException("\$format must be svg, eps, or png. {$format} is not a valid.");
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

    public function gradient(int $startRed, int $startGreen, int $startBlue, int $endRed, int $endGreen, int $endBlue, string $type): self
    {
        $type = strtoupper($type);

        $this->gradient = new Gradient(
            $this->createColor($startRed, $startGreen, $startBlue),
            $this->createColor($endRed, $endGreen, $endBlue),
            GradientType::$type()
        );

        return $this;
    }

    public function eye(string $style): self
    {
        if (! in_array($style, ['square', 'circle'])) {
            throw new InvalidArgumentException("\$style must be square or circle. {$style} is not a valid eye style.");
        }

        $this->eyeStyle = $style;

        return $this;
    }

    public function style(string $style, float $size = 0.5): self
    {
        if (! in_array($style, ['square', 'dot', 'round'])) {
            throw new InvalidArgumentException("\$style must be square, dot, or round. {$style} is not a valid.");
        }

        if ($size < 0 || $size >= 1) {
            throw new InvalidArgumentException("\$size must be between 0 and 1.  {$size} is not valid.");
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

    public function errorCorrection(string $errorCorrection): self
    {
        $errorCorrection = strtoupper($errorCorrection);
        $this->errorCorrection = ErrorCorrectionLevel::$errorCorrection();

        return $this;
    }

    public function margin(int $margin): self
    {
        $this->margin = $margin;

        return $this;
    }

    public function getWriter(ImageRenderer $renderer): Writer
    {
        return new Writer($renderer);
    }

    public function getRenderer(): ImageRenderer
    {
        $renderer = new ImageRenderer(
            $this->getRendererStyle(),
            $this->getFormatter()
        );

        return $renderer;
    }

    public function getRendererStyle(): RendererStyle
    {
        return new RendererStyle($this->size, $this->margin, $this->getModule(), $this->getEye(), $this->getFill());
    }

    public function getFormatter(): ImageBackEndInterface
    {
        if ($this->format === 'png') {
            return new ImagickImageBackEnd('png');
        }

        if ($this->format === 'eps') {
            return new EpsImageBackEnd;
        }

        return new SvgImageBackEnd;
    }

    public function getModule(): ModuleInterface
    {
        if ($this->style === 'dot') {
            return new DotsModule($this->styleSize);
        }

        if ($this->style === 'round') {
            return new RoundnessModule($this->styleSize);
        }

        return SquareModule::instance();
    }

    public function getEye(): EyeInterface
    {
        if ($this->eyeStyle === 'square') {
            return SquareEye::instance();
        }

        if ($this->eyeStyle === 'circle') {
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

        if ($this->gradient) {
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

    protected function createClass(string $method): DataTypeInterface
    {
        $class = $this->formatClass($method);

        if (! class_exists($class)) {
            throw new BadMethodCallException;
        }

        return new $class;
    }

    /** @return class-string<DataTypeInterface> */
    protected function formatClass(string $method): string
    {
        $method = ucfirst($method);

        /** @var class-string<DataTypeInterface> */
        $class = "Linkxtr\QrCode\DataTypes\\".$method;

        return $class;
    }
}

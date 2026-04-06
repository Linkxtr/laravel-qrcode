<?php

declare(strict_types=1);

namespace Linkxtr\QrCode;

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
use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\DTOs\Config;
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

use function strval;

/**
 * @method HtmlString BTC(string $address, float $amount, array<mixed> $options = [])
 * @method HtmlString CalendarEvent(array<mixed> $attributes)
 * @method HtmlString Email(string $address, string $subject = '', string $body = '', string $cc = '', string $bcc = '')
 * @method HtmlString Ethereum(string $address, ?float $amount = null)
 * @method HtmlString Geo(float $latitude, float $longitude, string $name = '')
 * @method HtmlString MeCard(string|array<mixed> $name, ?string $phone = null, ?string $email = null, ?string $note = null, ?string $birthday = null, ?string $address = null, ?string $url = null)
 * @method HtmlString PhoneNumber(string $phoneNumber)
 * @method HtmlString SMS(string $smsAddress = '', string $message = '')
 * @method HtmlString Telegram(string|array<mixed> $username)
 * @method HtmlString VCard(array<mixed> $properties)
 * @method HtmlString WhatsApp(string|array<mixed> $number, ?string $message = null)
 * @method HtmlString WiFi(array<mixed> $credentials)
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

    private Config $config;

    /**
     * Initialise the generator, optionally seeding defaults from the package config.
     *
     * @param  array<mixed>  $config  The resolved `config('qrcode')` array (or a subset of it).
     */
    public function __construct(array $config = [])
    {
        $this->config = new Config($config);
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
        $qrCode = $this->getWriter($this->getRenderer())->writeString($text, $this->config->getEncoding(), $this->config->getErrorCorrectionLevel()->toBaconErrorCorrectionLevel());

        if ($this->config->getImageMerge() !== '') {
            $qrCode = $this->mergeImage($qrCode);
        }

        if ($filename && file_put_contents($filename, $qrCode) === false) {
            throw new RuntimeException('Failed to write QR code to file: '.$filename);
        }

        return new HtmlString($qrCode);
    }

    public function merge(string $filepath, float $percentage = .2, bool $absolute = false): self
    {
        $this->config->setupMergePath($filepath, $percentage, $absolute);

        return $this;
    }

    public function mergeString(string $content, float $percentage = .2): self
    {
        $this->config->setupMergeString($content, $percentage);

        return $this;
    }

    public function size(int $size): self
    {
        $this->config->setSize($size);

        return $this;
    }

    public function format(string|Format $format): self
    {
        $this->config->setFormat($format);

        return $this;
    }

    public function cmyk(): self
    {
        $this->config->setColorModel(ColorModel::CMYK);

        return $this;
    }

    public function rgb(): self
    {
        $this->config->setColorModel(ColorModel::RGB);

        return $this;
    }

    public function gray(int $gray, ?int $backgroundGray = null): self
    {
        $this->config->setGrayscale($gray, $backgroundGray);

        return $this;
    }

    public function color(int $c1, int $c2, int $c3, ?int $c4 = null): self
    {
        $this->config->setupColor($c1, $c2, $c3, $c4);

        return $this;
    }

    public function backgroundColor(int $c1, int $c2, int $c3, ?int $c4 = null): self
    {
        $this->config->setupBackgroundColor($c1, $c2, $c3, $c4);

        return $this;
    }

    public function eyeColor(int $eyeNumber, int $innerRed, int $innerGreen, int $innerBlue, int $outerRed = 0, int $outerGreen = 0, int $outerBlue = 0): self
    {
        $this->config->setupEyeColor($eyeNumber, $innerRed, $innerGreen, $innerBlue, $outerRed, $outerGreen, $outerBlue);

        return $this;
    }

    public function gradient(int $startRed, int $startGreen, int $startBlue, int $endRed, int $endGreen, int $endBlue, string|GradientType $type): self
    {
        $this->config->setupGradient($startRed, $startGreen, $startBlue, $endRed, $endGreen, $endBlue, $type);

        return $this;
    }

    public function eye(string|EyeStyle $style): self
    {
        $this->config->setEyeStyle($style);

        return $this;
    }

    public function internalEye(string|EyeStyle $style): self
    {
        $this->config->setInternalEyeStyle($style);

        return $this;
    }

    public function style(string|Style $style, float $size = 0.5): self
    {
        $this->config->setupStyle($style, $size);

        return $this;
    }

    public function encoding(string $encoding): self
    {
        $this->config->setEncoding($encoding);

        return $this;
    }

    public function errorCorrection(string|ErrorCorrectionLevel $errorCorrection): self
    {
        $this->config->setErrorCorrectionLevel($errorCorrection);

        return $this;
    }

    public function margin(int $margin): self
    {
        $this->config->setMargin($margin);

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

        if ($this->config->getFormat() !== Format::PNG) {
            throw new RuntimeException('The imagick extension is required to generate QR codes in '.$this->config->getFormat()->value.' format.');
        }

        return new GDLibRenderer(
            $this->config->getSize(),
            $this->config->getMargin(),
            $this->config->getFormat()->value,
            self::PNG_COMPRESSION_LEVEL,
            $this->getFill()
        );
    }

    private function getRendererStyle(): RendererStyle
    {
        return new RendererStyle($this->config->getSize(), $this->config->getMargin(), $this->getModule(), $this->getEye(), $this->getFill());
    }

    private function getModule(): ModuleInterface
    {
        if ($this->config->getStyle() === Style::DOT) {
            return new DotsModule($this->config->getStyleSize());
        }

        if ($this->config->getStyle() === Style::ROUND) {
            return new RoundnessModule($this->config->getStyleSize());
        }

        return SquareModule::instance();
    }

    private function getEye(): EyeInterface
    {
        $module = $this->getModule();
        $externalEye = $this->getEyeInstance($this->config->getEyeStyle(), $module);

        if ($this->config->getInternalEyeStyle() instanceof EyeStyle) {
            $internalEye = $this->getEyeInstance($this->config->getInternalEyeStyle(), $module);

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
        $foregroundColor = $this->buildColor($this->config->getColorValue()) ?? new Rgb(0, 0, 0);
        $backgroundColor = $this->buildColor($this->config->getBackgroundColorValue()) ?? new Rgb(255, 255, 255);
        $eye0 = $this->config->getEyeColors()[0] ?? EyeFill::inherit();
        $eye1 = $this->config->getEyeColors()[1] ?? EyeFill::inherit();
        $eye2 = $this->config->getEyeColors()[2] ?? EyeFill::inherit();

        if ($this->config->getGradient() instanceof Gradient) {
            return Fill::withForegroundGradient($backgroundColor, $this->config->getGradient(), $eye0, $eye1, $eye2);
        }

        return Fill::withForegroundColor($backgroundColor, $foregroundColor, $eye0, $eye1, $eye2);
    }

    private function buildColor(?ColorValue $colorValue): ?ColorInterface
    {
        if (! $colorValue instanceof ColorValue) {
            return null;
        }

        if ($this->config->getColorModel() === ColorModel::GRAY) {
            return new Gray($colorValue->c1);
        }

        if ($this->config->getColorModel() === ColorModel::CMYK) {
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

    private function getFormatter(): ImageBackEndInterface
    {
        return match ($this->config->getFormat()) {
            Format::PNG => new ImagickImageBackEnd('png'),
            Format::WEBP => new ImagickImageBackEnd('webp'),
            Format::EPS => new EpsImageBackEnd,
            Format::SVG => new SvgImageBackEnd,
        };
    }

    private function mergeImage(string $qrCode): string
    {
        if ($this->config->getFormat() === Format::EPS) {
            $merger = new EpsMerger($qrCode, $this->config->getImageMerge(), $this->config->getImagePercentage());

            return $merger->merge();
        }

        if ($this->config->getFormat() === Format::SVG) {
            $merger = new SvgMerger($qrCode, $this->config->getImageMerge(), $this->config->getImagePercentage());

            return $merger->merge();
        }

        if (extension_loaded('imagick') && in_array($this->config->getFormat(), [Format::PNG, Format::WEBP], true)) {
            $merger = new ImagickMerger($qrCode, $this->config->getImageMerge(), $this->config->getFormat()->value, $this->config->getImagePercentage());

            return $merger->merge();
        }

        $merger = new RasterMerger(new Image($qrCode), new Image($this->config->getImageMerge()), $this->config->getFormat()->value, $this->config->getImagePercentage());

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

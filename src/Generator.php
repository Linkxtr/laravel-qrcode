<?php

declare(strict_types=1);

namespace Linkxtr\QrCode;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Traits\Macroable;
use Linkxtr\QrCode\DTOs\Config;
use Linkxtr\QrCode\Enums\ColorModel;
use Linkxtr\QrCode\Enums\ErrorCorrectionLevel;
use Linkxtr\QrCode\Enums\EyeStyle;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Enums\GradientType;
use Linkxtr\QrCode\Enums\Style;
use Linkxtr\QrCode\Renderers\BaconRenderer;
use Linkxtr\QrCode\Support\DataTypeResolver;
use Linkxtr\QrCode\ValueObjects\Colors\Rgb;
use RuntimeException;
use Stringable;
use UnexpectedValueException;

/**
 * @method HtmlString BTC(string $address, string $amount, array<mixed> $options = [])
 * @method HtmlString CalendarEvent(array<mixed> $attributes)
 * @method HtmlString Email(string $address, string $subject = '', string $body = '', string $cc = '', string $bcc = '')
 * @method HtmlString Ethereum(string $address, ?string $amount = null)
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
     * @param  array<mixed>  $arguments
     */
    public function __call(string $method, array $arguments): HtmlString
    {
        if (! self::hasMacro($method)) {
            $payload = DataTypeResolver::resolve($method, $arguments);

            return $this->generate($payload);
        }

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

    public function __clone()
    {
        $this->config = clone $this->config;
    }

    public function generate(string $text, ?string $filename = null): HtmlString
    {
        $baconRenderer = new BaconRenderer($this->config);

        $htmlString = $baconRenderer->render($text);

        if ($filename !== null && file_put_contents($filename, $htmlString->toHtml()) === false) {
            throw new RuntimeException('Failed to write QR code to file: '.$filename);
        }

        return $htmlString;
    }

    public function merge(string $filepath, float $percentage = .2): self
    {
        $instance = clone $this;

        $instance->config->setupMergePath($filepath);
        $instance->config->setImagePercentage($percentage);

        return $instance;
    }

    public function mergeString(string $content, float $percentage = .2): self
    {
        $instance = clone $this;

        $instance->config->setupMergeString($content);
        $instance->config->setImagePercentage($percentage);

        return $instance;
    }

    public function size(int $size): self
    {
        $instance = clone $this;

        $instance->config->setSize($size);

        return $instance;
    }

    public function format(string|Format $format): self
    {
        $instance = clone $this;

        $instance->config->setFormat($format);

        return $instance;
    }

    public function cmyk(): self
    {
        $instance = clone $this;

        $instance->config->setColorModel(ColorModel::CMYK);

        return $instance;
    }

    public function rgb(): self
    {
        $instance = clone $this;

        $instance->config->setColorModel(ColorModel::RGB);

        return $instance;
    }

    public function gray(int $gray, ?int $backgroundGray = null): self
    {
        $instance = clone $this;

        $instance->config->setGrayscale($gray, $backgroundGray);

        return $instance;
    }

    public function color(int $c1, int $c2, int $c3, ?int $c4 = null): self
    {
        $instance = clone $this;

        $instance->config->setupColor($c1, $c2, $c3, $c4);

        return $instance;
    }

    public function backgroundColor(int $c1, int $c2, int $c3, ?int $c4 = null): self
    {
        $instance = clone $this;

        $instance->config->setupBackgroundColor($c1, $c2, $c3, $c4);

        return $instance;
    }

    /**
     * @param  string|array{0: int, 1: int, 2: int}  $inner
     * @param  string|array{0: int, 1: int, 2: int}|null  $outer
     */
    public function eyeColor(int $eyeNumber, string|array $inner, string|array|null $outer = null): self
    {
        $instance = clone $this;
        $innerRgb = Rgb::parse($inner);

        if ($outer === null) {
            $instance->config->setupEyeColor($eyeNumber, $innerRgb);

            return $instance;
        }

        $outerRgb = Rgb::parse($outer);

        $instance->config->setupEyeColor(
            $eyeNumber,
            $innerRgb,
            $outerRgb
        );

        return $instance;
    }

    /**
     * @param  string|array<mixed>  $start
     * @param  string|array<mixed>  $end
     */
    public function gradient(string|array $start, string|array $end, string|GradientType $type = GradientType::VERTICAL): self
    {
        $startRgb = Rgb::parse($start);
        $endRgb = Rgb::parse($end);
        $instance = clone $this;

        $instance->config->setupGradient($startRgb, $endRgb, $type);

        return $instance;
    }

    public function eye(string|EyeStyle $style): self
    {
        $instance = clone $this;

        $instance->config->setEyeStyle($style);

        return $instance;
    }

    public function internalEye(string|EyeStyle $style): self
    {
        $instance = clone $this;

        $instance->config->setInternalEyeStyle($style);

        return $instance;
    }

    public function style(string|Style $style, ?float $size = null): self
    {
        $instance = clone $this;

        $instance->config->setupStyle($style, $size);

        return $instance;
    }

    public function encoding(string $encoding): self
    {
        $instance = clone $this;

        $instance->config->setEncoding($encoding);

        return $instance;
    }

    public function errorCorrection(string|ErrorCorrectionLevel $errorCorrection): self
    {
        $instance = clone $this;

        $instance->config->setErrorCorrectionLevel($errorCorrection);

        return $instance;
    }

    public function margin(int $margin): self
    {
        $instance = clone $this;

        $instance->config->setMargin($margin);

        return $instance;
    }
}

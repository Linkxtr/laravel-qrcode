<?php

declare(strict_types=1);

namespace Linkxtr\QrCode;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Traits\Macroable;
use Linkxtr\QrCode\DTOs\Config;
use Linkxtr\QrCode\Enums\ColorModel;
use Linkxtr\QrCode\Enums\ErrorCorrectionLevel;
use Linkxtr\QrCode\Enums\EyeStyle;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Enums\GradientType;
use Linkxtr\QrCode\Enums\Style;
use Linkxtr\QrCode\Exceptions\CannotWriteFileException;
use Linkxtr\QrCode\Exceptions\InvalidMacroReturnTypeException;
use Linkxtr\QrCode\Renderers\BaconRenderer;
use Linkxtr\QrCode\Support\DataTypeResolver;
use Linkxtr\QrCode\Support\QrCodeResult;
use Linkxtr\QrCode\ValueObjects\Colors\Rgb;
use Stringable;

/**
 * @method QrCodeResult BTC(string $address, float|string $amount, ?string $label = null, ?string $message = null, ?string $returnAddress = null)
 * @method QrCodeResult CalendarEvent(string $summary, Carbon|DateTimeInterface|string|int $start, Carbon|DateTimeInterface|string|int $end, ?string $description = null, ?string $location = null, ?string $uid = null)
 * @method QrCodeResult Email(string $address, ?string $subject = null, ?string $body = null, ?string $cc = null, ?string $bcc = null)
 * @method QrCodeResult Ethereum(string $address, float|string|null $amount = null)
 * @method QrCodeResult Geo(float $latitude, float $longitude, ?string $name = null)
 * @method QrCodeResult MeCard(string $name, ?string $phone = null, ?string $email = null, ?string $url = null, ?string $address = null, ?string $reading = null, ?string $nickname = null, ?string $phone2 = null, ?string $phone3 = null, ?string $videoPhone = null, ?string $note = null, ?string $birthday = null, ?string $postOfficeBox = null)
 * @method QrCodeResult PhoneNumber(string|int $phoneNumber)
 * @method QrCodeResult SMS(string|int $phoneNumber, ?string $message = null)
 * @method QrCodeResult Telegram(string $username)
 * @method QrCodeResult VCard(string $name, ?string $phone = null, ?string $email = null, ?string $company = null, ?string $job = null, ?string $firstName = null, ?string $lastName = null, ?string $emailWork = null, ?string $emailHome = null, ?string $phoneWork = null, ?string $phoneHome = null, ?string $phoneCell = null, ?string $role = null, ?string $address = null, ?string $url = null, ?string $note = null, ?string $birthday = null)
 * @method QrCodeResult WhatsApp(string|int $phoneNumber, ?string $message = null)
 * @method QrCodeResult WiFi(string $ssid, ?string $encryption = null, ?string $password = null, bool $hidden = false)
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
        $this->config = Config::fromArray($config);
    }

    /**
     * @param  array<mixed>  $arguments
     */
    public function __call(string $method, array $arguments): QrCodeResult
    {
        if (! self::hasMacro($method)) {
            $payload = DataTypeResolver::resolve($method, $arguments);

            return $this->generate($payload);
        }

        $result = $this->macroCall($method, $arguments);

        if ($result instanceof QrCodeResult) {
            return $result;
        }

        if (is_string($result) || $result instanceof Stringable) {
            return $this->generate((string) $result);
        }

        throw InvalidMacroReturnTypeException::invalidType($method, get_debug_type($result));
    }

    public function __clone()
    {
        $this->config = clone $this->config;
    }

    public function generate(string $text, ?string $filename = null): QrCodeResult
    {
        $baconRenderer = new BaconRenderer($this->config);

        $qrCodeResult = $baconRenderer->render($text);

        if ($filename !== null) {
            $directory = dirname($filename);

            if (! is_dir($directory)) {
                throw CannotWriteFileException::toPath($filename);
            }

            $bytesWritten = @file_put_contents($filename, $qrCodeResult);

            if ($bytesWritten === false) {
                throw CannotWriteFileException::toPath($filename);
            }
        }

        return $qrCodeResult;
    }

    public function merge(string $filepath, float $percentage = .2): static
    {
        $instance = clone $this;

        $instance->config->setupMergePath($filepath);
        $instance->config->setImagePercentage($percentage);

        return $instance;
    }

    public function mergeString(string $content, float $percentage = .2): static
    {
        $instance = clone $this;

        $instance->config->setupMergeString($content);
        $instance->config->setImagePercentage($percentage);

        return $instance;
    }

    public function size(int $size): static
    {
        $instance = clone $this;

        $instance->config->setSize($size);

        return $instance;
    }

    public function format(string|Format $format): static
    {
        $instance = clone $this;

        $instance->config->setFormat($format);

        return $instance;
    }

    public function cmyk(): static
    {
        $instance = clone $this;

        $instance->config->setColorModel(ColorModel::CMYK);

        return $instance;
    }

    public function rgb(): static
    {
        $instance = clone $this;

        $instance->config->setColorModel(ColorModel::RGB);

        return $instance;
    }

    public function gray(int $gray, ?int $backgroundGray = null): static
    {
        $instance = clone $this;

        $instance->config->setGrayscale($gray, $backgroundGray);

        return $instance;
    }

    public function color(int $c1, int $c2, int $c3, ?int $c4 = null): static
    {
        $instance = clone $this;

        $instance->config->setupColor($c1, $c2, $c3, $c4);

        return $instance;
    }

    public function backgroundColor(int $c1, int $c2, int $c3, ?int $c4 = null): static
    {
        $instance = clone $this;

        $instance->config->setupBackgroundColor($c1, $c2, $c3, $c4);

        return $instance;
    }

    /**
     * @param  string|array<mixed>  $inner
     * @param  string|array<mixed>|null  $outer
     */
    public function eyeColor(int $eyeNumber, string|array $inner, string|array|null $outer = null): static
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
    public function gradient(string|array $start, string|array $end, string|GradientType $type = GradientType::VERTICAL): static
    {
        $startRgb = Rgb::parse($start);
        $endRgb = Rgb::parse($end);
        $instance = clone $this;

        $instance->config->setupGradient($startRgb, $endRgb, $type);

        return $instance;
    }

    public function eye(string|EyeStyle $style): static
    {
        $instance = clone $this;

        $instance->config->setEyeStyle($style);

        return $instance;
    }

    public function internalEye(string|EyeStyle $style): static
    {
        $instance = clone $this;

        $instance->config->setInternalEyeStyle($style);

        return $instance;
    }

    public function style(string|Style $style, ?float $size = null): static
    {
        $instance = clone $this;

        $instance->config->setupStyle($style, $size);

        return $instance;
    }

    public function encoding(string $encoding): static
    {
        $instance = clone $this;

        $instance->config->setEncoding($encoding);

        return $instance;
    }

    public function errorCorrection(string|ErrorCorrectionLevel $errorCorrection): static
    {
        $instance = clone $this;

        $instance->config->setErrorCorrectionLevel($errorCorrection);

        return $instance;
    }

    public function margin(int $margin): static
    {
        $instance = clone $this;

        $instance->config->setMargin($margin);

        return $instance;
    }
}

<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Facades;

use Illuminate\Support\Facades\Facade;
use Linkxtr\QrCode\Enums\ErrorCorrectionLevel;
use Linkxtr\QrCode\Enums\EyeStyle;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Enums\GradientType;
use Linkxtr\QrCode\Enums\Style;
use Linkxtr\QrCode\Generator;
use Linkxtr\QrCode\Support\QrCodeResult;

/**
 * @method static Generator size(int $size)
 * @method static Generator margin(int $margin)
 * @method static Generator color(int $c1, int $c2, int $c3, ?int $c4 = null)
 * @method static Generator backgroundColor(int $c1, int $c2, int $c3, ?int $c4 = null)
 * @method static Generator cmyk()
 * @method static Generator rgb()
 * @method static Generator gray(int $gray, ?int $backgroundGray = null)
 * @method static Generator eyeColor(int $eyeNumber, array<mixed> $inner, array<mixed>|null $outer = null)
 * @method static Generator gradient(string|array<mixed> $start, string|array<mixed> $end, string|GradientType $type = GradientType::VERTICAL)
 * @method static Generator eye(string|EyeStyle $style)
 * @method static Generator internalEye(string|EyeStyle $style)
 * @method static Generator style(string|Style $style, float $size = 0.5)
 * @method static Generator errorCorrection(string|ErrorCorrectionLevel $errorCorrection)
 * @method static Generator encoding(string $encoding)
 * @method static Generator format(string|Format $format)
 * @method static Generator merge(string $filePath, float $percentage = 0.2)
 * @method static Generator mergeString(string $content, float $percentage = 0.2)
 * @method static QrCodeResult generate(string $text, ?string $filename = null)
 * @method static QrCodeResult BTC(string $address, int|float|string $amount, array<mixed> $options = [])
 * @method static QrCodeResult CalendarEvent(array<mixed> $attributes)
 * @method static QrCodeResult Email(string $address, string $subject = '', string $body = '', string $cc = '', string $bcc = '')
 * @method static QrCodeResult Ethereum(string $address, int|float|string|null $amount = null)
 * @method static QrCodeResult Geo(float $latitude, float $longitude, string $name = '')
 * @method static QrCodeResult MeCard(string|array<mixed> $name, ?string $phone = null, ?string $email = null, ?string $note = null, ?string $birthday = null, ?string $address = null, ?string $url = null)
 * @method static QrCodeResult PhoneNumber(string $phoneNumber)
 * @method static QrCodeResult SMS(string $smsAddress = '', string $message = '')
 * @method static QrCodeResult Telegram(string|array<mixed> $username)
 * @method static QrCodeResult VCard(array<mixed> $properties)
 * @method static QrCodeResult WhatsApp(string|array<mixed> $number, ?string $message = null)
 * @method static QrCodeResult WiFi(array<mixed> $credentials)
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
        return 'qrcode';
    }
}

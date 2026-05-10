<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Support;

use Linkxtr\QrCode\DataTypes\BTC;
use Linkxtr\QrCode\DataTypes\CalendarEvent;
use Linkxtr\QrCode\DataTypes\Email;
use Linkxtr\QrCode\DataTypes\Ethereum;
use Linkxtr\QrCode\DataTypes\Geo;
use Linkxtr\QrCode\DataTypes\MeCard;
use Linkxtr\QrCode\DataTypes\PhoneNumber;
use Linkxtr\QrCode\DataTypes\SMS;
use Linkxtr\QrCode\DataTypes\Telegram;
use Linkxtr\QrCode\DataTypes\VCard;
use Linkxtr\QrCode\DataTypes\WhatsApp;
use Linkxtr\QrCode\DataTypes\WiFi;
use Linkxtr\QrCode\Exceptions\UnknownMethodException;

final class DataTypeResolver
{
    /**
     * Map of normalized method names to their concrete DataType classes.
     */
    private const MAP = [
        'btc' => BTC::class,
        'calendarevent' => CalendarEvent::class,
        'email' => Email::class,
        'ethereum' => Ethereum::class,
        'geo' => Geo::class,
        'mecard' => MeCard::class,
        'phonenumber' => PhoneNumber::class,
        'sms' => SMS::class,
        'telegram' => Telegram::class,
        'vcard' => VCard::class,
        'whatsapp' => WhatsApp::class,
        'wifi' => WiFi::class,
    ];

    /**
     * Resolve a data type method call into its string payload.
     *
     * @param  array<mixed>  $arguments
     *
     * @throws UnknownMethodException
     */
    public static function resolve(string $method, array $arguments): string
    {
        $normalizedMethod = strtolower($method);

        if (! array_key_exists($normalizedMethod, self::MAP)) {
            throw UnknownMethodException::methodNotFound($method);
        }

        $className = self::MAP[$normalizedMethod];

        $dataType = new $className;

        $dataType->create($arguments);

        return (string) $dataType;
    }
}

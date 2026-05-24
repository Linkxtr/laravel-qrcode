<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Exceptions\DataTypes;

use Linkxtr\QrCode\Exceptions\InvalidDataTypeArgumentException;

final class InvalidWiFiArgumentException extends InvalidDataTypeArgumentException
{
    public static function invalidSsidValue(string $type): self
    {
        if ($type === 'string') {
            $type = 'empty string';
        }

        $exception = new self(sprintf('WiFi SSID must be a non-empty string. Provided type: %s', $type));
        $exception->errorCode = 'INVALID_WIFI_SSID_TYPE';
        $exception->helperMessage = 'Ensure the WiFi SSID is a non-empty string.';

        return $exception;
    }

    public static function invalidEncryptionValue(string $encryption): self
    {
        $exception = new self(sprintf('WiFi encryption must be WEP, WPA, WPA2, WPA3 or NOPASS. Provided encryption: %s', $encryption));
        $exception->errorCode = 'INVALID_WIFI_ENCRYPTION';
        $exception->helperMessage = 'Ensure the WiFi encryption is WEP, WPA, WPA2, WPA3 or NOPASS.';

        return $exception;
    }

    public static function passwordWithNopassEncryption(): self
    {
        $exception = new self('WiFi password cannot be provided when encryption is NOPASS.');
        $exception->errorCode = 'INVALID_WIFI_PASSWORD_WITH_NOPASS_ENCRYPTION';
        $exception->helperMessage = 'Ensure the WiFi password is not provided when encryption is NOPASS.';

        return $exception;
    }

    public static function invalidHiddenType(string $type): self
    {
        $exception = new self(sprintf('WiFi hidden flag must be a boolean or a string representation of a boolean. Provided type: %s', $type));
        $exception->errorCode = 'INVALID_WIFI_HIDDEN_TYPE';
        $exception->helperMessage = 'Ensure the WiFi hidden flag is a boolean or a string representation of a boolean.';

        return $exception;
    }
}

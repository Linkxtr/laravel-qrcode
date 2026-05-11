<?php

declare(strict_types=1);

use Linkxtr\QrCode\Exceptions\InvalidWiFiArgumentException;

covers(InvalidWiFiArgumentException::class);

test('invalidSsidValue sets correct error code and message', function (): void {
    $invalidWiFiArgumentException = InvalidWiFiArgumentException::invalidSsidValue('invalid_type');

    expect($invalidWiFiArgumentException)->toBeInstanceOf(InvalidWiFiArgumentException::class)
        ->and($invalidWiFiArgumentException->getErrorCode())->toBe('INVALID_WIFI_SSID_TYPE')
        ->and($invalidWiFiArgumentException->getMessage())->toBe('WiFi SSID must be a non-empty string. Provided type: invalid_type');

    $invalidWiFiArgumentException = InvalidWiFiArgumentException::invalidSsidValue('string');

    expect($invalidWiFiArgumentException)->toBeInstanceOf(InvalidWiFiArgumentException::class)
        ->and($invalidWiFiArgumentException->getErrorCode())->toBe('INVALID_WIFI_SSID_TYPE')
        ->and($invalidWiFiArgumentException->getMessage())->toBe('WiFi SSID must be a non-empty string. Provided type: empty string');
});

test('invalidEncryptionValue sets correct error code and message', function (): void {
    $invalidWiFiArgumentException = InvalidWiFiArgumentException::invalidEncryptionValue('invalid_type');

    expect($invalidWiFiArgumentException)->toBeInstanceOf(InvalidWiFiArgumentException::class)
        ->and($invalidWiFiArgumentException->getErrorCode())->toBe('INVALID_WIFI_ENCRYPTION')
        ->and($invalidWiFiArgumentException->getMessage())->toBe('WiFi encryption must be WEP, WPA, WPA2, WPA3 or NOPASS. Provided encryption: invalid_type');
});

test('passwordWithNopassEncryption sets correct error code and message', function (): void {
    $invalidWiFiArgumentException = InvalidWiFiArgumentException::passwordWithNopassEncryption();

    expect($invalidWiFiArgumentException)->toBeInstanceOf(InvalidWiFiArgumentException::class)
        ->and($invalidWiFiArgumentException->getErrorCode())->toBe('INVALID_WIFI_PASSWORD_WITH_NOPASS_ENCRYPTION')
        ->and($invalidWiFiArgumentException->getMessage())->toBe('WiFi password cannot be provided when encryption is NOPASS.');
});

test('invalidHiddenType sets correct error code and message', function (): void {
    $invalidWiFiArgumentException = InvalidWiFiArgumentException::invalidHiddenType('invalid_type');

    expect($invalidWiFiArgumentException)->toBeInstanceOf(InvalidWiFiArgumentException::class)
        ->and($invalidWiFiArgumentException->getErrorCode())->toBe('INVALID_WIFI_HIDDEN_TYPE')
        ->and($invalidWiFiArgumentException->getMessage())->toBe('WiFi hidden flag must be a boolean or a string representation of a boolean. Provided type: invalid_type');
});

test('missingArguments sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = InvalidWiFiArgumentException::missingArguments('message for exception');

    expect($invalidDataTypeArgumentException)->toBeInstanceOf(InvalidWiFiArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())->toBe('MISSING_ARGUMENTS')
        ->and($invalidDataTypeArgumentException->getMessage())->toBe('message for exception');
});

test('invalidArgument sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = InvalidWiFiArgumentException::invalidArgument('message for exception');

    expect($invalidDataTypeArgumentException)->toBeInstanceOf(InvalidWiFiArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())->toBe('INVALID_ARGUMENT')
        ->and($invalidDataTypeArgumentException->getMessage())->toBe('message for exception');
});

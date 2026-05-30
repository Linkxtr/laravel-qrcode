<?php

declare(strict_types=1);

use Linkxtr\QrCode\Exceptions\DataTypes\InvalidWhatsAppArgumentException;

covers(InvalidWhatsAppArgumentException::class);

test('invalidPhoneNumberType sets correct error code and message', function (): void {
    $invalidWhatsAppArgumentException = InvalidWhatsAppArgumentException::invalidPhoneNumberType('invalid_type');

    expect($invalidWhatsAppArgumentException)->toBeInstanceOf(InvalidWhatsAppArgumentException::class)
        ->and($invalidWhatsAppArgumentException->getErrorCode())->toBe('INVALID_PHONE_NUMBER_TYPE')
        ->and($invalidWhatsAppArgumentException->getMessage())->toBe('WhatsApp phone number must be a string or numeric value. Provided type: invalid_type');

    $invalidWhatsAppArgumentException = InvalidWhatsAppArgumentException::invalidPhoneNumberType('string');

    expect($invalidWhatsAppArgumentException)->toBeInstanceOf(InvalidWhatsAppArgumentException::class)
        ->and($invalidWhatsAppArgumentException->getErrorCode())->toBe('INVALID_PHONE_NUMBER_TYPE')
        ->and($invalidWhatsAppArgumentException->getMessage())->toBe('WhatsApp phone number must be a string or numeric value. Provided type: empty string');
});

test('invalidMessageType sets correct error code and message', function (): void {
    $invalidWhatsAppArgumentException = InvalidWhatsAppArgumentException::invalidMessageType('invalid_type');

    expect($invalidWhatsAppArgumentException)->toBeInstanceOf(InvalidWhatsAppArgumentException::class)
        ->and($invalidWhatsAppArgumentException->getErrorCode())->toBe('INVALID_MESSAGE_TYPE')
        ->and($invalidWhatsAppArgumentException->getMessage())->toBe('WhatsApp message must be a string. Provided type: invalid_type');
});

test('emptyPhoneNumber sets correct error code and message', function (): void {
    $invalidWhatsAppArgumentException = InvalidWhatsAppArgumentException::emptyPhoneNumber();

    expect($invalidWhatsAppArgumentException)->toBeInstanceOf(InvalidWhatsAppArgumentException::class)
        ->and($invalidWhatsAppArgumentException->getErrorCode())->toBe('EMPTY_PHONE_NUMBER')
        ->and($invalidWhatsAppArgumentException->getMessage())->toBe('WhatsApp phone number cannot be empty.');
});

test('missingArguments sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = InvalidWhatsAppArgumentException::missingArguments('test');

    expect($invalidDataTypeArgumentException)->toBeInstanceOf(InvalidWhatsAppArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())->toBe('MISSING_ARGUMENTS')
        ->and($invalidDataTypeArgumentException->getMessage())->toBe('test');
});

test('invalidArgument sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = InvalidWhatsAppArgumentException::invalidArgument('test');

    expect($invalidDataTypeArgumentException)->toBeInstanceOf(InvalidWhatsAppArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())->toBe('INVALID_ARGUMENT')
        ->and($invalidDataTypeArgumentException->getMessage())->toBe('test');
});

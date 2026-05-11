<?php

declare(strict_types=1);

use Linkxtr\QrCode\Exceptions\InvalidSMSArgumentException;

covers(InvalidSMSArgumentException::class);

test('invalidPhoneNumberType sets correct error code and message', function (): void {
    $invalidSMSArgumentException = InvalidSMSArgumentException::invalidPhoneNumberType('string');

    expect($invalidSMSArgumentException)->toBeInstanceOf(InvalidSMSArgumentException::class)
        ->and($invalidSMSArgumentException->getMessage())->toBe('Phone number must be a string or numeric value. Provided type: empty string')
        ->and($invalidSMSArgumentException->getErrorCode())->toBe('INVALID_PHONE_NUMBER_TYPE')
        ->and($invalidSMSArgumentException->getHelperMessage())->toBe('Ensure the phone number is a non-empty string or numeric value.');

    $invalidSMSArgumentException = InvalidSMSArgumentException::invalidPhoneNumberType('invalid_type');

    expect($invalidSMSArgumentException)->toBeInstanceOf(InvalidSMSArgumentException::class)
        ->and($invalidSMSArgumentException->getMessage())->toBe('Phone number must be a string or numeric value. Provided type: invalid_type');
});

test('invalidMessageType sets correct error code and message', function (): void {
    $invalidSMSArgumentException = InvalidSMSArgumentException::invalidMessageType('test');

    expect($invalidSMSArgumentException)->toBeInstanceOf(InvalidSMSArgumentException::class)
        ->and($invalidSMSArgumentException->getErrorCode())->toBe('INVALID_MESSAGE_TYPE')
        ->and($invalidSMSArgumentException->getHelperMessage())->toBe('Ensure the message is a non-empty string.');
});

test('missingArguments sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = InvalidSMSArgumentException::missingArguments('test');

    expect($invalidDataTypeArgumentException)->toBeInstanceOf(InvalidSMSArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())->toBe('MISSING_ARGUMENTS')
        ->and($invalidDataTypeArgumentException->getHelperMessage())->toBe('test');
});

test('invalidArgument sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = InvalidSMSArgumentException::invalidArgument('test');

    expect($invalidDataTypeArgumentException)->toBeInstanceOf(InvalidSMSArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())->toBe('INVALID_ARGUMENT')
        ->and($invalidDataTypeArgumentException->getHelperMessage())->toBe('test');
});

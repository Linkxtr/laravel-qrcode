<?php

declare(strict_types=1);

use Linkxtr\QrCode\Exceptions\DataTypes\InvalidPhoneNumberArgumentException;

covers(InvalidPhoneNumberArgumentException::class);

test('invalidPhoneNumberType sets correct error code and message', function (): void {
    $invalidPhoneNumberArgumentException = InvalidPhoneNumberArgumentException::invalidPhoneNumberType('string');

    expect($invalidPhoneNumberArgumentException)->toBeInstanceOf(InvalidPhoneNumberArgumentException::class)
        ->and($invalidPhoneNumberArgumentException->getMessage())->toBe('Phone number must be a string or numeric value. Provided type: empty string')
        ->and($invalidPhoneNumberArgumentException->getErrorCode())->toBe('INVALID_PHONE_NUMBER_TYPE')
        ->and($invalidPhoneNumberArgumentException->getHelperMessage())->toBe('Ensure the phone number is a non-empty string or numeric value.');

    $invalidPhoneNumberArgumentException = InvalidPhoneNumberArgumentException::invalidPhoneNumberType('invalid_type');

    expect($invalidPhoneNumberArgumentException)->toBeInstanceOf(InvalidPhoneNumberArgumentException::class)
        ->and($invalidPhoneNumberArgumentException->getMessage())->toBe('Phone number must be a string or numeric value. Provided type: invalid_type');
});

test('invalidPhoneNumberFormat sets correct error code and message', function (): void {
    $invalidPhoneNumberArgumentException = InvalidPhoneNumberArgumentException::invalidPhoneNumberFormat();

    expect($invalidPhoneNumberArgumentException)->toBeInstanceOf(InvalidPhoneNumberArgumentException::class)
        ->and($invalidPhoneNumberArgumentException->getErrorCode())->toBe('INVALID_PHONE_NUMBER_FORMAT')
        ->and($invalidPhoneNumberArgumentException->getHelperMessage())->toBe('Ensure the phone number contains only valid characters (digits, spaces, hyphens, parentheses, dots, and a leading plus).');
});

test('invalidPhoneNumberLength sets correct error code and message', function (): void {
    $invalidPhoneNumberArgumentException = InvalidPhoneNumberArgumentException::invalidPhoneNumberLength();

    expect($invalidPhoneNumberArgumentException)->toBeInstanceOf(InvalidPhoneNumberArgumentException::class)
        ->and($invalidPhoneNumberArgumentException->getErrorCode())->toBe('INVALID_PHONE_NUMBER_LENGTH')
        ->and($invalidPhoneNumberArgumentException->getHelperMessage())->toBe('Ensure the phone number has 1-15 digits and optionally starts with a plus sign.');
});

test('missingArguments sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = InvalidPhoneNumberArgumentException::missingArguments('test');

    expect($invalidDataTypeArgumentException)->toBeInstanceOf(InvalidPhoneNumberArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())->toBe('MISSING_ARGUMENTS')
        ->and($invalidDataTypeArgumentException->getHelperMessage())->toBe('test');
});

test('invalidArgument sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = InvalidPhoneNumberArgumentException::invalidArgument('test');

    expect($invalidDataTypeArgumentException)->toBeInstanceOf(InvalidPhoneNumberArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())->toBe('INVALID_ARGUMENT')
        ->and($invalidDataTypeArgumentException->getHelperMessage())->toBe('test');
});

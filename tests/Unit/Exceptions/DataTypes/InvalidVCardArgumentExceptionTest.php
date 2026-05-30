<?php

declare(strict_types=1);

use Linkxtr\QrCode\Exceptions\DataTypes\InvalidVCardArgumentException;

covers(InvalidVCardArgumentException::class);

test('invalidNameType sets correct error code and message', function (): void {
    $invalidVCardArgumentException = InvalidVCardArgumentException::invalidNameType('string');

    expect($invalidVCardArgumentException)->toBeInstanceOf(InvalidVCardArgumentException::class)
        ->and($invalidVCardArgumentException->getErrorCode())->toBe('INVALID_VCARD_NAME')
        ->and($invalidVCardArgumentException->getMessage())->toBe('VCard name must be a non-empty string. Provided type: empty string');

    $invalidVCardArgumentException = InvalidVCardArgumentException::invalidNameType('invalid_type');

    expect($invalidVCardArgumentException)->toBeInstanceOf(InvalidVCardArgumentException::class)
        ->and($invalidVCardArgumentException->getErrorCode())->toBe('INVALID_VCARD_NAME')
        ->and($invalidVCardArgumentException->getMessage())->toBe('VCard name must be a non-empty string. Provided type: invalid_type');
});

test('missingArguments sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = InvalidVCardArgumentException::missingArguments('vcard');

    expect($invalidDataTypeArgumentException)->toBeInstanceOf(InvalidVCardArgumentException::class);
    expect($invalidDataTypeArgumentException->getErrorCode())->toBe('MISSING_ARGUMENTS');
    expect($invalidDataTypeArgumentException->getHelperMessage())->toBe('vcard');
});

test('invalidArgument sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = InvalidVCardArgumentException::invalidArgument('vcard');

    expect($invalidDataTypeArgumentException)->toBeInstanceOf(InvalidVCardArgumentException::class);
    expect($invalidDataTypeArgumentException->getErrorCode())->toBe('INVALID_ARGUMENT');
    expect($invalidDataTypeArgumentException->getHelperMessage())->toBe('vcard');
});

<?php

declare(strict_types=1);

use Linkxtr\QrCode\Exceptions\InvalidBTCArgumentException;

covers(InvalidBTCArgumentException::class);

test('invalidAddress sets correct error code and message', function (): void {
    $invalidBTCArgumentException = InvalidBTCArgumentException::invalidAddress('string');

    expect($invalidBTCArgumentException)
        ->toBeInstanceOf(InvalidBTCArgumentException::class)
        ->and($invalidBTCArgumentException->getMessage())->toBe('Bitcoin address must be a non-empty string. Provided type: empty string')
        ->and($invalidBTCArgumentException->getErrorCode())
        ->toBe('INVALID_BITCOIN_ADDRESS')
        ->and($invalidBTCArgumentException->getHelperMessage())
        ->toBe('Ensure the address is a non-empty string. It should follow valid Bitcoin address formats (e.g., starts with 1, 3, or bc1).');

    $invalidBTCArgumentException = InvalidBTCArgumentException::invalidAddress('invalid_type');

    expect($invalidBTCArgumentException)->toBeInstanceOf(InvalidBTCArgumentException::class)
        ->and($invalidBTCArgumentException->getMessage())->toBe('Bitcoin address must be a non-empty string. Provided type: invalid_type');
});

test('invalidAmount sets correct error code and message', function (): void {
    $invalidBTCArgumentException = InvalidBTCArgumentException::invalidAmount('test');

    expect($invalidBTCArgumentException)
        ->toBeInstanceOf(InvalidBTCArgumentException::class)
        ->and($invalidBTCArgumentException->getErrorCode())
        ->toBe('INVALID_BITCOIN_AMOUNT')
        ->and($invalidBTCArgumentException->getHelperMessage())
        ->toBe('Bitcoin amounts must be valid, non-negative numeric strings. Small amounts must be passed as strings to avoid scientific notation loss.');
});

test('invalidAmountType sets correct error code and message', function (): void {
    $invalidBTCArgumentException = InvalidBTCArgumentException::invalidAmountType('test');

    expect($invalidBTCArgumentException)
        ->toBeInstanceOf(InvalidBTCArgumentException::class)
        ->and($invalidBTCArgumentException->getErrorCode())
        ->toBe('INVALID_BITCOIN_AMOUNT')
        ->and($invalidBTCArgumentException->getHelperMessage())
        ->toBe('Bitcoin amounts must be valid, non-negative numeric strings. Small amounts must be passed as strings to avoid scientific notation loss.');
});

test('missingArguments sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = InvalidBTCArgumentException::missingArguments('test');

    expect($invalidDataTypeArgumentException)
        ->toBeInstanceOf(InvalidBTCArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())
        ->toBe('MISSING_ARGUMENTS')
        ->and($invalidDataTypeArgumentException->getHelperMessage())
        ->toBe('test');
});

test('invalidArgument sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = InvalidBTCArgumentException::invalidArgument('test');

    expect($invalidDataTypeArgumentException)
        ->toBeInstanceOf(InvalidBTCArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())
        ->toBe('INVALID_ARGUMENT')
        ->and($invalidDataTypeArgumentException->getHelperMessage())
        ->toBe('test');
});

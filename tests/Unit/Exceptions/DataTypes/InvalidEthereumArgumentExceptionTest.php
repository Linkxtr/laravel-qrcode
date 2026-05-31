<?php

declare(strict_types=1);

use Linkxtr\QrCode\Exceptions\DataTypes\InvalidEthereumArgumentException;

covers(InvalidEthereumArgumentException::class);

test('invalidAddress sets correct error code and message', function (): void {
    $invalidEthereumArgumentException = InvalidEthereumArgumentException::invalidAddress('string');

    expect($invalidEthereumArgumentException)->toBeInstanceOf(InvalidEthereumArgumentException::class)
        ->and($invalidEthereumArgumentException->getMessage())->toBe('Ethereum address must be a non-empty string. Provided type: empty string')
        ->and($invalidEthereumArgumentException->getErrorCode())->toBe('INVALID_ETHEREUM_ADDRESS')
        ->and($invalidEthereumArgumentException->getHelperMessage())->toBe('Ensure the address is a non-empty string. It should follow valid Ethereum address formats (e.g., starts with 0x followed by 40 hexadecimal characters).');

    $invalidEthereumArgumentException = InvalidEthereumArgumentException::invalidAddress('invalid_type');

    expect($invalidEthereumArgumentException)->toBeInstanceOf(InvalidEthereumArgumentException::class)
        ->and($invalidEthereumArgumentException->getMessage())->toBe('Ethereum address must be a non-empty string. Provided type: invalid_type');
});

test('invalidAmount sets correct error code and message', function (): void {
    $invalidEthereumArgumentException = InvalidEthereumArgumentException::invalidAmount('test');

    expect($invalidEthereumArgumentException)->toBeInstanceOf(InvalidEthereumArgumentException::class)
        ->and($invalidEthereumArgumentException->getErrorCode())->toBe('INVALID_ETHEREUM_AMOUNT')
        ->and($invalidEthereumArgumentException->getHelperMessage())->toBe('Ethereum amounts must be valid, non-negative numeric strings. Small amounts must be passed as strings to avoid scientific notation loss.');
});

test('invalidAmountType sets correct error code and message', function (): void {
    $invalidEthereumArgumentException = InvalidEthereumArgumentException::invalidAmountType('test');

    expect($invalidEthereumArgumentException)->toBeInstanceOf(InvalidEthereumArgumentException::class)
        ->and($invalidEthereumArgumentException->getErrorCode())->toBe('INVALID_ETHEREUM_AMOUNT')
        ->and($invalidEthereumArgumentException->getHelperMessage())->toBe('Ethereum amounts must be valid, non-negative numeric strings. Small amounts must be passed as strings to avoid scientific notation loss.');
});

test('missingArguments sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = InvalidEthereumArgumentException::missingArguments('test');

    expect($invalidDataTypeArgumentException)->toBeInstanceOf(InvalidEthereumArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())->toBe('MISSING_ARGUMENTS')
        ->and($invalidDataTypeArgumentException->getHelperMessage())->toBe('test');
});

test('invalidArgument sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = InvalidEthereumArgumentException::invalidArgument('test');

    expect($invalidDataTypeArgumentException)->toBeInstanceOf(InvalidEthereumArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())->toBe('INVALID_ARGUMENT')
        ->and($invalidDataTypeArgumentException->getHelperMessage())->toBe('test');
});

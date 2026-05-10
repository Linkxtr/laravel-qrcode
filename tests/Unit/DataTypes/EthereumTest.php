<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\Ethereum;
use Linkxtr\QrCode\Exceptions\InvalidEthereumArgumentException;
use Linkxtr\QrCode\Exceptions\UninitializedDataTypeException;

covers(Ethereum::class);

it('throws exception if rendered before creation', function (): void {
    expect(fn (): string => (string) new Ethereum)
        ->toThrow(UninitializedDataTypeException::class, 'Ethereum must be initialized via create() before rendering.');
});

test('it throws exception if address is missing', function (): void {
    $eth = new Ethereum;
    expect(fn () => $eth->create([]))
        ->toThrow(InvalidEthereumArgumentException::class, 'Ethereum address is required.');
});

test('it throws exception if address is not a string', function (): void {
    $eth = new Ethereum;
    expect(fn () => $eth->create([12345]))
        ->toThrow(InvalidEthereumArgumentException::class, 'Ethereum address must be a non-empty string.');
});

test('it throws exception if amount is not numeric', function (): void {
    $eth = new Ethereum;
    expect(fn () => $eth->create(['0x71C7656EC7ab88b098defB751B7401B5f6d8976F', 'not-a-number']))
        ->toThrow(InvalidEthereumArgumentException::class, 'Ethereum amount must be a valid, non-negative numeric string without scientific notation. Provided value: not-a-number');

    $eth = new Ethereum;
    expect(fn () => $eth->create(['0x71C7656EC7ab88b098defB751B7401B5f6d8976F', []]))
        ->toThrow(InvalidEthereumArgumentException::class, 'Ethereum amount must be a positive decimal string. Provided type: array');
});

test('it strictly bounds negative amounts to kill boundary mutants', function (): void {
    $eth = new Ethereum;
    expect(fn () => $eth->create(['0x71C7656EC7ab88b098defB751B7401B5f6d8976F', -0.5]))
        ->toThrow(InvalidEthereumArgumentException::class, 'Ethereum amount must be a valid, non-negative numeric string without scientific notation. Provided value: -0.5');
});

test('it generates basic ethereum uri without amount', function (): void {
    $eth = new Ethereum;
    $eth->create(['0x71C7656EC7ab88b098defB751B7401B5f6d8976F']);

    expect((string) $eth)->toBe('ethereum:0x71C7656EC7ab88b098defB751B7401B5f6d8976F');
});

test('it generates full ethereum uri with positive amount', function (): void {
    $eth = new Ethereum;
    $eth->create(['0x71C7656EC7ab88b098defB751B7401B5f6d8976F', 1.5]);

    expect((string) $eth)->toBe('ethereum:0x71C7656EC7ab88b098defB751B7401B5f6d8976F?amount=1.5');
});

test('it preserves exact precision for highly granular 18-decimal amounts and trims spaces', function (): void {
    $eth = new Ethereum;

    $eth->create(['  0x1234567890abcdef  ', '0']);

    expect((string) $eth)->toBe('ethereum:0x1234567890abcdef?amount=0');

    $extremePrecision = '0.000000000000000001';
    $eth->create(['0x1234567890abcdef', $extremePrecision]);

    expect((string) $eth)->toBe('ethereum:0x1234567890abcdef?amount=0.000000000000000001');
});

test('it mathematically rejects empty addresses', function (): void {
    $eth = new Ethereum;

    expect(fn () => $eth->create(['   ']))
        ->toThrow(InvalidEthereumArgumentException::class, 'Ethereum address must be a non-empty string.');

    expect(fn () => $eth->create(['']))
        ->toThrow(InvalidEthereumArgumentException::class, 'Ethereum address must be a non-empty string.');
});

test('it mathematically rejects negatives and scientific notation to comply with EIP-681', function (): void {
    $eth = new Ethereum;

    expect(fn () => $eth->create(['0x123', '-1']))
        ->toThrow(InvalidEthereumArgumentException::class, 'Ethereum amount must be a valid, non-negative numeric string without scientific notation.');

    expect(fn () => $eth->create(['0x123', '1e-18']))
        ->toThrow(InvalidEthereumArgumentException::class, 'Ethereum amount must be a valid, non-negative numeric string without scientific notation.');

    expect(fn () => $eth->create(['0x123', true]))
        ->toThrow(InvalidEthereumArgumentException::class, 'Ethereum amount must be a positive decimal string. Provided type: boolean');
});

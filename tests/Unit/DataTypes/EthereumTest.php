<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\Ethereum;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidEthereumArgumentException;

covers(Ethereum::class);

test('it throws exception if address is missing', function (): void {
    expect(fn (): Ethereum => new Ethereum)
        ->toThrow(TypeError::class);
});

test('it throws exception if address is not a string', function (): void {
    expect(fn (): Ethereum => new Ethereum(12345))
        ->toThrow(TypeError::class);

    expect(fn (): Ethereum => new Ethereum(''))
        ->toThrow(InvalidEthereumArgumentException::class, 'Ethereum address must be a non-empty string.');
});

test('it throws exception if amount is not numeric', function (): void {
    expect(fn (): Ethereum => new Ethereum('0x71C7656EC7ab88b098defB751B7401B5f6d8976F', 'not-a-number'))
        ->toThrow(InvalidEthereumArgumentException::class, 'Ethereum amount must be a valid, non-negative numeric string without scientific notation. Provided value: not-a-number');

    expect(fn (): Ethereum => new Ethereum('0x71C7656EC7ab88b098defB751B7401B5f6d8976F', []))
        ->toThrow(TypeError::class);
});

test('it strictly bounds negative amounts to kill boundary mutants', function (): void {
    expect(fn (): Ethereum => new Ethereum('0x71C7656EC7ab88b098defB751B7401B5f6d8976F', -0.5))
        ->toThrow(InvalidEthereumArgumentException::class, 'Ethereum amount must be a valid, non-negative numeric string without scientific notation. Provided value: -0.5');
});

test('it generates basic ethereum uri without amount', function (): void {
    $eth = new Ethereum('0x71C7656EC7ab88b098defB751B7401B5f6d8976F');

    expect((string) $eth)->toBe('ethereum:0x71C7656EC7ab88b098defB751B7401B5f6d8976F');
});

test('it generates full ethereum uri with positive amount', function (): void {
    $eth = new Ethereum('0x71C7656EC7ab88b098defB751B7401B5f6d8976F', 1.5);

    expect((string) $eth)->toBe('ethereum:0x71C7656EC7ab88b098defB751B7401B5f6d8976F?amount=1.5');
});

test('it preserves exact precision for highly granular 18-decimal amounts and trims spaces', function (): void {
    $eth1 = new Ethereum('  0x1234567890abcdef  ', '0');

    expect((string) $eth1)->toBe('ethereum:0x1234567890abcdef?amount=0');

    $extremePrecision = '0.000000000000000001';
    $eth2 = new Ethereum('0x1234567890abcdef', $extremePrecision);

    expect((string) $eth2)->toBe('ethereum:0x1234567890abcdef?amount=0.000000000000000001');
});

test('it mathematically rejects empty addresses', function (): void {
    expect(fn (): Ethereum => new Ethereum('   '))
        ->toThrow(InvalidEthereumArgumentException::class, 'Ethereum address must be a non-empty string.');

    expect(fn (): Ethereum => new Ethereum(''))
        ->toThrow(InvalidEthereumArgumentException::class, 'Ethereum address must be a non-empty string.');
});

test('it mathematically rejects negatives and scientific notation to comply with EIP-681', function (): void {
    expect(fn (): Ethereum => new Ethereum('0x123', '-1'))
        ->toThrow(InvalidEthereumArgumentException::class, 'Ethereum amount must be a valid, non-negative numeric string without scientific notation.');

    expect(fn (): Ethereum => new Ethereum('0x123', '1e-18'))
        ->toThrow(InvalidEthereumArgumentException::class, 'Ethereum amount must be a valid, non-negative numeric string without scientific notation.');
});

<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\Ethereum;

covers(Ethereum::class);

test('it throws exception if address is missing', function () {
    $eth = new Ethereum;
    expect(fn () => $eth->create([]))
        ->toThrow(InvalidArgumentException::class, 'Ethereum address is required.');
});

test('it throws exception if address is not a string', function () {
    $eth = new Ethereum;
    expect(fn () => $eth->create([12345]))
        ->toThrow(InvalidArgumentException::class, 'Ethereum address must be a string.');
});

test('it throws exception if amount is not numeric', function () {
    $eth = new Ethereum;
    expect(fn () => $eth->create(['0x71C7656EC7ab88b098defB751B7401B5f6d8976F', 'not-a-number']))
        ->toThrow(InvalidArgumentException::class, 'Ethereum amount must be a numeric value.');
});

test('it strictly bounds negative amounts to kill boundary mutants', function () {
    $eth = new Ethereum;
    expect(fn () => $eth->create(['0x71C7656EC7ab88b098defB751B7401B5f6d8976F', -0.5]))
        ->toThrow(InvalidArgumentException::class, 'Ethereum amount must be non-negative.');
});

test('it generates basic ethereum uri without amount', function () {
    $eth = new Ethereum;
    $eth->create(['0x71C7656EC7ab88b098defB751B7401B5f6d8976F']);

    expect((string) $eth)->toBe('ethereum:0x71C7656EC7ab88b098defB751B7401B5f6d8976F');
});

test('it generates full ethereum uri with positive amount', function () {
    $eth = new Ethereum;
    $eth->create(['0x71C7656EC7ab88b098defB751B7401B5f6d8976F', 1.5]);

    expect((string) $eth)->toBe('ethereum:0x71C7656EC7ab88b098defB751B7401B5f6d8976F?amount=1.5');
});

test('it safely casts numeric strings to float and preserves zero amounts', function () {
    $eth = new Ethereum;
    $eth->create(['0x71C7656EC7ab88b098defB751B7401B5f6d8976F', '0']);

    expect((string) $eth)->toBe('ethereum:0x71C7656EC7ab88b098defB751B7401B5f6d8976F?amount=0');
});

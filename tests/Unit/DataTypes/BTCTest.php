<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\BTC;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidBTCArgumentException;

covers(BTC::class);

test('it throws exception if address or amount is missing', function (): void {
    expect(fn (): BTC => new BTC(amount: 1.5))
        ->toThrow(TypeError::class);
});

test('it throws exception if address is not a string', function (): void {
    expect(fn (): BTC => new BTC(12345, 1.5))
        ->toThrow(TypeError::class);
});

test('it throws exception if amount is not numeric', function (): void {
    expect(fn (): BTC => new BTC('1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa', 'not-a-number'))
        ->toThrow(InvalidBTCArgumentException::class, 'Bitcoin amount must be a positive decimal string. Provided value: not-a-number');
    expect(fn (): BTC => new BTC('1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa', [1.5]))
        ->toThrow(TypeError::class);
});

test('it throws exception if amount is negative', function (): void {
    expect(fn (): BTC => new BTC('1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa', -0.5))
        ->toThrow(InvalidBTCArgumentException::class, 'Bitcoin amount must be a positive decimal string. Provided value: -0.5');
});

test('it generates basic bitcoin uri with positive amount', function (): void {
    $btc = new BTC('1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa', 1.5);

    expect((string) $btc)->toBe('bitcoin:1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa?amount=1.5');
});

test('it strictly allows zero amount and preserves it in query', function (): void {
    $btc = new BTC('1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa', 0);

    expect((string) $btc)->toContain('amount=0');
});

test('it successfully appends optional parameters with RFC3986 encoding', function (): void {
    $btc = new BTC(
        '1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa',
        1.5,
        label: 'Satoshi Nakamoto',
        message: 'Donation for project',
        returnAddress: '1ReturnAddressHere'
    );

    $result = (string) $btc;

    expect($result)->toContain('label=Satoshi%20Nakamoto')
        ->and($result)->toContain('message=Donation%20for%20project')
        ->and($result)->toContain('r=1ReturnAddressHere');
});

test('it safely casts numeric strings to float for the amount', function (): void {
    $btc = new BTC('1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa', '1.5');

    expect((string) $btc)->toBe('bitcoin:1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa?amount=1.5');

    $btc2 = new BTC('1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa', 1.5);

    expect((string) $btc2)->toBe('bitcoin:1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa?amount=1.5');
});

test('it throws an exception if amount is converted to scientific notation to prevent BIP21 wallet failures', function (): void {
    expect(fn (): BTC => new BTC('1Address', 0.00000001))
        ->toThrow(InvalidBTCArgumentException::class, 'Bitcoin amount must be a positive decimal string');

    $btcSafe = new BTC('1Address', '0.00000001');

    expect((string) $btcSafe)->toContain('amount=0.00000001');
});

test('it mathematically enforces format', function (): void {
    expect(fn (): BTC => new BTC('1Address', '-1.5'))
        ->toThrow(InvalidBTCArgumentException::class);

    expect(fn (): BTC => new BTC('1Address', '1.5abc'))
        ->toThrow(InvalidBTCArgumentException::class);
});

test('it mathematically rejects empty addresses to prevent broken URIs', function (): void {
    expect(fn (): BTC => new BTC('   ', 0.5))
        ->toThrow(InvalidBTCArgumentException::class, 'Bitcoin address must be a non-empty string.');

    expect(fn (): BTC => new BTC('', '0.5'))
        ->toThrow(InvalidBTCArgumentException::class, 'Bitcoin address must be a non-empty string.');
});

it('trims whitespace from the bitcoin address', function (): void {
    $btc = new BTC('   1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa   ', '1.0');

    expect((string) $btc)->toBe('bitcoin:1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa?amount=1.0');
});

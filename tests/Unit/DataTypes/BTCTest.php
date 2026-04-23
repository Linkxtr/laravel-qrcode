<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\BTC;

covers(BTC::class);

test('it throws exception if rendered before creation', function () {
    $btc = new BTC;
    expect(fn () => (string) $btc)
        ->toThrow(LogicException::class, 'BTC must be initialized via create() before rendering.');
});

test('it throws exception if address or amount is missing', function () {
    $btc = new BTC;
    expect(fn () => $btc->create(['1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa']))
        ->toThrow(InvalidArgumentException::class, 'Bitcoin address and amount are required.');
});

test('it throws exception if address is not a string', function () {
    $btc = new BTC;
    expect(fn () => $btc->create([12345, 1.5]))
        ->toThrow(InvalidArgumentException::class, 'Bitcoin address must be a non-empty string.');
});

test('it throws exception if amount is not numeric', function () {
    $btc = new BTC;
    expect(fn () => $btc->create(['1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa', 'not-a-number']))
        ->toThrow(InvalidArgumentException::class, 'Bitcoin amount must be a positive decimal string. Small amounts must be passed as strings to avoid scientific notation loss.');
});

test('it throws exception if amount is negative', function () {
    $btc = new BTC;
    expect(fn () => $btc->create(['1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa', -0.5]))
        ->toThrow(InvalidArgumentException::class, 'Bitcoin amount must be a positive decimal string. Small amounts must be passed as strings to avoid scientific notation loss.');
});

test('it generates basic bitcoin uri with positive amount', function () {
    $btc = new BTC;
    $btc->create(['1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa', 1.5]);

    expect((string) $btc)->toBe('bitcoin:1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa?amount=1.5');
});

test('it strictly allows zero amount and preserves it in query', function () {
    $btc = new BTC;
    $btc->create(['1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa', 0]);

    expect((string) $btc)->toContain('amount=0');
});

test('it successfully appends optional parameters with RFC3986 encoding', function () {
    $btc = new BTC;
    $btc->create(['1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa', 1.5, [
        'label' => 'Satoshi Nakamoto',
        'message' => 'Donation for project',
        'returnAddress' => '1ReturnAddressHere',
    ]]);

    $result = (string) $btc;

    expect($result)->toContain('label=Satoshi%20Nakamoto')
        ->and($result)->toContain('message=Donation%20for%20project')
        ->and($result)->toContain('r=1ReturnAddressHere');
});

test('it ignores optional parameters if they are not strings', function () {
    $btc = new BTC;
    $btc->create(['1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa', '1.5', [
        'label' => 123,
        'message' => ['invalid'],
        'returnAddress' => 456,
    ]]);

    expect((string) $btc)->toBe('bitcoin:1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa?amount=1.5');
});

test('it safely casts numeric strings to float for the amount', function () {
    $btc = new BTC;
    $btc->create(['1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa', '1.5']);

    expect((string) $btc)->toBe('bitcoin:1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa?amount=1.5');

    $btc2 = new BTC;
    $btc2->create(['1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa', 1.5]);
    expect((string) $btc2)->toBe('bitcoin:1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa?amount=1.5');
});

test('it throws an exception if amount is converted to scientific notation to prevent BIP21 wallet failures', function () {
    $btc = new BTC;

    expect(fn () => $btc->create(['1Address', 0.00000001]))
        ->toThrow(InvalidArgumentException::class, 'Bitcoin amount must be a positive decimal string');
    $btcSafe = new BTC;
    $btcSafe->create(['1Address', '0.00000001']);
    expect((string) $btcSafe)->toContain('amount=0.00000001');
});

test('it mathematically enforces format', function () {
    $btc = new BTC;

    expect(fn () => $btc->create(['1Address', '-1.5']))
        ->toThrow(InvalidArgumentException::class);

    expect(fn () => $btc->create(['1Address', '1.5abc']))
        ->toThrow(InvalidArgumentException::class);

    expect(fn () => $btc->create(['1Address', ['amount' => 1.5]]))
        ->toThrow(InvalidArgumentException::class);
});

test('it mathematically rejects empty addresses to prevent broken URIs', function () {
    $btc = new BTC;

    expect(fn () => $btc->create(['   ', '0.5']))
        ->toThrow(InvalidArgumentException::class, 'Bitcoin address must be a non-empty string.');

    expect(fn () => $btc->create(['', '0.5']))
        ->toThrow(InvalidArgumentException::class, 'Bitcoin address must be a non-empty string.');
});

it('trims whitespace from the bitcoin address', function () {
    $btc = new BTC;
    $btc->create(['   1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa   ', '1.0']);

    expect((string) $btc)->toBe('bitcoin:1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa?amount=1.0');
});

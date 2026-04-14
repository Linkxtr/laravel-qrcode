<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\BTC;

covers(BTC::class);

test('it throws exception if address or amount is missing', function () {
    $btc = new BTC;
    expect(fn () => $btc->create(['1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa']))
        ->toThrow(InvalidArgumentException::class, 'Bitcoin address and amount are required.');
});

test('it throws exception if address is not a string', function () {
    $btc = new BTC;
    expect(fn () => $btc->create([12345, 1.5]))
        ->toThrow(InvalidArgumentException::class, 'Bitcoin address must be a string.');
});

test('it throws exception if amount is not numeric', function () {
    $btc = new BTC;
    expect(fn () => $btc->create(['1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa', 'not-a-number']))
        ->toThrow(InvalidArgumentException::class, 'Bitcoin amount must be a numeric value.');
});

test('it throws exception if amount is negative', function () {
    $btc = new BTC;
    expect(fn () => $btc->create(['1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa', -0.5]))
        ->toThrow(InvalidArgumentException::class, 'Bitcoin amount must be non-negative.');
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
    $btc->create(['1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa', 1.5, [
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
});

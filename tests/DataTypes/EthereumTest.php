<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\Ethereum;

covers(Ethereum::class);

beforeEach(function () {
    $this->ethereum = new Ethereum;
});

it('should generate a valid Ethereum QR code with only address', function () {
    $this->ethereum->create(['0x742d35Cc6634C0532925a3b8D2b2CE1e5bfb043d']);
    expect(strval($this->ethereum))->toBe('ethereum:0x742d35Cc6634C0532925a3b8D2b2CE1e5bfb043d');
});

it('should generate a valid Ethereum QR code with amount', function () {
    $this->ethereum->create(['0x742d35Cc6634C0532925a3b8D2b2CE1e5bfb043d', 1.5]);
    expect(strval($this->ethereum))->toBe('ethereum:0x742d35Cc6634C0532925a3b8D2b2CE1e5bfb043d?value=1.5');
});

it('throws an exception when Ethereum address is missing', function () {
    expect(fn () => $this->ethereum->create([]))
        ->toThrow(InvalidArgumentException::class, 'Ethereum address is required.');
});

it('throws an exception when Ethereum address is not a string', function () {
    expect(fn () => $this->ethereum->create([123]))
        ->toThrow(InvalidArgumentException::class, 'Ethereum address must be a string.');
});

it('throws an exception when Ethereum amount is not numeric', function () {
    expect(fn () => $this->ethereum->create(['0x123', 'invalid']))
        ->toThrow(InvalidArgumentException::class, 'Ethereum amount must be a numeric value.');
});

it('throws an exception when Ethereum amount is negative', function () {
    expect(fn () => $this->ethereum->create(['0x123', -1.5]))
        ->toThrow(InvalidArgumentException::class, 'Ethereum amount must be non-negative.');
});

it('clears amount when recreating with address only', function () {
    $this->ethereum->create(['0x742d35Cc6634C0532925a3b8D2b2CE1e5bfb043d', 1.5]);
    $this->ethereum->create(['0x1111111111111111111111111111111111111111']);
    expect(strval($this->ethereum))->toBe('ethereum:0x1111111111111111111111111111111111111111');
});

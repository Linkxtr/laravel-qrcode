<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\SMS;

covers(SMS::class);

beforeEach(function () {
    $this->sms = new SMS;
});

it('should generate a valid SMS QR code', function () {
    $this->sms->create(['555-555-5555']);
    expect(strval($this->sms))->toBe('SMSTO:555-555-5555');
});

it('should generate a valid SMS QR code with message', function () {
    $this->sms->create(['555-555-5555', 'message']);
    expect(strval($this->sms))->toBe('SMSTO:555-555-5555:message');
});

it('should generate a valid SMS QR code with message and without phone number', function () {
    $this->sms->create([null, 'message']);
    expect(strval($this->sms))->toBe('SMSTO::message');
});

it('throws an exception when SMS address or message is missing', function () {
    expect(fn () => $this->sms->create([]))
        ->toThrow(InvalidArgumentException::class, 'Either SMS address or message is required.');
    expect(fn () => $this->sms->create([null, null]))
        ->toThrow(InvalidArgumentException::class, 'Either SMS address or message is required.');
    expect(fn () => $this->sms->create(['']))
        ->toThrow(InvalidArgumentException::class, 'SMS address cannot be empty.');
});

it('throws an exception when SMS address is not a string', function () {
    expect(fn () => $this->sms->create([123]))
        ->toThrow(InvalidArgumentException::class, 'SMS address must be a string.');
});

it('throws an exception when SMS address is not a valid SMS address', function () {
    expect(fn () => $this->sms->create(['invalid']))
        ->toThrow(InvalidArgumentException::class, 'Invalid phone number format. Must be 1-15 digits, optionally starting with +');
});

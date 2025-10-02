<?php

namespace Linkxtr\QrCode\Tests\DataTypes;

use Linkxtr\QrCode\DataTypes\SMS;
use InvalidArgumentException;

beforeEach(function () {
    $this->sms = new SMS();
});

it('should generate a valid SMS QR code', function () {
    $this->sms->create(['555-555-5555']);
    expect(strval($this->sms))->toBe('sms:555-555-5555');
});

it('should generate a valid SMS QR code with message', function () {
    $this->sms->create(['555-555-5555', 'message']);
    expect(strval($this->sms))->toBe('sms:555-555-5555&body=message');
});

it('should generate a valid SMS QR code with message and without phone number', function () {
    $this->sms->create([null, 'message']);
    expect(strval($this->sms))->toBe('sms:&body=message');
});

it('throws an exception when SMS address or message is missing', function () {
    expect(fn () => $this->sms->create([]))
        ->toThrow(InvalidArgumentException::class, 'Either SMS address or message is required.');
});

it('throws an exception when SMS address is not a string', function () {
    expect(fn () => $this->sms->create([123]))
        ->toThrow(InvalidArgumentException::class, 'SMS address must be a string.');
});

it('throws an exception when SMS address is not a valid SMS address', function () {
    expect(fn () => $this->sms->create(['invalid']))
        ->toThrow(InvalidArgumentException::class, 'Invalid SMS address format.');
});

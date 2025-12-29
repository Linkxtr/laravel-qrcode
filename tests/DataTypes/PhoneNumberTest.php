<?php

use Linkxtr\QrCode\DataTypes\PhoneNumber;

covers(PhoneNumber::class);

beforeEach(function () {
    $this->phoneNumber = new PhoneNumber;
});

it('should generate a valid phone number QR code', function () {
    $this->phoneNumber->create(['+1234567890']);
    expect(strval($this->phoneNumber))->toBe('tel:+1234567890');
});

it('throws an exception when phone number is missing', function () {
    expect(fn () => $this->phoneNumber->create([]))
        ->toThrow(InvalidArgumentException::class, 'Phone number is required.');
});

it('throws an exception when phone number is not a string', function () {
    expect(fn () => $this->phoneNumber->create([123]))
        ->toThrow(InvalidArgumentException::class, 'Phone number must be a string.');
});

it('throws an exception when phone number is not a valid phone number', function () {
    expect(fn () => $this->phoneNumber->create(['invalid']))
        ->toThrow(InvalidArgumentException::class, 'Invalid phone number format. Must be 1-15 digits, optionally starting with +');
});

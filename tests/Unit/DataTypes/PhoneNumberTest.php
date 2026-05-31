<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\PhoneNumber;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidPhoneNumberArgumentException;

covers(PhoneNumber::class);

test('it throws exception if phone number is missing', function (): void {
    expect(fn (): PhoneNumber => new PhoneNumber)
        ->toThrow(TypeError::class);
});

test('it throws exception if phone number is an invalid type', function (): void {
    expect(fn (): PhoneNumber => new PhoneNumber(['invalid_array_payload']))
        ->toThrow(TypeError::class);
});

test('it throws exception if phone number is an empty string', function (): void {
    expect(fn (): PhoneNumber => new PhoneNumber(''))
        ->toThrow(InvalidPhoneNumberArgumentException::class);
});

test('it formats string phone numbers correctly and strips spaces to kill formatting mutants', function (): void {
    $phone = new PhoneNumber('+1 555-123 4567');

    expect((string) $phone)->toBe('tel:+15551234567');
});

test('it safely casts numeric phone numbers to string to kill type logic mutants', function (): void {
    $phone = new PhoneNumber(15551234567);

    expect((string) $phone)->toBe('tel:15551234567');
});

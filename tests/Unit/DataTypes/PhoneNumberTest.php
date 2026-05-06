<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\PhoneNumber;

covers(PhoneNumber::class);

test('it throws exception if phone number is missing', function (): void {
    $phone = new PhoneNumber;
    expect(fn () => $phone->create([]))
        ->toThrow(InvalidArgumentException::class, 'Phone number is required.');
});

test('it throws exception if phone number is an invalid type', function (): void {
    $phone = new PhoneNumber;
    expect(fn () => $phone->create([['invalid array']]))
        ->toThrow(InvalidArgumentException::class, 'Phone number must be a string or numeric value.');
});

test('it throws exception if phone number is an empty string', function (): void {
    $phone = new PhoneNumber;
    expect(fn () => $phone->create(['']))
        ->toThrow(InvalidArgumentException::class, 'Phone number contains invalid characters. Only digits, spaces, hyphens, parentheses, dots, and a leading plus are allowed.');
});

test('it formats string phone numbers correctly and strips spaces to kill string mutants', function (): void {
    $phone = new PhoneNumber;

    $phone->create(['+1 555-123 4567']);

    expect((string) $phone)->toBe('tel:+15551234567');
});

test('it safely casts numeric phone numbers to string to kill type logic mutants', function (): void {
    $phone = new PhoneNumber;

    $phone->create([15551234567]);

    expect((string) $phone)->toBe('tel:15551234567');
});

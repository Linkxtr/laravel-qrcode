<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\SMS;

covers(SMS::class);

test('it throws exception if phone number is missing', function (): void {
    expect(fn (): SMS => new SMS)
        ->toThrow(TypeError::class);
});

test('it throws exception if phone number is an invalid type', function (): void {
    expect(fn (): SMS => new SMS(['invalid array']))
        ->toThrow(TypeError::class);
});

test('it throws exception if message is not a string', function (): void {
    expect(fn (): SMS => new SMS('+15551234567', 12345))
        ->toThrow(TypeError::class);
});

test('it safely casts numeric phone numbers to string to kill type logic mutants', function (): void {
    $sms = new SMS(15551234567);

    expect((string) $sms)->toBe('sms:15551234567');
});

test('it dynamically delegates phone number stripping to the trait', function (): void {
    $sms = new SMS('+1 555-123 4567');

    expect((string) $sms)->toBe('sms:+15551234567');
});

test('it generates standard SMS uri when message is omitted', function (): void {
    $sms = new SMS('+15551234567');

    expect((string) $sms)->toBe('sms:+15551234567');
});

test('it strips empty string messages to kill empty block and array index mutants', function (): void {
    $sms = new SMS('+15551234567', '');

    expect((string) $sms)->toBe('sms:+15551234567');
});

test('it generates full SMS uri and safely encodes spaces to kill concat mutants', function (): void {
    $sms = new SMS('+15551234567', 'Hello World!');

    expect((string) $sms)->toBe('sms:+15551234567?body=Hello%20World%21');
});

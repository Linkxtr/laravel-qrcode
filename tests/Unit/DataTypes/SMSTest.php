<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\SMS;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidSMSArgumentException;
use Linkxtr\QrCode\Exceptions\UninitializedDataTypeException;

covers(SMS::class);

it('throws exception if rendered before creation', function (): void {
    $sms = new SMS;
    expect(fn (): string => (string) $sms)
        ->toThrow(UninitializedDataTypeException::class, 'SMS must be initialized via create() before rendering.');
});

test('it throws exception if phone number is missing', function (): void {
    $sms = new SMS;
    expect(fn () => $sms->create([]))
        ->toThrow(InvalidSMSArgumentException::class, 'SMS phone number is required.');
});

test('it throws exception if phone number is an invalid type', function (): void {
    $sms = new SMS;
    expect(fn () => $sms->create([['invalid array']]))
        ->toThrow(InvalidSMSArgumentException::class, 'Phone number must be a string or numeric value. Provided type: array');
});

test('it throws exception if message is not a string', function (): void {
    $sms = new SMS;
    expect(fn () => $sms->create(['+15551234567', 12345]))
        ->toThrow(InvalidSMSArgumentException::class, 'Message must be a string. Provided type: integer');
});

test('it safely casts numeric phone numbers to string to kill type logic mutants', function (): void {
    $sms = new SMS;
    $sms->create([15551234567]);

    expect((string) $sms)->toBe('sms:15551234567');
});

test('it dynamically delegates phone number stripping to the trait', function (): void {
    $sms = new SMS;
    $sms->create(['+1 555-123 4567']);

    expect((string) $sms)->toBe('sms:+15551234567');
});

test('it generates standard SMS uri when message is omitted', function (): void {
    $sms = new SMS;
    $sms->create(['+15551234567']);

    expect((string) $sms)->toBe('sms:+15551234567');
});

test('it strips empty string messages to kill empty block and array index mutants', function (): void {
    $sms = new SMS;

    $sms->create(['+15551234567', '']);

    expect((string) $sms)->toBe('sms:+15551234567');
});

test('it generates full SMS uri and safely encodes spaces to kill concat mutants', function (): void {
    $sms = new SMS;
    $sms->create(['+15551234567', 'Hello World!']);

    expect((string) $sms)->toBe('sms:+15551234567?body=Hello%20World%21');
});

test('it clears stale message data on object reuse to prevent state leakage', function (): void {
    $sms = new SMS;

    $sms->create(['+15551234567', 'Hello there']);

    expect((string) $sms)->toContain('body=Hello%20there');

    $sms->create(['+15551234567']);

    expect((string) $sms)->not->toContain('body=Hello%20there')
        ->and((string) $sms)->not->toContain('body=');
});

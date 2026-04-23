<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\SMS;

covers(SMS::class);

test('it throws exception if phone number is missing', function () {
    $sms = new SMS;
    expect(fn () => $sms->create([]))
        ->toThrow(InvalidArgumentException::class, 'SMS phone number is required.');
});

test('it throws exception if phone number is an invalid type', function () {
    $sms = new SMS;
    expect(fn () => $sms->create([['invalid array']]))
        ->toThrow(InvalidArgumentException::class, 'SMS phone number must be a string or numeric value.');
});

test('it throws exception if message is not a string', function () {
    $sms = new SMS;
    expect(fn () => $sms->create(['+15551234567', 12345]))
        ->toThrow(InvalidArgumentException::class, 'SMS message must be a string.');
});

test('it safely casts numeric phone numbers to string to kill type logic mutants', function () {
    $sms = new SMS;
    $sms->create([15551234567]);

    expect((string) $sms)->toBe('sms:15551234567');
});

test('it dynamically delegates phone number stripping to the trait', function () {
    $sms = new SMS;
    $sms->create(['+1 555-123 4567']);

    expect((string) $sms)->toBe('sms:+15551234567');
});

test('it generates standard SMS uri when message is omitted', function () {
    $sms = new SMS;
    $sms->create(['+15551234567']);

    expect((string) $sms)->toBe('sms:+15551234567');
});

test('it strips empty string messages to kill empty block and array index mutants', function () {
    $sms = new SMS;

    $sms->create(['+15551234567', '']);

    expect((string) $sms)->toBe('sms:+15551234567');
});

test('it generates full SMS uri and safely encodes spaces to kill concat mutants', function () {
    $sms = new SMS;
    $sms->create(['+15551234567', 'Hello World!']);

    expect((string) $sms)->toBe('sms:+15551234567?body=Hello%20World%21');
});

test('it clears stale message data on object reuse to prevent state leakage', function () {
    $sms = new SMS;

    $sms->create(['+15551234567', 'Hello there']);

    expect((string) $sms)->toContain('body=Hello%20there');

    $sms->create(['+15551234567']);

    expect((string) $sms)->not->toContain('body=Hello%20there')
        ->and((string) $sms)->not->toContain('body=');
});

<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\WhatsApp;

covers(WhatsApp::class);

test('it throws exception if phone number is missing', function () {
    $wa = new WhatsApp;
    expect(fn () => $wa->create([]))
        ->toThrow(InvalidArgumentException::class, 'WhatsApp phone number is required.');
});

test('it throws exception if phone number is an invalid type', function () {
    $wa = new WhatsApp;
    expect(fn () => $wa->create([['invalid_array_payload']]))
        ->toThrow(InvalidArgumentException::class, 'WhatsApp phone number must be a string or numeric value.');
});

test('it throws exception if phone number is an empty string', function () {
    $wa = new WhatsApp;
    expect(fn () => $wa->create(['']))
        ->toThrow(InvalidArgumentException::class, 'WhatsApp phone number cannot be empty.');
});

test('it throws exception if message is not a string', function () {
    $wa = new WhatsApp;
    expect(fn () => $wa->create(['+15551234567', 12345]))
        ->toThrow(InvalidArgumentException::class, 'WhatsApp message must be a string.');
});

test('it safely casts numeric phone numbers to string', function () {
    $wa = new WhatsApp;
    $wa->create([15551234567]);

    expect((string) $wa)->toBe('https://wa.me/15551234567');
});

test('it dynamically delegates phone number stripping to the trait', function () {
    $wa = new WhatsApp;
    $wa->create(['+1 555-123 4567']);

    expect((string) $wa)->toBe('https://wa.me/15551234567');
});

test('it generates standard WhatsApp uri when message is omitted', function () {
    $wa = new WhatsApp;
    $wa->create(['+15551234567']);

    expect((string) $wa)->toBe('https://wa.me/15551234567');
});

test('it strips empty string messages to kill empty block and array index mutants', function () {
    $wa = new WhatsApp;

    $wa->create(['+15551234567', '']);

    expect((string) $wa)->toBe('https://wa.me/15551234567');
});

test('it generates full WhatsApp uri and safely encodes spaces and trim whitespace from number', function () {
    $wa = new WhatsApp;
    $wa->create([' +15551234567', 'Hello World!']);

    expect((string) $wa)->toBe('https://wa.me/15551234567?text=Hello%20World%21');
});

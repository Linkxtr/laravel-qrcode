<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\WhatsApp;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidWhatsAppArgumentException;

covers(WhatsApp::class);

test('it throws exception if phone number is missing', function (): void {
    expect(fn (): WhatsApp => new WhatsApp)
        ->toThrow(TypeError::class);
});

test('it throws exception if phone number is an invalid type', function (): void {
    expect(fn (): WhatsApp => new WhatsApp(['invalid_array_payload']))
        ->toThrow(TypeError::class);
});

test('it throws exception if phone number is an empty string', function (): void {
    expect(fn (): WhatsApp => new WhatsApp(''))
        ->toThrow(InvalidWhatsAppArgumentException::class, 'WhatsApp phone number cannot be empty.');
});

test('it throws exception if message is not a string', function (): void {
    expect(fn (): WhatsApp => new WhatsApp('+15551234567', 12345))
        ->toThrow(TypeError::class);
});

test('it safely casts numeric phone numbers to string', function (): void {
    $wa = new WhatsApp(15551234567);

    expect((string) $wa)->toBe('https://wa.me/15551234567');
});

test('it dynamically delegates phone number stripping to the trait', function (): void {
    $wa = new WhatsApp('+1 555-123 4567');

    expect((string) $wa)->toBe('https://wa.me/15551234567');
});

test('it generates standard WhatsApp uri when message is omitted', function (): void {
    $wa = new WhatsApp('+15551234567');

    expect((string) $wa)->toBe('https://wa.me/15551234567');
});

test('it strips empty string messages to kill empty block and array index mutants', function (): void {
    $wa = new WhatsApp('+15551234567', '');

    expect((string) $wa)->toBe('https://wa.me/15551234567');
});

test('it generates full WhatsApp uri and safely encodes spaces and trim whitespace from number', function (): void {
    $wa = new WhatsApp(' +15551234567', 'Hello World!');

    expect((string) $wa)->toBe('https://wa.me/15551234567?text=Hello%20World%21');
});

<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\WhatsApp;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidWhatsAppArgumentException;
use Linkxtr\QrCode\Exceptions\UninitializedDataTypeException;

covers(WhatsApp::class);

it('throws exception if rendered before creation', function (): void {
    $wa = new WhatsApp;
    expect(fn (): string => (string) $wa)
        ->toThrow(UninitializedDataTypeException::class, 'WhatsApp must be initialized via create() before rendering.');
});

test('it throws exception if phone number is missing', function (): void {
    $wa = new WhatsApp;
    expect(fn () => $wa->create([]))
        ->toThrow(InvalidWhatsAppArgumentException::class, 'WhatsApp phone number is required.');
});

test('it throws exception if phone number is an invalid type', function (): void {
    $wa = new WhatsApp;
    expect(fn () => $wa->create([['invalid_array_payload']]))
        ->toThrow(InvalidWhatsAppArgumentException::class, 'WhatsApp phone number must be a string or numeric value.');
});

test('it throws exception if phone number is an empty string', function (): void {
    $wa = new WhatsApp;
    expect(fn () => $wa->create(['']))
        ->toThrow(InvalidWhatsAppArgumentException::class, 'WhatsApp phone number cannot be empty.');
});

test('it throws exception if message is not a string', function (): void {
    $wa = new WhatsApp;
    expect(fn () => $wa->create(['+15551234567', 12345]))
        ->toThrow(InvalidWhatsAppArgumentException::class, 'WhatsApp message must be a string. Provided type: integer');
});

test('it safely casts numeric phone numbers to string', function (): void {
    $wa = new WhatsApp;
    $wa->create([15551234567]);

    expect((string) $wa)->toBe('https://wa.me/15551234567');
});

test('it dynamically delegates phone number stripping to the trait', function (): void {
    $wa = new WhatsApp;
    $wa->create(['+1 555-123 4567']);

    expect((string) $wa)->toBe('https://wa.me/15551234567');
});

test('it generates standard WhatsApp uri when message is omitted', function (): void {
    $wa = new WhatsApp;
    $wa->create(['+15551234567']);

    expect((string) $wa)->toBe('https://wa.me/15551234567');
});

test('it strips empty string messages to kill empty block and array index mutants', function (): void {
    $wa = new WhatsApp;

    $wa->create(['+15551234567', '']);

    expect((string) $wa)->toBe('https://wa.me/15551234567');
});

test('it generates full WhatsApp uri and safely encodes spaces and trim whitespace from number', function (): void {
    $wa = new WhatsApp;
    $wa->create([' +15551234567', 'Hello World!']);

    expect((string) $wa)->toBe('https://wa.me/15551234567?text=Hello%20World%21');
});

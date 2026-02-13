<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\WhatsApp;

it('generates a WhatsApp string', function () {
    $whatsapp = new WhatsApp;
    $whatsapp->create([
        'number' => '1234567890',
        'message' => 'Hello World',
    ]);

    expect((string) $whatsapp)->toBe('https://wa.me/1234567890?text=Hello+World');
});

it('generates a WhatsApp string with only number', function () {
    $whatsapp = new WhatsApp;
    $whatsapp->create([
        'number' => '1234567890',
    ]);

    expect((string) $whatsapp)->toBe('https://wa.me/1234567890');
});

it('throws exception if number is missing', function () {
    $whatsapp = new WhatsApp;
    $whatsapp->create([
        'message' => 'Hello',
    ]);
    // Trigger string conversion which validates
    $str = (string) $whatsapp;
})->throws(InvalidArgumentException::class, 'WhatsApp number is mandatory.');

it('supports positional arguments', function () {
    $whatsapp = new WhatsApp;
    $whatsapp->create(['1234567890', 'Hello World']);

    expect((string) $whatsapp)->toBe('https://wa.me/1234567890?text=Hello+World');
});

it('throws exception if number is not a string (named)', function () {
    $whatsapp = new WhatsApp;
    expect(fn () => $whatsapp->create(['number' => 1234567890]))
        ->toThrow(InvalidArgumentException::class, 'WhatsApp number must be a string.');
});

it('throws exception if number is not a string (positional)', function () {
    $whatsapp = new WhatsApp;
    expect(fn () => $whatsapp->create([1234567890]))
        ->toThrow(InvalidArgumentException::class, 'WhatsApp number must be a string.');
});

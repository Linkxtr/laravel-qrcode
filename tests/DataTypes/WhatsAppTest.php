<?php

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

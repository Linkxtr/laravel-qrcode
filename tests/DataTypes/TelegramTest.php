<?php

use Linkxtr\QrCode\DataTypes\Telegram;

it('generates a Telegram string', function () {
    $telegram = new Telegram;
    $telegram->create([
        'username' => 'username',
    ]);

    expect((string) $telegram)->toBe('https://t.me/username');
});

it('generates a Telegram string with positional arguments', function () {
    $telegram = new Telegram;
    $telegram->create(['username']);

    expect((string) $telegram)->toBe('https://t.me/username');
});

it('throws exception if username is missing', function () {
    $telegram = new Telegram;
    $telegram->create([]);
    // Trigger string conversion which validates
    $str = (string) $telegram;
})->throws(InvalidArgumentException::class, 'Telegram username is mandatory.');

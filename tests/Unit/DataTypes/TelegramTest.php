<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\Telegram;

covers(Telegram::class);

test('it throws exception if username is missing', function () {
    $telegram = new Telegram;
    expect(fn () => $telegram->create([]))
        ->toThrow(InvalidArgumentException::class, 'Telegram username is required.');
});

test('it throws exception if username is an invalid type', function () {
    $telegram = new Telegram;
    expect(fn () => $telegram->create([12345]))
        ->toThrow(InvalidArgumentException::class, 'Telegram username must be a string.');
});

test('it throws an exception if the normalized username is empty to prevent broken URIs', function () {
    $telegram = new Telegram;

    expect(fn () => $telegram->create(['']))
        ->toThrow(InvalidArgumentException::class, 'Telegram username cannot be empty.');
    expect(fn () => $telegram->create(['   ']))
        ->toThrow(InvalidArgumentException::class, 'Telegram username cannot be empty.');
    expect(fn () => $telegram->create(['@']))
        ->toThrow(InvalidArgumentException::class, 'Telegram username cannot be empty');
    expect(fn () => $telegram->create(['   @   ']))
        ->toThrow(InvalidArgumentException::class, 'Telegram username cannot be empty');
});

test('it generates standard Telegram uri from a clean username', function () {
    $telegram = new Telegram;
    $telegram->create(['khaledsadek']);

    expect((string) $telegram)->toBe('https://t.me/khaledsadek');
});

test('it intelligently strips the @ symbol from the username', function () {
    $telegram = new Telegram;

    $telegram->create(['@khaledsadek']);

    expect((string) $telegram)->toBe('https://t.me/khaledsadek');
});

test('it trims accidental whitespace around the username', function () {
    $telegram = new Telegram;

    $telegram->create(['  @khaledsadek  ']);

    expect((string) $telegram)->toBe('https://t.me/khaledsadek');
});

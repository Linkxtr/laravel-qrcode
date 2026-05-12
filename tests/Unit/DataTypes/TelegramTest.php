<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\Telegram;
use Linkxtr\QrCode\Exceptions\InvalidTelegramArgumentException;
use Linkxtr\QrCode\Exceptions\UninitializedDataTypeException;

covers(Telegram::class);

it('throws exception if rendered before creation', function (): void {
    $telegram = new Telegram;
    expect(fn (): string => (string) $telegram)
        ->toThrow(UninitializedDataTypeException::class, 'Telegram must be initialized via create() before rendering.');
});

test('it throws exception if username is missing', function (): void {
    $telegram = new Telegram;
    expect(fn () => $telegram->create([]))
        ->toThrow(InvalidTelegramArgumentException::class, 'Telegram username is required.');
});

test('it throws exception if username is an invalid type', function (): void {
    $telegram = new Telegram;
    expect(fn () => $telegram->create([12345]))
        ->toThrow(InvalidTelegramArgumentException::class, 'Telegram username must be a string.');
});

test('it throws an exception if the normalized username is empty to prevent broken URIs', function (): void {
    $telegram = new Telegram;

    expect(fn () => $telegram->create(['']))
        ->toThrow(InvalidTelegramArgumentException::class, 'Invalid Telegram username format.');
    expect(fn () => $telegram->create(['   ']))
        ->toThrow(InvalidTelegramArgumentException::class, 'Invalid Telegram username format.');
    expect(fn () => $telegram->create(['@']))
        ->toThrow(InvalidTelegramArgumentException::class, 'Invalid Telegram username format');
    expect(fn () => $telegram->create(['   @   ']))
        ->toThrow(InvalidTelegramArgumentException::class, 'Invalid Telegram username format');
});

it('throws an exception if the username is invalid', function (): void {
    $telegram = new Telegram;

    expect(fn () => $telegram->create(['abc']))
        ->toThrow(InvalidTelegramArgumentException::class, 'Invalid Telegram username format.');

    expect(fn () => $telegram->create(['!@#$%^']))
        ->toThrow(InvalidTelegramArgumentException::class, 'Invalid Telegram username format.');

    expect(fn () => $telegram->create(['12345678901234567890123456789012']))
        ->toThrow(InvalidTelegramArgumentException::class, 'Invalid Telegram username format.');

    expect(fn () => $telegram->create(['Hello world']))
        ->toThrow(InvalidTelegramArgumentException::class, 'Invalid Telegram username format.');

    expect(fn () => $telegram->create(['TooLongUserName1234567890123456789012345678901234']))
        ->toThrow(InvalidTelegramArgumentException::class, 'Invalid Telegram username format.');
});

test('it generates standard Telegram uri from a clean username', function (): void {
    $telegram = new Telegram;
    $telegram->create(['khaledsadek']);

    expect((string) $telegram)->toBe('https://t.me/khaledsadek');
});

test('it intelligently strips the @ symbol from the username', function (): void {
    $telegram = new Telegram;

    $telegram->create(['@khaledsadek']);

    expect((string) $telegram)->toBe('https://t.me/khaledsadek');
});

test('it trims accidental whitespace around the username', function (): void {
    $telegram = new Telegram;

    $telegram->create(['  @khaledsadek  ']);

    expect((string) $telegram)->toBe('https://t.me/khaledsadek');
});

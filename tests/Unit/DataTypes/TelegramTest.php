<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\Telegram;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidTelegramArgumentException;

covers(Telegram::class);

test('it throws exception if username is missing', function (): void {
    expect(fn (): Telegram => new Telegram)
        ->toThrow(TypeError::class);
});

test('it throws exception if username is an invalid type', function (): void {
    expect(fn (): Telegram => new Telegram(12345))
        ->toThrow(TypeError::class);
});

test('it throws an exception if the normalized username is empty to prevent broken URIs', function (): void {
    expect(fn (): Telegram => new Telegram(''))
        ->toThrow(InvalidTelegramArgumentException::class, 'Invalid Telegram username format.');
    expect(fn (): Telegram => new Telegram('   '))
        ->toThrow(InvalidTelegramArgumentException::class, 'Invalid Telegram username format.');
    expect(fn (): Telegram => new Telegram('@'))
        ->toThrow(InvalidTelegramArgumentException::class, 'Invalid Telegram username format.');
    expect(fn (): Telegram => new Telegram('   @   '))
        ->toThrow(InvalidTelegramArgumentException::class, 'Invalid Telegram username format.');
});

it('throws an exception if the username is invalid', function (): void {
    expect(fn (): Telegram => new Telegram('abc'))
        ->toThrow(InvalidTelegramArgumentException::class, 'Invalid Telegram username format.');

    expect(fn (): Telegram => new Telegram('!@#$%^'))
        ->toThrow(InvalidTelegramArgumentException::class, 'Invalid Telegram username format.');

    expect(fn (): Telegram => new Telegram('12345678901234567890123456789012'))
        ->toThrow(InvalidTelegramArgumentException::class, 'Invalid Telegram username format.');

    expect(fn (): Telegram => new Telegram('Hello world'))
        ->toThrow(InvalidTelegramArgumentException::class, 'Invalid Telegram username format.');

    expect(fn (): Telegram => new Telegram('TooLongUserName1234567890123456789012345678901234'))
        ->toThrow(InvalidTelegramArgumentException::class, 'Invalid Telegram username format.');
});

test('it generates standard Telegram uri from a clean username', function (): void {
    $telegram = new Telegram('khaledsadek');

    expect((string) $telegram)->toBe('https://t.me/khaledsadek');
});

test('it intelligently strips the @ symbol from the username', function (): void {
    $telegram = new Telegram('@khaledsadek');

    expect((string) $telegram)->toBe('https://t.me/khaledsadek');
});

test('it trims accidental whitespace around the username', function (): void {
    $telegram = new Telegram('  @khaledsadek  ');

    expect((string) $telegram)->toBe('https://t.me/khaledsadek');
});

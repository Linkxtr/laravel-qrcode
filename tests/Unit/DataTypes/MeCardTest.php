<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\MeCard;

covers(MeCard::class);

it('throws exception if rendered before creation', function () {
    $meCard = new MeCard;
    expect(fn () => (string) $meCard)
        ->toThrow(LogicException::class, 'MeCard must be initialized via create() before rendering.');
});

it('throws exception if name is missing or invalid', function () {
    $meCard = new MeCard;

    expect(fn () => $meCard->create([]))
        ->toThrow(InvalidArgumentException::class, 'MeCard Name is mandatory.');

    expect(fn () => $meCard->create([123]))
        ->toThrow(InvalidArgumentException::class, 'MeCard Name is mandatory.');

    expect(fn () => $meCard->create(['name' => '']))
        ->toThrow(InvalidArgumentException::class, 'MeCard Name is mandatory.');
});

it('successfully maps the 5 common positional arguments', function () {
    $meCard = new MeCard;
    // Maps: Name, Phone, Email, URL, Address
    $meCard->create([
        'Khaled Sadek',
        '+123456789',
        'test@example.com',
        'https://github.com',
        '123 Main St',
    ]);

    expect((string) $meCard)->toBe('MECARD:N:Khaled Sadek;TEL:+123456789;EMAIL:test@example.com;ADR:123 Main St;URL:https://github.com;;');
});

it('successfully maps all 13 associative array arguments', function () {
    $meCard = new MeCard;
    $meCard->create([[
        'name' => 'Khaled Sadek',
        'reading' => 'Kha-led',
        'nickname' => 'KhaledDev',
        'phone' => '+123456',
        'phone2' => '+654321',
        'phone3' => '+789012',
        'videoPhone' => '+111111',
        'email' => 'test@example.com',
        'note' => 'Developer',
        'birthday' => '19900101',
        'address' => '123 Main St',
        'postOfficeBox' => 'PO123',
        'url' => 'https://github.com',
    ]]);

    $expected = 'MECARD:N:Khaled Sadek;SOUND:Kha-led;NICKNAME:KhaledDev;TEL:+123456;TEL:+654321;TEL:+789012;TEL-AV:+111111;EMAIL:test@example.com;NOTE:Developer;BDAY:19900101;ADR:123 Main St;POBOX:PO123;URL:https://github.com;;';
    expect((string) $meCard)->toBe($expected);
});

it('ignores non-string associative arguments', function () {
    $meCard = new MeCard;
    $meCard->create([[
        'name' => 'Khaled Sadek',
        'reading' => 123,
        'nickname' => false,
        'phone' => ['invalid'],
        'phone2' => 456,
        'phone3' => null,
        'videoPhone' => [],
        'email' => null,
        'note' => 456.7,
        'birthday' => false,
        'address' => [],
        'postOfficeBox' => 123,
        'url' => null,
    ]]);

    expect((string) $meCard)->toBe('MECARD:N:Khaled Sadek;;');
});

it('strips empty string associative arguments', function () {
    $meCard = new MeCard;
    $meCard->create([[
        'name' => 'Khaled Sadek',
        'reading' => '',
        'nickname' => '',
        'phone' => '',
        'phone2' => '',
        'phone3' => '',
        'videoPhone' => '',
        'email' => '',
        'note' => '',
        'birthday' => '',
        'address' => '',
        'postOfficeBox' => '',
        'url' => '',
    ]]);

    expect((string) $meCard)->toBe('MECARD:N:Khaled Sadek;;');
});

it('accurately escapes special characters', function () {
    $meCard = new MeCard;

    $chaosString = 'Name\\With;Special:Chars,Here';

    $meCard->create([$chaosString]);
    $expected = 'MECARD:N:Name\\\\With\\;Special\\:Chars,Here;;';

    expect((string) $meCard)->toBe($expected);
});

it('rigorously escapes semicolons and backslashes in URLs to prevent MeCard delimiter corruption', function () {
    $meCard = new MeCard;

    $meCard->create([[
        'name' => 'John Doe',
        'url' => 'https://example.com/path;session=123\\backup',
    ]]);

    $result = (string) $meCard;

    expect($result)->toContain('URL:https://example.com/path\;session=123\\\\backup;');

    expect($result)->not->toContain('path;session');
});

it('escapes commas in non-name fields', function () {
    $meCard = new MeCard;

    $meCard->create([
        'name' => 'Doe,John',
        'note' => 'Hello, World',
    ]);

    expect((string) $meCard)->toBe('MECARD:N:Doe,John;NOTE:Hello\, World;;');
});

it('escapes special characters correctly in both name and non-name fields', function () {
    $meCard = new MeCard;

    $meCard->create([
        'name' => 'Doe,John\\With;Special:Chars',
        'note' => 'Note,With\\All;Special:Chars',
    ]);

    $expected = 'MECARD:N:Doe,John\\\\With\\;Special\\:Chars;NOTE:Note\\,With\\\\All\\;Special\\:Chars;;';

    expect((string) $meCard)->toBe($expected);
});

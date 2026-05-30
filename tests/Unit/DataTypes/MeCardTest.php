<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\MeCard;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidMeCardArgumentException;

covers(MeCard::class);

test('it throws exception if name is missing', function (): void {
    expect(fn (): MeCard => new MeCard)
        ->toThrow(TypeError::class);
});

test('it throws exception if name is an empty string', function (): void {
    expect(fn (): MeCard => new MeCard(''))
        ->toThrow(InvalidMeCardArgumentException::class);
});

test('it strips empty string associative arguments', function (): void {
    $mecard = new MeCard('John Doe', phone: '', email: '', note: '');

    $expected = 'MECARD:N:John Doe;;';

    expect((string) $mecard)->toBe($expected);
});

test('it generates full MeCard uri', function (): void {
    $mecard = new MeCard('John Doe', phone: '+15551234567', email: 'test@example.com', note: 'A note');

    $expected = 'MECARD:N:John Doe;TEL:+15551234567;EMAIL:test@example.com;NOTE:A note;;';

    expect((string) $mecard)->toBe($expected);
});

test('it strictly maps first and last names independently', function (): void {
    $mecard1 = new MeCard('Khaled');
    expect((string) $mecard1)->toBe('MECARD:N:Khaled;;');
});

test('it escapes reserved characters accurately', function (): void {
    $mecard = new MeCard('Name\\With;Special:Chars,Here', note: 'Line1Line2');

    $expected = 'MECARD:N:Name\\\\With\\;Special\\:Chars,Here;NOTE:Line1Line2;;';

    expect((string) $mecard)->toBe($expected);
});

test('it creates mecard with all properties', function (): void {
    $mecard = new MeCard(
        name: 'John Doe',
        phone: '+15551234567',
        email: '[EMAIL_ADDRESS]',
        url: 'https://example.com',
        address: '123 Main St',
        reading: 'John Doe',
        nickname: 'John',
        phone2: '+15551234567',
        phone3: '+15551234567',
        videoPhone: '+15551234567',
        note: 'A note',
        birthday: '2022-01-01',
        postOfficeBox: '1234567890'
    );

    $expected = 'MECARD:N:John Doe;SOUND:John Doe;NICKNAME:John;TEL:+15551234567;TEL:+15551234567;TEL:+15551234567;TEL-AV:+15551234567;EMAIL:[EMAIL_ADDRESS];NOTE:A note;BDAY:2022-01-01;ADR:123 Main St;POBOX:1234567890;URL:https://example.com;;';

    expect((string) $mecard)->toBe($expected);
});

it('ignores empty optional arguments', function (): void {
    $mecard = new MeCard(
        name: 'John Doe',
        phone: '',
        email: '',
        url: '',
        address: '',
        reading: '',
        nickname: '',
        phone2: '',
        phone3: '',
        videoPhone: '',
        note: '',
        birthday: '',
        postOfficeBox: ''
    );

    $expected = 'MECARD:N:John Doe;;';

    expect((string) $mecard)->toBe($expected);
});

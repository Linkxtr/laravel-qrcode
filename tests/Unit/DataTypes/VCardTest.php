<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\VCard;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidVCardArgumentException;

covers(VCard::class);

test('it throws exception if arguments are missing', function (): void {
    expect(fn (): VCard => new VCard)
        ->toThrow(TypeError::class);
});

test('it throws exception if name is missing or empty', function (): void {
    expect(fn (): VCard => new VCard(''))
        ->toThrow(InvalidVCardArgumentException::class);
});

test('it accurately escapes special characters and newlines', function (): void {
    $chaosString = "Name\\With;Special,Chars\nHere";

    $vcard = new VCard($chaosString);

    $expected = implode("\r\n", [
        'BEGIN:VCARD',
        'VERSION:3.0',
        'FN:Name\\\\With\\;Special\\,Chars\\nHere',
        'END:VCARD',
        '',
    ]);

    expect((string) $vcard)->toBe($expected);

    $chaoticString = "Line1\r\nLine2\rLine3\nLine4";

    $vcard2 = new VCard(
        name: 'John Doe',
        note: $chaoticString
    );

    $result = (string) $vcard2;

    expect($result)->toContain('NOTE:Line1\nLine2\nLine3\nLine4');
});

test('it gracefully handles missing first or last names independently', function (): void {
    $vcard1 = new VCard(
        name: 'Khaled',
        firstName: 'Khaled'
    );

    $expected1 = implode("\r\n", [
        'BEGIN:VCARD',
        'VERSION:3.0',
        'FN:Khaled',
        'N:;Khaled;;;',
        'END:VCARD',
        '',
    ]);

    expect((string) $vcard1)->toBe($expected1);

    $vcard2 = new VCard(
        name: 'Sadek',
        lastName: 'Sadek'
    );

    $expected2 = implode("\r\n", [
        'BEGIN:VCARD',
        'VERSION:3.0',
        'FN:Sadek',
        'N:Sadek;;;;',
        'END:VCARD',
        '',
    ]);

    expect((string) $vcard2)->toBe($expected2);
});

test('it creates vcard with all properties', function (): void {
    $vcard = new VCard(
        name: 'John Doe',
        phone: '+15551234567',
        email: '[EMAIL_ADDRESS]',
        company: 'ACME Corp',
        job: 'Software Engineer',
        firstName: 'John',
        lastName: 'Doe',
        emailWork: '[EMAIL_ADDRESS]',
        emailHome: '[EMAIL_ADDRESS]',
        phoneWork: '+15551234567',
        phoneHome: '+15551234567',
        phoneCell: '+15551234567',
        role: 'Software Engineer',
        address: '123 Main St',
        url: 'https://example.com',
        note: 'A note',
        birthday: '2022-01-01'
    );

    $expected = "BEGIN:VCARD\r\nVERSION:3.0\r\nFN:John Doe\r\nN:Doe;John;;;\r\nORG:ACME Corp\r\nTITLE:Software Engineer\r\nROLE:Software Engineer\r\nEMAIL;type=INTERNET:[EMAIL_ADDRESS]\r\nEMAIL;type=INTERNET,WORK:[EMAIL_ADDRESS]\r\nEMAIL;type=INTERNET,HOME:[EMAIL_ADDRESS]\r\nTEL:+15551234567\r\nTEL;type=WORK:+15551234567\r\nTEL;type=HOME:+15551234567\r\nTEL;type=CELL:+15551234567\r\nADR:;;123 Main St;;;;\r\nURL:https://example.com\r\nNOTE:A note\r\nBDAY:2022-01-01\r\nEND:VCARD\r\n";

    expect((string) $vcard)->toBe($expected);
});

it('ignores empty optional properties', function (): void {
    $vcard = new VCard(
        name: 'John Doe',
        phone: '',
        email: '',
        company: '',
        job: '',
        emailWork: '',
        emailHome: '',
        phoneWork: '',
        phoneHome: '',
        phoneCell: '',
        role: '',
        address: '',
        url: '',
        note: '',
        birthday: ''
    );

    $expected = "BEGIN:VCARD\r\nVERSION:3.0\r\nFN:John Doe\r\nEND:VCARD\r\n";

    expect((string) $vcard)->toBe($expected);
});

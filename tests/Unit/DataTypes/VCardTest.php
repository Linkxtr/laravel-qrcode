<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\VCard;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidVCardArgumentException;

covers(VCard::class);

test('it throws exception if rendered before creation', function (): void {
    $vcard = new VCard;
    expect(fn (): string => (string) $vcard)
        ->toThrow(LogicException::class, 'VCard must be initialized via create() before rendering.');
});

test('it throws exception if name is missing or invalid', function (): void {
    $vcard = new VCard;

    expect(fn () => $vcard->create([]))
        ->toThrow(InvalidVCardArgumentException::class, 'VCard Name is mandatory.');

    expect(fn () => $vcard->create([123]))
        ->toThrow(InvalidVCardArgumentException::class, 'VCard name must be a non-empty string. Provided type: integer');

    expect(fn () => $vcard->create(['name' => '']))
        ->toThrow(InvalidVCardArgumentException::class, 'VCard name must be a non-empty string. Provided type: empty string');
});

test('it successfully maps the 5 common positional arguments', function (): void {
    $vcard = new VCard;
    // Maps: Name, Phone, Email, Company, Job
    $vcard->create([
        'Khaled Sadek',
        '+123456789',
        'test@example.com',
        'Linkxtr',
        'Developer',
    ]);

    $expected = implode("\r\n", [
        'BEGIN:VCARD',
        'VERSION:3.0',
        'FN:Khaled Sadek',
        'ORG:Linkxtr',
        'TITLE:Developer',
        'EMAIL;type=INTERNET:test@example.com',
        'TEL:+123456789',
        'END:VCARD',
        '',
    ]);

    expect((string) $vcard)->toBe($expected);
});

test('it successfully maps massive associative array arguments', function (): void {
    $vcard = new VCard;
    $vcard->create([[
        'name' => 'Khaled Sadek',
        'firstName' => 'Khaled',
        'lastName' => 'Sadek',
        'email' => 'test@test.com',
        'emailWork' => 'work@test.com',
        'emailHome' => 'home@test.com',
        'phone' => '+1',
        'phoneWork' => '+2',
        'phoneHome' => '+3',
        'phoneCell' => '+4',
        'company' => 'Inc',
        'job' => 'Dev',
        'role' => 'Admin',
        'address' => '123 Main St',
        'url' => 'https://github.com',
        'note' => 'Creator',
        'birthday' => '1990-01-01',
    ]]);

    $expected = implode("\r\n", [
        'BEGIN:VCARD',
        'VERSION:3.0',
        'FN:Khaled Sadek',
        'N:Sadek;Khaled;;;',
        'ORG:Inc',
        'TITLE:Dev',
        'ROLE:Admin',
        'EMAIL;type=INTERNET:test@test.com',
        'EMAIL;type=INTERNET,WORK:work@test.com',
        'EMAIL;type=INTERNET,HOME:home@test.com',
        'TEL:+1',
        'TEL;type=WORK:+2',
        'TEL;type=HOME:+3',
        'TEL;type=CELL:+4',
        'ADR:;;123 Main St;;;;',
        'URL:https://github.com',
        'NOTE:Creator',
        'BDAY:1990-01-01',
        'END:VCARD',
        '',
    ]);

    expect((string) $vcard)->toBe($expected);
});

test('it ignores non-string associative arguments', function (): void {
    $vcard = new VCard;
    $vcard->create([[
        'name' => 'Khaled Sadek',
        'firstName' => 123,
        'email' => false,
        'phone' => ['invalid'],
        'company' => null,
        'job' => 456.7,
    ]]);

    $expected = implode("\r\n", [
        'BEGIN:VCARD',
        'VERSION:3.0',
        'FN:Khaled Sadek',
        'END:VCARD',
        '',
    ]);

    expect((string) $vcard)->toBe($expected);
});

test('it strips empty string associative arguments', function (): void {
    $vcard = new VCard;
    $vcard->create([[
        'name' => 'Khaled Sadek',
        'firstName' => '',
        'lastName' => '',
        'email' => '',
        'phone' => '',
        'company' => '',
        'job' => '',
    ]]);

    $expected = implode("\r\n", [
        'BEGIN:VCARD',
        'VERSION:3.0',
        'FN:Khaled Sadek',
        'END:VCARD',
        '',
    ]);

    expect((string) $vcard)->toBe($expected);
});

test('it accurately escapes special characters and newlines', function (): void {
    $vcard = new VCard;

    $chaosString = "Name\\With;Special,Chars\nHere";

    $vcard->create([$chaosString]);

    $expected = implode("\r\n", [
        'BEGIN:VCARD',
        'VERSION:3.0',
        'FN:Name\\\\With\\;Special\\,Chars\\nHere',
        'END:VCARD',
        '',
    ]);

    expect((string) $vcard)->toBe($expected);

    $vcard2 = new VCard;

    $chaoticString = "Line1\r\nLine2\rLine3\nLine4";

    $vcard2->create([[
        'name' => 'John Doe',
        'note' => $chaoticString,
    ]]);

    $result = (string) $vcard2;

    expect($result)->toContain('NOTE:Line1\nLine2\nLine3\nLine4');
});

test('it gracefully handles missing first or last names independently', function (): void {
    $vcard1 = new VCard;
    $vcard1->create([
        'name' => 'Khaled',
        'firstName' => 'Khaled',
    ]);

    $expected1 = implode("\r\n", [
        'BEGIN:VCARD',
        'VERSION:3.0',
        'FN:Khaled',
        'N:;Khaled;;;',
        'END:VCARD',
        '',
    ]);

    expect((string) $vcard1)->toBe($expected1);

    $vcard2 = new VCard;
    $vcard2->create([
        'name' => 'Sadek',
        'lastName' => 'Sadek',
    ]);

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

test('it clears stale optional data on object reuse', function (): void {
    $vcard = new VCard;
    $vcard->create([[
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'phoneCell' => '+1',
        'note' => 'Old note',
    ]]);

    $vcard->create([['name' => 'Bob']]);

    $output = (string) $vcard;
    expect($output)->not->toContain('EMAIL')
        ->and($output)->not->toContain('TEL')
        ->and($output)->not->toContain('NOTE');
});

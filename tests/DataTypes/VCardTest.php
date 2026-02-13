<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\VCard;

it('generates a vCard string from direct array', function () {
    $vCard = new VCard;
    $vCard->create([
        'name' => 'John Doe',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'phone' => '+1234567890',
        'company' => 'ACME Inc.',
        'title' => 'Developer',
        'url' => 'https://example.com',
    ]);

    $content = (string) $vCard;

    expect($content)->toBe("BEGIN:VCARD\r\nVERSION:3.0\r\nFN:John Doe\r\nN:Doe;John;;;\r\nEMAIL:john@example.com\r\nTEL:+1234567890\r\nORG:ACME Inc.\r\nTITLE:Developer\r\nURL:https://example.com\r\nEND:VCARD");
});

it('generates a vCard string from wrapped array (Generator style)', function () {
    $vCard = new VCard;
    $vCard->create([[
        'name' => 'John Doe',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
    ]]);

    $content = (string) $vCard;

    expect($content)->toBe("BEGIN:VCARD\r\nVERSION:3.0\r\nFN:John Doe\r\nN:Doe;John;;;\r\nEMAIL:john@example.com\r\nEND:VCARD");
});

it('generates a vCard string with empty N field if components are missing', function () {
    $vCard = new VCard;
    $vCard->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $content = (string) $vCard;

    expect($content)->toBe("BEGIN:VCARD\r\nVERSION:3.0\r\nFN:John Doe\r\nN:;;;;\r\nEMAIL:john@example.com\r\nEND:VCARD");
});

it('ignores non-string values for optional fields', function () {
    $vCard = new VCard;
    $vCard->create([
        'name' => 'John Doe',
        'email' => 123,
        'phone' => 1234567890,
        'company' => 123,
        'title' => 123,
        'url' => 123,
    ]);

    $content = (string) $vCard;

    expect($content)->toBe("BEGIN:VCARD\r\nVERSION:3.0\r\nFN:John Doe\r\nN:;;;;\r\nEND:VCARD");
});

it('throws exception if name is missing', function () {
    $vCard = new VCard;
    $vCard->create([
        'email' => 'john@example.com',
    ]);
    (string) $vCard;
})->throws(InvalidArgumentException::class, 'vCard FN (Formatted Name) is mandatory.');

it('validation fails for invalid email', function () {
    $vCard = new VCard;
    $vCard->create([
        'name' => 'John Doe',
        'email' => 'invalid-email',
    ]);
})->throws(InvalidArgumentException::class, 'Invalid email address provided to vCard.');

it('validation fails for invalid url', function () {
    $vCard = new VCard;
    $vCard->create([
        'name' => 'John Doe',
        'url' => 'invalid-url',
    ]);
})->throws(InvalidArgumentException::class, 'Invalid URL provided to vCard.');

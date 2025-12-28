<?php

namespace Tests\DataTypes;

use Linkxtr\QrCode\DataTypes\VCard;

it('generates a vCard string from direct array', function () {
    $vCard = new VCard;
    $vCard->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '+1234567890',
        'company' => 'ACME Inc.',
        'title' => 'Developer',
        'url' => 'https://example.com',
    ]);

    $content = (string) $vCard;

    expect($content)->toBe("BEGIN:VCARD\r\nVERSION:3.0\r\nFN:John Doe\r\nEMAIL:john@example.com\r\nTEL:+1234567890\r\nORG:ACME Inc.\r\nTITLE:Developer\r\nURL:https://example.com\r\nEND:VCARD");
});

it('generates a vCard string from wrapped array (Generator style)', function () {
    $vCard = new VCard;
    $vCard->create([[
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]]);

    $content = (string) $vCard;

    expect($content)->toBe("BEGIN:VCARD\r\nVERSION:3.0\r\nFN:John Doe\r\nEMAIL:john@example.com\r\nEND:VCARD");
});

it('validation fails for invalid email', function () {
    $vCard = new VCard;
    $vCard->create([
        'email' => 'invalid-email',
    ]);
})->throws(\InvalidArgumentException::class, 'Invalid email address provided to vCard.');

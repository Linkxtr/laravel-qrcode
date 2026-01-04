<?php

use Linkxtr\QrCode\DataTypes\MeCard;

it('generates a MeCard string', function () {
    $meCard = new MeCard;
    $meCard->create([
        'name' => 'John Doe',
        'phone' => '+1234567890',
        'email' => 'john@example.com',
        'address' => '123 Main St',
        'birthday' => '19901231',
        'url' => 'https://example.com',
        'note' => 'Friend from work',
        'reading' => 'Johnny',
    ]);

    expect((string) $meCard)->toBe('MECARD:N:John Doe;SOUND:Johnny;TEL:+1234567890;EMAIL:john@example.com;NOTE:Friend from work;BDAY:19901231;ADR:123 Main St;URL:https\://example.com;;');
});

it('generates a MeCard string with minimal data', function () {
    $meCard = new MeCard;
    $meCard->create([
        'name' => 'John Doe',
    ]);

    expect((string) $meCard)->toBe('MECARD:N:John Doe;;');
});

it('throws exception if name is missing', function () {
    $meCard = new MeCard;
    $meCard->create([
        'phone' => '+1234567890',
    ]);
    // Trigger string conversion which validates
    $str = (string) $meCard;
})->throws(InvalidArgumentException::class, 'MeCard Name is mandatory.');

it('supports positional arguments', function () {
    $meCard = new MeCard;
    $meCard->create(['John Doe', '+1234567890', 'john@example.com']);

    expect((string) $meCard)->toBe('MECARD:N:John Doe;TEL:+1234567890;EMAIL:john@example.com;;');
});

it('escapes special characters', function () {
    $meCard = new MeCard;
    $meCard->create([
        'name' => 'Doe, John',
        'note' => 'A;B:C',
    ]);

    // MeCard escaping rules generally: backslash escapes specific chars or just colons/semicolons depending on parser implementation.
    // Common practice: escape ; : \ with \
    // Looking at VCard implementation it escapes multiple chars. MeCard is simpler but commonly ; and : are delimiters.
    // Let's assume basic escaping for now similar to VCard but maybe adjusted if needed.
    // Actually standard MeCard often just escapes special chars, let's see.
    // For now let's expect standard escaping.
    
    expect((string) $meCard)->toBe('MECARD:N:Doe\, John;NOTE:A\;B\:C;;');
});

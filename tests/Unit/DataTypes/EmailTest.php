<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\Email;

covers(Email::class);

test('it throws exception if email address is missing', function () {
    $email = new Email;
    expect(fn () => $email->create([]))
        ->toThrow(InvalidArgumentException::class, 'Email address is required.');
});

test('it throws exception if main email address is invalid', function () {
    $email = new Email;
    expect(fn () => $email->create(['not-an-email']))
        ->toThrow(InvalidArgumentException::class, 'Invalid email address provided to Email.');
});

test('it throws exception if subject is not a string', function () {
    $email = new Email;
    expect(fn () => $email->create(['test@example.com', 12345]))
        ->toThrow(InvalidArgumentException::class, 'Invalid subject provided to Email.');
});

test('it throws exception if body is not a string', function () {
    $email = new Email;
    expect(fn () => $email->create(['test@example.com', 'Subject', ['invalid array']]))
        ->toThrow(InvalidArgumentException::class, 'Invalid body provided to Email.');
});

test('it throws exception if cc email is invalid', function () {
    $email = new Email;
    expect(fn () => $email->create(['test@example.com', 'Sub', 'Body', 'invalid-cc']))
        ->toThrow(InvalidArgumentException::class, 'Invalid email address provided to Email.');
});

test('it throws exception if bcc email is invalid', function () {
    $email = new Email;
    expect(fn () => $email->create(['test@example.com', 'Sub', 'Body', 'cc@example.com', 'invalid-bcc']))
        ->toThrow(InvalidArgumentException::class, 'Invalid email address provided to Email.');
});

test('it generates simple mailto link with only an address', function () {
    $email = new Email;
    $email->create(['test@example.com']);

    expect((string) $email)->toBe('mailto:test@example.com');
});

test('it gracefully handles varying argument lengths to kill isset index mutants', function () {
    $email2 = new Email;
    $email2->create(['test@example.com', 'Subject']);
    expect((string) $email2)->toBe('mailto:test@example.com?subject=Subject');

    $email3 = new Email;
    $email3->create(['test@example.com', 'Subject', 'Body']);
    expect((string) $email3)->toBe('mailto:test@example.com?subject=Subject&body=Body');
    $email4 = new Email;
    $email4->create(['test@example.com', 'Subject', 'Body', 'cc@example.com']);
    expect((string) $email4)->toBe('mailto:test@example.com?subject=Subject&body=Body&cc=cc%40example.com');
});

test('it strictly maps indices and strips alternating empty strings to kill value swapping mutants', function () {
    $email1 = new Email;
    $email1->create(['test@example.com', '', 'Body Text', '', 'bcc@example.com']);

    expect((string) $email1)->toBe('mailto:test@example.com?body=Body%20Text&bcc=bcc%40example.com');

    $email2 = new Email;
    $email2->create(['test@example.com', 'Subject Text', '', 'cc@example.com', '']);
    expect((string) $email2)->toBe('mailto:test@example.com?subject=Subject%20Text&cc=cc%40example.com');
});

test('it generates full mailto link and strictly encodes spaces to kill concatenation mutants', function () {
    $email = new Email;
    $email->create([
        'test@example.com',
        'Hello World',
        'This is the body',
        'cc@example.com',
        'bcc@example.com',
    ]);

    expect((string) $email)->toBe(
        'mailto:test@example.com?subject=Hello%20World&body=This%20is%20the%20body&cc=cc%40example.com&bcc=bcc%40example.com'
    );
});

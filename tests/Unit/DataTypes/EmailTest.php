<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\Email;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidEmailArgumentException;

covers(Email::class);

it('throws exception if email address is missing', function (): void {
    expect(fn (): Email => new Email)
        ->toThrow(TypeError::class);
});

it('throws exception if main email address is invalid', function (): void {
    expect(fn (): Email => new Email('not-an-email'))
        ->toThrow(InvalidEmailArgumentException::class, 'Invalid email address provided to Email.');
});

it('throws exception if cc email is invalid', function (): void {
    expect(fn (): Email => new Email('test@example.com', 'Sub', 'Body', 'invalid-cc'))
        ->toThrow(InvalidEmailArgumentException::class, 'Invalid email address provided to Email.');
});

it('throws exception if bcc email is invalid', function (): void {
    expect(fn (): Email => new Email('test@example.com', 'Sub', 'Body', 'cc@example.com', 'invalid-bcc'))
        ->toThrow(InvalidEmailArgumentException::class, 'Invalid email address provided to Email.');
});

it('generates simple mailto link with only an address', function (): void {
    $email = new Email('test@example.com');

    expect((string) $email)->toBe('mailto:test@example.com');
});

it('handles varying arguments lengths', function (): void {
    $email2 = new Email('test@example.com', 'Subject');

    expect((string) $email2)->toBe('mailto:test@example.com?subject=Subject');

    $email3 = new Email('test@example.com', 'Subject', 'Body');

    expect((string) $email3)->toBe('mailto:test@example.com?subject=Subject&body=Body');
    $email4 = new Email('test@example.com', 'Subject', 'Body', 'cc@example.com');

    expect((string) $email4)->toBe('mailto:test@example.com?subject=Subject&body=Body&cc=cc%40example.com');

    $email5 = new Email('test@example.com', 'Subject', 'Body', 'cc@example.com', 'bcc@example.com');

    expect((string) $email5)->toBe(
        'mailto:test@example.com?subject=Subject&body=Body&cc=cc%40example.com&bcc=bcc%40example.com'
    );
});

it('generates full mailto link and strictly encodes spaces', function (): void {
    $email = new Email(
        'test@example.com',
        'Hello World',
        'This is the body',
        'cc@example.com',
        'bcc@example.com'
    );

    expect((string) $email)->toBe(
        'mailto:test@example.com?subject=Hello%20World&body=This%20is%20the%20body&cc=cc%40example.com&bcc=bcc%40example.com'
    );
});

it('ignores empty subject, body', function (): void {
    $email = new Email('test@example.com', '', '');

    expect((string) $email)->toBe('mailto:test@example.com');
});

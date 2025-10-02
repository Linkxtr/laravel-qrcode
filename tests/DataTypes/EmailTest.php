<?php

namespace Linkxtr\QrCode\Tests\DataTypes;

use InvalidArgumentException;
use Linkxtr\QrCode\DataTypes\Email;

beforeEach(function () {
    $this->email = new Email;
});

it('should generate a valid email QR code', function () {
    $this->email->create(['email@example.com']);
    expect(strval($this->email))->toBe('mailto:email@example.com');
});

it('should generate a valid email QR code with subject', function () {
    $this->email->create(['email@example.com', 'subject']);
    expect(strval($this->email))->toBe('mailto:email@example.com?subject=subject');
});

it('should generate a valid email QR code with subject and body', function () {
    $this->email->create(['email@example.com', 'subject', 'body']);
    expect(strval($this->email))->toBe('mailto:email@example.com?subject=subject&body=body');
});

it('should generate a valid email QR code with subject only', function () {
    $this->email->create([null, 'subject']);
    expect(strval($this->email))->toBe('mailto:?subject=subject');
});

it('throws an exception when the email is invalid', function () {
    expect(fn () => $this->email->create(['invalid-email']))
        ->toThrow(InvalidArgumentException::class);
});

it('should generate a valid email QR code with cc', function () {
    $this->email->create(['email@example.com', 'subject', 'body', 'cc']);
    expect(strval($this->email))->toBe('mailto:email@example.com?subject=subject&body=body&cc=cc');
});

it('should generate a valid email QR code with cc and bcc', function () {
    $this->email->create(['email@example.com', 'subject', 'body', 'cc', 'bcc']);
    expect(strval($this->email))->toBe('mailto:email@example.com?subject=subject&body=body&cc=cc&bcc=bcc');
});

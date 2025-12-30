<?php

use Linkxtr\QrCode\DataTypes\Email;

covers(Email::class);

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
    expect(fn () => $this->email->create([1234]))
        ->toThrow(InvalidArgumentException::class);
});

it('should generate a valid email QR code with cc', function () {
    $this->email->create(['email@example.com', 'subject', 'body', 'cc@example.com']);
    expect(strval($this->email))->toBe('mailto:email@example.com?subject=subject&body=body&cc=cc%40example.com');
});

it('should generate a valid email QR code with cc and bcc', function () {
    $this->email->create(['email@example.com', 'subject', 'body', 'cc@example.com', 'bcc@example.com']);
    expect(strval($this->email))->toBe('mailto:email@example.com?subject=subject&body=body&cc=cc%40example.com&bcc=bcc%40example.com');
});

it('throws an exception when cc is invalid', function () {
    expect(fn () => $this->email->create(['email@example.com', 'subject', 'body', 'invalid-email']))
        ->toThrow(InvalidArgumentException::class);
    expect(fn () => $this->email->create(['email@example.com', 'subject', 'body', 1234]))
        ->toThrow(InvalidArgumentException::class);
});

it('throws an exception when bcc is invalid', function () {
    expect(fn () => $this->email->create(['email@example.com', 'subject', 'body', 'cc@example.com', 'invalid-email']))
        ->toThrow(InvalidArgumentException::class);
    expect(fn () => $this->email->create(['email@example.com', 'subject', 'body', 'cc@example.com', 1234]))
        ->toThrow(InvalidArgumentException::class);
});

it('throws an exception when body is invalid', function () {
    expect(fn () => $this->email->create(['email@example.com', 'subject', 1234]))
        ->toThrow(InvalidArgumentException::class);
    expect(fn () => $this->email->create(['email@example.com', 'subject', ['foo' => 'bar']]))
        ->toThrow(InvalidArgumentException::class);
});

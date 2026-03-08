<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\WiFi;

covers(WiFi::class);

beforeEach(function () {
    $this->wifi = new WiFi;
});

it('should generate a valid WiFi QR code with just the SSID', function () {
    $this->wifi->create([
        0 => [
            'ssid' => 'SSID',
        ],
    ]);
    expect(strval($this->wifi))->toBe('WIFI:S:SSID;');
});

it('throws exception if not initialized before string conversion', function () {
    expect(fn () => strval($this->wifi))->toThrow(InvalidArgumentException::class, 'WiFi SSID is required.');
});

it('should generate a valid WiFi QR code with SSID and password', function () {
    $this->wifi->create([
        0 => [
            'ssid' => 'SSID',
            'password' => 'password',
        ],
    ]);
    expect(strval($this->wifi))->toBe('WIFI:T:WPA;S:SSID;P:password;');
});

it('should generate a valid WiFi QR code for a hidden SSID', function () {
    $this->wifi->create([
        0 => [
            'ssid' => 'SSID',
            'hidden' => true,
        ],
    ]);
    expect(strval($this->wifi))->toBe('WIFI:S:SSID;H:true;');
});

it('should generate a valid WiFi QR code for a hidden SSID and password', function () {
    $this->wifi->create([
        0 => [
            'ssid' => 'SSID',
            'hidden' => true,
            'password' => 'password',
        ],
    ]);
    expect(strval($this->wifi))->toBe('WIFI:T:WPA;S:SSID;P:password;H:true;');
});

it('should generate a valid WiFi QR code with WEP encryption', function () {
    $this->wifi->create([
        0 => [
            'ssid' => 'SSID',
            'password' => 'password',
            'encryption' => 'WEP',
        ],
    ]);
    expect(strval($this->wifi))->toBe('WIFI:T:WEP;S:SSID;P:password;');
});

it('should generate a valid WiFi QR code with WPA2 encryption', function () {
    $this->wifi->create([
        0 => [
            'ssid' => 'SSID',
            'password' => 'password',
            'encryption' => 'WPA2',
        ],
    ]);
    expect(strval($this->wifi))->toBe('WIFI:T:WPA2;S:SSID;P:password;');
});

it('should generate a valid WiFi QR code with nopass encryption', function () {
    $this->wifi->create([
        0 => [
            'ssid' => 'SSID',
            'encryption' => 'nopass',
        ],
    ]);
    expect(strval($this->wifi))->toBe('WIFI:T:nopass;S:SSID;');
});

it('should ignore password if nopass encryption is specified', function () {
    $this->wifi->create([
        0 => [
            'ssid' => 'SSID',
            'password' => 'password123',
            'encryption' => 'nopass',
        ],
    ]);
    expect(strval($this->wifi))->toBe('WIFI:T:nopass;S:SSID;');
});

it('throws an exception when an invalid encryption type is provided', function () {
    expect(fn () => $this->wifi->create([
        0 => [
            'ssid' => 'SSID',
            'encryption' => 'INVALID',
        ],
    ]))->toThrow(InvalidArgumentException::class, 'Invalid encryption type. Supported types are WEP, WPA, WPA2, nopass.');
});

it('should handle case-insensitive encryption values', function () {
    $this->wifi->create([
        0 => [
            'ssid' => 'SSID',
            'password' => 'password',
            'encryption' => 'wpa2',
        ],
    ]);
    expect(strval($this->wifi))->toBe('WIFI:T:WPA2;S:SSID;P:password;');
});

it('throws an exception when SSID is missing', function () {
    expect(fn () => $this->wifi->create([]))
        ->toThrow(InvalidArgumentException::class);
    expect(fn () => $this->wifi->create([
        0 => [
            'ssid' => '',
        ],
    ]))
        ->toThrow(InvalidArgumentException::class);
    expect(fn () => $this->wifi->create([
        0 => [
            'password' => 'password',
        ],
    ]))
        ->toThrow(InvalidArgumentException::class);
    expect(fn () => $this->wifi->create([
        0 => [
            'hidden' => true,
        ],
    ]))
        ->toThrow(InvalidArgumentException::class);

    expect(fn () => $this->wifi->create([
        0 => [
            'hidden' => true,
            'password' => 'password',
        ],
    ]))
        ->toThrow(InvalidArgumentException::class);
});

it('should normalize uppercase NOPASS to lowercase nopass', function () {
    $this->wifi->create([
        0 => [
            'ssid' => 'SSID',
            'encryption' => 'NOPASS',
        ],
    ]);
    expect(strval($this->wifi))->toBe('WIFI:T:nopass;S:SSID;');
});

it('should generate a valid WiFi QR code with WPA encryption', function () {
    $this->wifi->create([
        0 => [
            'ssid' => 'SSID',
            'password' => 'password',
            'encryption' => 'WPA',
        ],
    ]);
    expect(strval($this->wifi))->toBe('WIFI:T:WPA;S:SSID;P:password;');
});

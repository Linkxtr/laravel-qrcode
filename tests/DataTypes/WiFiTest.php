<?php

namespace Linkxtr\QrCode\Tests\DataTypes;

use InvalidArgumentException;
use Linkxtr\QrCode\DataTypes\WiFi;

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

it('throws an exception when SSID is missing', function () {
    expect(fn () => $this->wifi->create([]))
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

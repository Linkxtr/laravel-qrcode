<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\WiFi;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidWiFiArgumentException;

covers(WiFi::class);

test('it throws exception if ssid is missing or empty', function (): void {
    expect(fn (): WiFi => new WiFi)
        ->toThrow(TypeError::class);

    expect(fn (): WiFi => new WiFi(''))
        ->toThrow(InvalidWiFiArgumentException::class, 'WiFi SSID must be a non-empty string.');
});

test('it throws exception if encryption is invalid', function (): void {
    expect(fn (): WiFi => new WiFi('MyWiFi', 'INVALID_ENCRYPTION'))
        ->toThrow(InvalidWiFiArgumentException::class, 'WiFi encryption must be WEP, WPA, WPA2, WPA3 or NOPASS. Provided encryption: INVALID_ENCRYPTION');
});

test('it maps positional arguments and normalizes encryption case', function (): void {
    $wifi = new WiFi('HomeNetwork', 'wpa', 'secret123', true);

    expect((string) $wifi)->toBe('WIFI:S:HomeNetwork;T:WPA;P:secret123;H:true;;');
});

test('it maps associative arguments and falls back to smart defaults correctly', function (): void {
    $wifi1 = new WiFi(ssid: 'PublicWiFi');

    expect((string) $wifi1)->toBe('WIFI:S:PublicWiFi;T:NOPASS;;');

    $wifi2 = new WiFi(ssid: 'PrivateWiFi', password: 'secret123');

    expect((string) $wifi2)->toBe('WIFI:S:PrivateWiFi;T:WPA;P:secret123;;');

    $wifi3 = new WiFi(ssid: 'OldRouter', encryption: 'WEP', password: '12345');

    expect((string) $wifi3)->toBe('WIFI:S:OldRouter;T:WEP;P:12345;;');
});

test('it strips empty string parameters', function (): void {
    $wifi = new WiFi('HomeNetwork', '', '', false);

    expect((string) $wifi)->toBe('WIFI:S:HomeNetwork;T:NOPASS;;');
});

test('it safely escapes special characters in ssid and password', function (): void {
    $chaosSsid = 'Net\\Work;Name';
    $chaosPass = 'Pass:Word,Here';

    $wifi = new WiFi($chaosSsid, 'WPA', $chaosPass);

    $expected = 'WIFI:S:Net\\\\Work\\;Name;T:WPA;P:Pass\\:Word\\,Here;;';

    expect((string) $wifi)->toBe($expected);
});

test('it explicitly allows NOPASS encryption', function (): void {
    $wifi = new WiFi(ssid: 'PublicNet', encryption: 'NOPASS');

    expect((string) $wifi)->toBe('WIFI:S:PublicNet;T:NOPASS;;');
});

test('it safely casts non-boolean hidden values to boolean', function (): void {
    $wifi = new WiFi(ssid: 'HiddenNet', hidden: (bool) 1);

    expect((string) $wifi)->toBe('WIFI:S:HiddenNet;T:NOPASS;H:true;;');
});

it('strictly parses hidden flag values as boolean, rejecting invalid strings', function (): void {
    $wifi = new WiFi(ssid: 'VisibleNet', hidden: false);

    expect((string) $wifi)->toBe('WIFI:S:VisibleNet;T:NOPASS;;');

    $wifi2 = new WiFi(ssid: 'VisibleNet', hidden: true);

    expect((string) $wifi2)->toBe('WIFI:S:VisibleNet;T:NOPASS;H:true;;');
});

test('it mathematically enforces NOPASS encryption cannot have a password', function (): void {
    expect(fn (): WiFi => new WiFi(ssid: 'MyNetwork', encryption: 'NOPASS', password: 'Secret123'))
        ->toThrow(InvalidWiFiArgumentException::class, 'WiFi password cannot be provided when encryption is NOPASS.');

    expect(fn (): WiFi => new WiFi('MyNetwork', 'NOPASS', 'Secret123'))
        ->toThrow(InvalidWiFiArgumentException::class, 'WiFi password cannot be provided when encryption is NOPASS.');
});

it('converts WPA2 and WPA3 to WPA', function (): void {
    $wifi = new WiFi(ssid: 'HomeNetwork', encryption: 'WPA2');
    expect((string) $wifi)->toBe('WIFI:S:HomeNetwork;T:WPA;;');

    $wifi2 = new WiFi(ssid: 'HomeNetwork', encryption: 'WPA3');

    expect((string) $wifi2)->toBe('WIFI:S:HomeNetwork;T:WPA;;');
});

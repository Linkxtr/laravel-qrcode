<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\WiFi;

covers(WiFi::class);

test('it throws exception if rendered before creation', function (): void {
    $wifi = new WiFi;
    expect(fn (): string => (string) $wifi)
        ->toThrow(LogicException::class, 'WiFi must be initialized via create() before rendering.');
});

test('it throws exception if ssid is missing or empty', function (): void {
    $wifi = new WiFi;

    expect(fn () => $wifi->create([]))
        ->toThrow(InvalidArgumentException::class, 'WiFi SSID is mandatory.');

    expect(fn () => $wifi->create(['']))
        ->toThrow(InvalidArgumentException::class, 'WiFi SSID is mandatory.');
});

test('it throws exception if encryption is invalid', function (): void {
    $wifi = new WiFi;

    expect(fn () => $wifi->create(['MyWiFi', 'INVALID_ENCRYPTION']))
        ->toThrow(InvalidArgumentException::class, 'WiFi encryption must be WEP, WPA, or NOPASS.');
});

test('it maps positional arguments and normalizes encryption case', function (): void {
    $wifi = new WiFi;
    $wifi->create(['HomeNetwork', 'wpa', 'secret123', true]);

    expect((string) $wifi)->toBe('WIFI:S:HomeNetwork;T:WPA;P:secret123;H:true;;');
});

test('it maps associative arguments and falls back to smart defaults correctly', function (): void {
    $wifi1 = new WiFi;
    $wifi1->create(['ssid' => 'PublicWiFi']);

    expect((string) $wifi1)->toBe('WIFI:S:PublicWiFi;T:NOPASS;;');
    $wifi2 = new WiFi;
    $wifi2->create(['ssid' => 'PrivateWiFi', 'password' => 'secret123']);

    expect((string) $wifi2)->toBe('WIFI:S:PrivateWiFi;T:WPA;P:secret123;;');
    $wifi3 = new WiFi;
    $wifi3->create(['ssid' => 'OldRouter', 'encryption' => 'WEP', 'password' => '12345']);

    expect((string) $wifi3)->toBe('WIFI:S:OldRouter;T:WEP;P:12345;;');
});

test('it strips empty string parameters', function (): void {
    $wifi = new WiFi;

    $wifi->create(['HomeNetwork', '', '', false]);

    expect((string) $wifi)->toBe('WIFI:S:HomeNetwork;T:NOPASS;;');
});

test('it safely escapes special characters in ssid and password', function (): void {
    $wifi = new WiFi;

    $chaosSsid = 'Net\\Work;Name';
    $chaosPass = 'Pass:Word,Here';

    $wifi->create([$chaosSsid, 'WPA', $chaosPass]);

    $expected = 'WIFI:S:Net\\\\Work\\;Name;T:WPA;P:Pass\\:Word\\,Here;;';

    expect((string) $wifi)->toBe($expected);
});

test('it successfully unpacks a wrapped array from the facade pattern', function (): void {
    $wifi = new WiFi;

    $wifi->create([[
        'ssid' => 'WrappedWiFi',
        'encryption' => 'WPA',
        'password' => 'secret123',
        'hidden' => true,
    ]]);

    expect((string) $wifi)->toBe('WIFI:S:WrappedWiFi;T:WPA;P:secret123;H:true;;');
});

test('it explicitly allows NOPASS encryption', function (): void {
    $wifi = new WiFi;

    $wifi->create(['ssid' => 'PublicNet', 'encryption' => 'NOPASS']);

    expect((string) $wifi)->toBe('WIFI:S:PublicNet;T:NOPASS;;');
});

test('it safely casts non-boolean hidden values to boolean', function (): void {
    $wifi = new WiFi;

    $wifi->create(['ssid' => 'HiddenNet', 'hidden' => 1]);

    expect((string) $wifi)->toBe('WIFI:S:HiddenNet;T:NOPASS;H:true;;');
});

it('strictly parses hidden flag values as boolean, rejecting invalid strings', function (): void {
    $wifi = new WiFi;
    $wifi->create(['ssid' => 'VisibleNet', 'hidden' => 'false']);

    expect((string) $wifi)->toBe('WIFI:S:VisibleNet;T:NOPASS;;');

    $wifi2 = new WiFi;
    $wifi2->create([['ssid' => 'VisibleNet', 'hidden' => 'yes']]);

    expect((string) $wifi2)->toBe('WIFI:S:VisibleNet;T:NOPASS;H:true;;');

    $wifi3 = new WiFi;
    $wifi3->create([['ssid' => 'VisibleNet', 'hidden' => 'invalid-string']]);

    expect((string) $wifi3)->toBe('WIFI:S:VisibleNet;T:NOPASS;;');
});

test('it mathematically enforces NOPASS encryption cannot have a password', function (): void {
    $wifi = new WiFi;

    expect(fn () => $wifi->create([['ssid' => 'MyNetwork', 'encryption' => 'NOPASS', 'password' => 'Secret123']]))
        ->toThrow(InvalidArgumentException::class, 'WiFi password cannot be provided when encryption is NOPASS.');

    $wifiPositional = new WiFi;
    expect(fn () => $wifiPositional->create([['MyNetwork', 'NOPASS', 'Secret123']]))
        ->toThrow(InvalidArgumentException::class, 'WiFi password cannot be provided when encryption is NOPASS.');
});

it('rejects malformed hidden flag values', function (): void {
    $wifi = new WiFi;

    expect(fn () => $wifi->create([['ssid' => 'MyNetwork', 'hidden' => ['nested_array']]]))
        ->toThrow(InvalidArgumentException::class, 'WiFi hidden flag must be a boolean or a string representation of a boolean.');
});

it('converts WPA2 and WPA3 to WPA', function (): void {
    $wifi = new WiFi;

    $wifi->create(['ssid' => 'HomeNetwork', 'encryption' => 'WPA2']);

    expect((string) $wifi)->toBe('WIFI:S:HomeNetwork;T:WPA;;');

    $wifi2 = new WiFi;
    $wifi2->create(['ssid' => 'HomeNetwork', 'encryption' => 'WPA3']);

    expect((string) $wifi2)->toBe('WIFI:S:HomeNetwork;T:WPA;;');
});

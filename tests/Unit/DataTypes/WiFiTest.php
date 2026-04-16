<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\WiFi;

covers(WiFi::class);

test('it throws exception if rendered before creation', function () {
    $wifi = new WiFi;
    expect(fn () => (string) $wifi)
        ->toThrow(LogicException::class, 'WiFi must be initialized via create() before rendering.');
});

test('it throws exception if ssid is missing or empty', function () {
    $wifi = new WiFi;

    expect(fn () => $wifi->create([]))
        ->toThrow(InvalidArgumentException::class, 'WiFi SSID is mandatory.');

    expect(fn () => $wifi->create(['']))
        ->toThrow(InvalidArgumentException::class, 'WiFi SSID is mandatory.');
});

test('it throws exception if encryption is invalid', function () {
    $wifi = new WiFi;

    expect(fn () => $wifi->create(['MyWiFi', 'INVALID_ENCRYPTION']))
        ->toThrow(InvalidArgumentException::class, 'WiFi encryption must be WEP, WPA, or NOPASS.');
});

test('it maps positional arguments and normalizes encryption case', function () {
    $wifi = new WiFi;
    $wifi->create(['HomeNetwork', 'wpa', 'secret123', true]);

    expect((string) $wifi)->toBe('WIFI:S:HomeNetwork;T:WPA;P:secret123;H:true;;');
});

test('it maps associative arguments and falls back to smart defaults correctly', function () {
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

test('it strips empty string parameters to kill boundary mutants', function () {
    $wifi = new WiFi;

    $wifi->create(['HomeNetwork', '', '', false]);

    expect((string) $wifi)->toBe('WIFI:S:HomeNetwork;T:NOPASS;;');
});

test('it safely escapes special characters in ssid and password to kill strtr mutants', function () {
    $wifi = new WiFi;

    $chaosSsid = 'Net\\Work;Name';
    $chaosPass = 'Pass:Word,Here';

    $wifi->create([$chaosSsid, 'WPA', $chaosPass]);

    $expected = 'WIFI:S:Net\\\\Work\\;Name;T:WPA;P:Pass\\:Word\\,Here;;';

    expect((string) $wifi)->toBe($expected);
});

test('it successfully unpacks a wrapped array from the facade pattern to kill wrapper mutants', function () {
    $wifi = new WiFi;

    $wifi->create([[
        'ssid' => 'WrappedWiFi',
        'encryption' => 'WPA',
        'password' => 'secret123',
        'hidden' => true,
    ]]);

    expect((string) $wifi)->toBe('WIFI:S:WrappedWiFi;T:WPA;P:secret123;H:true;;');
});

test('it explicitly allows NOPASS encryption to kill RemoveArrayItem mutant', function () {
    $wifi = new WiFi;

    $wifi->create(['ssid' => 'PublicNet', 'encryption' => 'NOPASS']);

    expect((string) $wifi)->toBe('WIFI:S:PublicNet;T:NOPASS;;');
});

test('it safely casts non-boolean hidden values to boolean to kill cast mutants', function () {
    $wifi = new WiFi;

    $wifi->create(['ssid' => 'HiddenNet', 'hidden' => 1]);

    expect((string) $wifi)->toBe('WIFI:S:HiddenNet;T:NOPASS;H:true;;');
});

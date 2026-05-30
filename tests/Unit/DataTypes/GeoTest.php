<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\Geo;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidGeoArgumentException;

covers(Geo::class);

test('it throws exception if arguments are missing', function (): void {
    expect(fn (): Geo => new Geo(latitude: 37.7749))
        ->toThrow(TypeError::class);
});

test('it throws exception if latitude or longitude are not numeric', function (): void {
    expect(fn (): Geo => new Geo('invalid', -122.4194))
        ->toThrow(TypeError::class);
});

it('generates full geo string from standard floats', function (float $lat, float $lng, string $expected): void {
    $geo = new Geo($lat, $lng);

    expect((string) $geo)->toBe($expected);
})->with([
    'standard' => [37.7749, -122.4194, 'geo:37.7749,-122.4194'],
    'south-west' => [-90.0, -180.0, 'geo:-90,-180'],
    'north-east' => [90.0, 180.0, 'geo:90,180'],
]);

test('it safely casts numeric strings to floats to kill cast mutants', function (): void {
    $geo = new Geo((float) '37.7749', (float) '-122.4194');

    expect((string) $geo)->toBe('geo:37.7749,-122.4194');
});

test('it accurately renders zero coordinates without stripping them', function (): void {
    $geo = new Geo(0, 0);

    expect((string) $geo)->toBe('geo:0,0');
});

test('it appends optional name and strictly encodes spaces', function (): void {
    $geo = new Geo(37.7749, -122.4194, 'Golden Gate Bridge');

    expect((string) $geo)->toBe('geo:37.7749,-122.4194?name=Golden%20Gate%20Bridge')
        ->and((string) $geo)->not->toContain('+');
});

test('it throws an exception for out-of-bounds latitude and longitude', function (): void {
    expect(fn (): Geo => new Geo(-90.000001, 0))
        ->toThrow(InvalidGeoArgumentException::class, 'Latitude must be between -90 and 90.');

    expect(fn (): Geo => new Geo(90.000001, 0))
        ->toThrow(InvalidGeoArgumentException::class, 'Latitude must be between -90 and 90.');

    expect(fn (): Geo => new Geo(0, -180.000001))
        ->toThrow(InvalidGeoArgumentException::class, 'Longitude must be between -180 and 180.');

    expect(fn (): Geo => new Geo(0, 180.000001))
        ->toThrow(InvalidGeoArgumentException::class, 'Longitude must be between -180 and 180.');
});

it('formats small coordinates correctly', function (): void {
    $geo = new Geo(0.0000001, -0.0000002);

    expect((string) $geo)->toBe('geo:0.0000001,-0.0000002');
});

it('preserves high precision coordinates beyond 10 decimal places', function (): void {
    $geo = new Geo(40.123456789123, -74.123456789123);

    expect((string) $geo)->toBe('geo:40.123456789123,-74.123456789123');
});

it('handles extremely small scientific notation coordinates by converting them to zero', function (): void {
    $geo = new Geo(1.0E-11, -1.0E-11);

    expect((string) $geo)->toBe('geo:0,0');
});

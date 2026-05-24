<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\Geo;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidGeoArgumentException;
use Linkxtr\QrCode\Exceptions\UninitializedDataTypeException;

covers(Geo::class);

it('throws exception if rendered before creation', function (): void {
    expect(fn (): string => (string) new Geo)
        ->toThrow(UninitializedDataTypeException::class, 'Geo must be initialized via create() before rendering.');
});

test('it throws exception if arguments are missing', function (): void {
    $geo = new Geo;

    expect(fn () => $geo->create([]))
        ->toThrow(InvalidGeoArgumentException::class, 'Latitude and longitude are required.');

    expect(fn () => $geo->create([37.7749]))
        ->toThrow(InvalidGeoArgumentException::class, 'Latitude and longitude are required.');

    expect(fn () => $geo->create([1 => -122.4194]))
        ->toThrow(InvalidGeoArgumentException::class, 'Latitude and longitude are required.');
});

test('it throws exception if latitude or longitude are not numeric', function (): void {
    $geo = new Geo;

    expect(fn () => $geo->create(['invalid', -122.4194]))
        ->toThrow(InvalidGeoArgumentException::class, 'Latitude and longitude must be numeric. Provided types: string, double');

    expect(fn () => $geo->create([37.7749, 'invalid']))
        ->toThrow(InvalidGeoArgumentException::class, 'Latitude and longitude must be numeric. Provided types: double, string');
});

it('generates full geo string from standard floats', function (float $lat, float $lng, string $expected): void {
    $geo = new Geo;

    $geo->create([$lat, $lng]);

    expect((string) $geo)->toBe($expected);
})->with([
    'standard' => [37.7749, -122.4194, 'geo:37.7749,-122.4194'],
    'south-west' => [-90.0, -180.0, 'geo:-90,-180'],
    'north-east' => [90.0, 180.0, 'geo:90,180'],
]);

test('it safely casts numeric strings to floats to kill cast mutants', function (): void {
    $geo = new Geo;

    $geo->create(['37.7749', '-122.4194']);

    expect((string) $geo)->toBe('geo:37.7749,-122.4194');
});

test('it accurately renders zero coordinates without stripping them', function (): void {
    $geo = new Geo;

    $geo->create([0, 0]);

    expect((string) $geo)->toBe('geo:0,0');
});

test('it throws exception if name is not a string', function (): void {
    $geo = new Geo;
    expect(fn () => $geo->create([37.7749, -122.4194, 12345]))
        ->toThrow(InvalidGeoArgumentException::class, 'Geo name must be a string. Provided type: integer');
});

test('it intercepts and ignores empty string names', function (): void {
    $geo = new Geo;
    $geo->create([37.7749, -122.4194, '']);

    expect((string) $geo)->toBe('geo:37.7749,-122.4194');
});

test('it appends optional name and strictly encodes spaces', function (): void {
    $geo = new Geo;
    $geo->create([37.7749, -122.4194, 'Golden Gate Bridge']);

    expect((string) $geo)->toBe('geo:37.7749,-122.4194?name=Golden%20Gate%20Bridge')
        ->and((string) $geo)->not->toContain('+');
});

test('it throws an exception for out-of-bounds latitude and longitude', function (): void {
    $geo = new Geo;

    expect(fn () => $geo->create([-90.000001, 0]))
        ->toThrow(InvalidGeoArgumentException::class, 'Latitude must be between -90 and 90.');

    expect(fn () => $geo->create([90.000001, 0]))
        ->toThrow(InvalidGeoArgumentException::class, 'Latitude must be between -90 and 90.');

    expect(fn () => $geo->create([0, -180.000001]))
        ->toThrow(InvalidGeoArgumentException::class, 'Longitude must be between -180 and 180.');

    expect(fn () => $geo->create([0, 180.000001]))
        ->toThrow(InvalidGeoArgumentException::class, 'Longitude must be between -180 and 180.');
});

test('it clears the name if null is passed as the third argument', function (): void {
    $geo = new Geo;

    $geo->create([10.0, 20.0, 'Initial Name']);

    expect((string) $geo)->toContain('Initial%20Name');

    $geo->create([15.0, 25.0, null]);

    expect((string) $geo)->not->toContain('Initial%20Name')
        ->and((string) $geo)->toBe('geo:15,25');
});

test('it throws a required exception when null is passed as latitude or longitude', function (): void {
    $geo = new Geo;

    expect(fn () => $geo->create([null, -122.4194]))
        ->toThrow(InvalidGeoArgumentException::class, 'Latitude and longitude are required.');

    expect(fn () => $geo->create([37.7749, null]))
        ->toThrow(InvalidGeoArgumentException::class, 'Latitude and longitude are required.');
});

it('formats small coordinates correctly', function (): void {
    $geo = new Geo;
    $geo->create([0.0000001, -0.0000002]);

    expect((string) $geo)->toBe('geo:0.0000001,-0.0000002');
});

it('preserves high precision coordinates beyond 10 decimal places', function (): void {
    $geo = new Geo;

    $geo->create([40.123456789123, -74.123456789123]);

    expect((string) $geo)->toBe('geo:40.123456789123,-74.123456789123');
});

it('handles extremely small scientific notation coordinates by converting them to zero', function (): void {
    $geo = new Geo;

    $geo->create([1.0E-11, -1.0E-11]);

    expect((string) $geo)->toBe('geo:0,0');
});

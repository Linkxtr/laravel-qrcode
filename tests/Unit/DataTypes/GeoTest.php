<?php

declare(strict_types=1);

use Linkxtr\QrCode\DataTypes\Geo;

covers(Geo::class);

test('it throws exception if arguments are missing', function () {
    $geo = new Geo;

    expect(fn () => $geo->create([]))
        ->toThrow(InvalidArgumentException::class, 'Latitude and longitude are required.');

    expect(fn () => $geo->create([37.7749]))
        ->toThrow(InvalidArgumentException::class, 'Latitude and longitude are required.');

    expect(fn () => $geo->create([1 => -122.4194]))
        ->toThrow(InvalidArgumentException::class, 'Latitude and longitude are required.');
});

test('it throws exception if latitude or longitude are not numeric', function () {
    $geo = new Geo;

    expect(fn () => $geo->create(['invalid', -122.4194]))
        ->toThrow(InvalidArgumentException::class, 'Latitude and longitude must be numeric.');

    expect(fn () => $geo->create([37.7749, 'invalid']))
        ->toThrow(InvalidArgumentException::class, 'Latitude and longitude must be numeric.');
});

test('it generates full geo string from standard floats', function () {
    $geo = new Geo;
    $geo->create([37.7749, -122.4194]);

    expect((string) $geo)->toBe('geo:37.7749,-122.4194');
});

test('it safely casts numeric strings to floats to kill cast mutants', function () {
    $geo = new Geo;

    $geo->create(['37.7749', '-122.4194']);

    expect((string) $geo)->toBe('geo:37.7749,-122.4194');
});

test('it accurately renders zero coordinates without stripping them', function () {
    $geo = new Geo;

    $geo->create([0, 0]);

    expect((string) $geo)->toBe('geo:0,0');
});

test('it throws exception if name is not a string', function () {
    $geo = new Geo;
    expect(fn () => $geo->create([37.7749, -122.4194, 12345]))
        ->toThrow(InvalidArgumentException::class, 'Geo name must be a string.');
});

test('it intercepts and ignores empty string names to kill empty block mutants', function () {
    $geo = new Geo;
    $geo->create([37.7749, -122.4194, '']);

    expect((string) $geo)->toBe('geo:37.7749,-122.4194');
});

test('it appends optional name and strictly encodes spaces to kill concatenation mutants', function () {
    $geo = new Geo;
    $geo->create([37.7749, -122.4194, 'Golden Gate Bridge']);

    expect((string) $geo)->toBe('geo:37.7749,-122.4194?name=Golden%20Gate%20Bridge')
        ->and((string) $geo)->not->toContain('+');
});

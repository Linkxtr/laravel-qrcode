<?php

namespace Linkxtr\QrCode\Tests\DataTypes;

use InvalidArgumentException;
use Linkxtr\QrCode\DataTypes\Geo;

covers(Geo::class);

beforeEach(function () {
    $this->geo = new Geo;
});

it('should generate a valid geo QR code with name', function () {
    $this->geo->create(['40.7128', '-74.0060', 'New York']);
    expect(strval($this->geo))->toBe('geo:40.7128,-74.006?name=New+York');
});

it('should generate a valid geo QR code without name', function () {
    $this->geo->create(['40.7128', '-74.0060']);
    expect(strval($this->geo))->toBe('geo:40.7128,-74.006');
});

it('should generate a valid geo Qr code with integer and float latitude and longitude', function () {
    $this->geo->create([90, -180]);
    expect(strval($this->geo))->toBe('geo:90,-180');
    $this->geo->create([-90, 180]);
    expect(strval($this->geo))->toBe('geo:-90,180');
    $this->geo->create([40.7128, -74.0060]);
    expect(strval($this->geo))->toBe('geo:40.7128,-74.006');
});

it('throws an exception when latitude is missing', function () {
    expect(fn () => $this->geo->create([null, '-74.0060']))
        ->toThrow(InvalidArgumentException::class, 'Both latitude and longitude are required.');
});

it('throws an exception when longitude is missing', function () {
    expect(fn () => $this->geo->create(['40.7128', null]))
        ->toThrow(InvalidArgumentException::class, 'Both latitude and longitude are required.');
});

it('throws an exception when latitude is not numeric', function () {
    expect(fn () => $this->geo->create(['invalid', '-74.0060']))
        ->toThrow(InvalidArgumentException::class, 'Invalid latitude value: must be a number');
});

it('throws an exception when longitude is not numeric', function () {
    expect(fn () => $this->geo->create(['40.7128', 'invalid']))
        ->toThrow(InvalidArgumentException::class, 'Invalid longitude value: must be a number');
});

it('throws an exception when latitude is out of range', function () {
    expect(fn () => $this->geo->create(['-91', '0']))
        ->toThrow(InvalidArgumentException::class, 'Latitude must be between -90 and 90 degrees');

    expect(fn () => $this->geo->create(['91', '0']))
        ->toThrow(InvalidArgumentException::class, 'Latitude must be between -90 and 90 degrees');
});

it('throws an exception when longitude is out of range', function () {
    expect(fn () => $this->geo->create(['0', '-181']))
        ->toThrow(InvalidArgumentException::class, 'Longitude must be between -180 and 180 degrees');

    expect(fn () => $this->geo->create(['0', '181']))
        ->toThrow(InvalidArgumentException::class, 'Longitude must be between -180 and 180 degrees');
});

it('throws an exception when name is not a string', function () {
    expect(fn () => $this->geo->create(['40.7128', '-74.0060', ['foo' => 'bar']]))
        ->toThrow(InvalidArgumentException::class, 'Invalid name value: must be a string');
});

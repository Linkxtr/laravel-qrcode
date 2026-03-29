<?php

declare(strict_types=1);

use Linkxtr\QrCode\ValueObjects\ColorValue;

Covers(ColorValue::class);

it('can create a color value', function () {
    $color = new ColorValue(255, 0, 0);
    expect($color->c1)->toBe(255);
    expect($color->c2)->toBe(0);
    expect($color->c3)->toBe(0);
    expect($color->c4)->toBeNull();
});

it('can create a color value with alpha', function () {
    $color = new ColorValue(255, 0, 0, 100);
    expect($color->c1)->toBe(255);
    expect($color->c2)->toBe(0);
    expect($color->c3)->toBe(0);
    expect($color->c4)->toBe(100);
});

it('throws an exception if c1 is out of range', function () {
    expect(fn () => new ColorValue(256, 0, 0))->toThrow(InvalidArgumentException::class);
});

it('throws an exception if c2 is out of range', function () {
    expect(fn () => new ColorValue(255, 256, 0))->toThrow(InvalidArgumentException::class);
});

it('throws an exception if c3 is out of range', function () {
    expect(fn () => new ColorValue(255, 0, 256))->toThrow(InvalidArgumentException::class);
});

it('throws an exception if c4 is out of range', function () {
    expect(fn () => new ColorValue(255, 0, 0, 101))->toThrow(InvalidArgumentException::class);
});

it('throws an exception if c1 is negative', function () {
    expect(fn () => new ColorValue(-1, 0, 0))->toThrow(InvalidArgumentException::class);
});

it('throws an exception if c4 is negative', function () {
    expect(fn () => new ColorValue(255, 0, 0, -1))->toThrow(InvalidArgumentException::class);
});

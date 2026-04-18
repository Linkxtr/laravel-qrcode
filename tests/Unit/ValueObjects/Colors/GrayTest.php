<?php

declare(strict_types=1);

use BaconQrCode\Renderer\Color\Gray as BaconGray;
use Linkxtr\QrCode\ValueObjects\Colors\Cmyk;
use Linkxtr\QrCode\ValueObjects\Colors\Gray;
use Linkxtr\QrCode\ValueObjects\Colors\Rgb;

covers(Gray::class);

test('it throws exception if Gray values breach the 0 and 100 boundaries', function () {
    expect(fn () => new Gray(-1))->toThrow(InvalidArgumentException::class, 'Gray must be between 0 and 100.');
    expect(fn () => new Gray(101))->toThrow(InvalidArgumentException::class, 'Gray must be between 0 and 100.');
});

test('it dynamically assigns and returns alpha boundaries', function () {
    expect((new Gray(0, 50))->getAlpha())->toBe(50);
    expect(fn () => new Gray(0, 101))->toThrow(InvalidArgumentException::class, 'Alpha must be between 0 and 100.');
});

test('it converts gray to rgb', function () {
    expect((new Gray(0))->toRgb())->toEqual(new Rgb(0, 0, 0));
    expect((new Gray(100))->toRgb())->toEqual(new Rgb(255, 255, 255));
    expect((new Gray(50))->toRgb())->toEqual(new Rgb(128, 128, 128));
});

test('it converts gray to rgb with alpha', function () {
    expect((new Gray(0, 50))->toRgb())->toEqual(new Rgb(0, 0, 0, 50));
});

test('it converts gray to cmyk', function () {
    expect((new Gray(0))->toCmyk())->toEqual(new Cmyk(0, 0, 0, 100));
});

test('it converts gray to cmyk with alpha', function () {
    expect((new Gray(0, 50))->toCmyk())->toEqual(new Cmyk(0, 0, 0, 100, 50));
});

test('it returns itself when calling toGray', function () {
    $gray = new Gray(0);
    expect($gray->toGray())->toBe($gray);
});

test('it converts gray to bacon color', function () {
    expect((new Gray(0))->toBaconColor())->toEqual(new BaconGray(0));
});

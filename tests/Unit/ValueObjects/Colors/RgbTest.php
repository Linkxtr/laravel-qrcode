<?php

declare(strict_types=1);

use BaconQrCode\Renderer\Color\Rgb as BaconRgb;
use Linkxtr\QrCode\ValueObjects\Colors\Cmyk;
use Linkxtr\QrCode\ValueObjects\Colors\Gray;
use Linkxtr\QrCode\ValueObjects\Colors\Rgb;

covers(Rgb::class);

test('it throws exception if RGB values breach the 0 and 255 boundaries', function () {
    expect(fn () => new Rgb(-1, 255, 255))->toThrow(InvalidArgumentException::class, 'Red must be between 0 and 255.');
    expect(fn () => new Rgb(256, 255, 255))->toThrow(InvalidArgumentException::class, 'Red must be between 0 and 255.');

    expect(fn () => new Rgb(255, -1, 255))->toThrow(InvalidArgumentException::class, 'Green must be between 0 and 255.');
    expect(fn () => new Rgb(255, 256, 255))->toThrow(InvalidArgumentException::class, 'Green must be between 0 and 255.');

    expect(fn () => new Rgb(255, 255, -1))->toThrow(InvalidArgumentException::class, 'Blue must be between 0 and 255.');
    expect(fn () => new Rgb(255, 255, 256))->toThrow(InvalidArgumentException::class, 'Blue must be between 0 and 255.');
});

test('it successfully parses full 6-character and 3-character hex strings', function () {
    expect((Rgb::fromHex('#FF5733'))->red)->toBe(255);
    expect((Rgb::fromHex('FF5733'))->green)->toBe(87);
    expect((Rgb::fromHex('#F53'))->blue)->toBe(51);
});

test('it throws exception if hex string is invalid', function () {
    expect(fn () => Rgb::fromHex('#FF'))->toThrow(InvalidArgumentException::class, 'Invalid hex color format. Must be 3 or 6 characters.');
    expect(fn () => Rgb::fromHex('#ZZZZZZ'))->toThrow(InvalidArgumentException::class, 'Invalid hex color string provided.');
});

test('it dynamically assigns and returns alpha boundaries', function () {
    expect((new Rgb(0, 0, 0, 50))->getAlpha())->toBe(50);
    expect(fn () => new Rgb(0, 0, 0, -1))->toThrow(InvalidArgumentException::class, 'Alpha must be between 0 and 100.');
    expect(fn () => new Rgb(0, 0, 0, 101))->toThrow(InvalidArgumentException::class, 'Alpha must be between 0 and 100.');
});

test('it converts rgb to cmyk', function () {
    expect((new Rgb(0, 0, 0))->toCmyk())->toEqual(new Cmyk(0, 0, 0, 100));
    expect((new Rgb(255, 255, 255))->toCmyk())->toEqual(new Cmyk(0, 0, 0, 0));
    expect((new Rgb(255, 0, 0))->toCmyk())->toEqual(new Cmyk(0, 100, 100, 0));
    expect((new Rgb(0, 255, 0))->toCmyk())->toEqual(new Cmyk(100, 0, 100, 0));
    expect((new Rgb(0, 0, 255))->toCmyk())->toEqual(new Cmyk(100, 100, 0, 0));
    expect((new Rgb(255, 255, 0))->toCmyk())->toEqual(new Cmyk(0, 0, 100, 0));
    expect((new Rgb(255, 0, 255))->toCmyk())->toEqual(new Cmyk(0, 100, 0, 0));
    expect((new Rgb(0, 255, 255))->toCmyk())->toEqual(new Cmyk(100, 0, 0, 0));
    expect((new Rgb(255, 255, 255))->toCmyk())->toEqual(new Cmyk(0, 0, 0, 0));
});

test('it converts rgb to gray', function () {
    expect((new Rgb(0, 0, 0))->toGray())->toEqual(new Gray(0));
    expect((new Rgb(255, 255, 255))->toGray())->toEqual(new Gray(100));
    expect((new Rgb(255, 0, 0))->toGray())->toEqual(new Gray(30));
    expect((new Rgb(0, 255, 0))->toGray())->toEqual(new Gray(59));
    expect((new Rgb(0, 0, 255))->toGray())->toEqual(new Gray(11));
    expect((new Rgb(255, 255, 0))->toGray())->toEqual(new Gray(89));
    expect((new Rgb(255, 0, 255))->toGray())->toEqual(new Gray(41));
    expect((new Rgb(0, 255, 255))->toGray())->toEqual(new Gray(70));
    expect((new Rgb(128, 128, 128))->toGray())->toEqual(new Gray(50));
});

test('it converts rgb to bacon color', function () {
    expect((new Rgb(0, 0, 0))->toBaconColor())->toEqual(new BaconRgb(0, 0, 0));
    expect((new Rgb(255, 255, 255))->toBaconColor())->toEqual(new BaconRgb(255, 255, 255));
    expect((new Rgb(255, 0, 0))->toBaconColor())->toEqual(new BaconRgb(255, 0, 0));
    expect((new Rgb(0, 255, 0))->toBaconColor())->toEqual(new BaconRgb(0, 255, 0));
    expect((new Rgb(0, 0, 255))->toBaconColor())->toEqual(new BaconRgb(0, 0, 255));
    expect((new Rgb(255, 255, 0))->toBaconColor())->toEqual(new BaconRgb(255, 255, 0));
    expect((new Rgb(255, 0, 255))->toBaconColor())->toEqual(new BaconRgb(255, 0, 255));
    expect((new Rgb(0, 255, 255))->toBaconColor())->toEqual(new BaconRgb(0, 255, 255));
    expect((new Rgb(128, 128, 128))->toBaconColor())->toEqual(new BaconRgb(128, 128, 128));
});

test('it returns itself when calling toRgb', function () {
    $rgb = new Rgb(0, 0, 0);
    expect($rgb->toRgb())->toBe($rgb);
});

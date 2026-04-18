<?php

declare(strict_types=1);

use BaconQrCode\Renderer\Color\Cmyk as BaconCmyk;
use Linkxtr\QrCode\ValueObjects\Colors\Cmyk;
use Linkxtr\QrCode\ValueObjects\Colors\Gray;
use Linkxtr\QrCode\ValueObjects\Colors\Rgb;

covers(Cmyk::class);

test('it throws exception if CMYK values breach the 0 and 100 boundaries', function () {
    // CMYK maximum is strictly 100, not 255!
    expect(fn () => new Cmyk(-1, 100, 100, 100))->toThrow(InvalidArgumentException::class, 'Cyan must be between 0 and 100.');
    expect(fn () => new Cmyk(101, 100, 100, 100))->toThrow(InvalidArgumentException::class, 'Cyan must be between 0 and 100.');

    expect(fn () => new Cmyk(100, -1, 100, 100))->toThrow(InvalidArgumentException::class, 'Magenta must be between 0 and 100.');
    expect(fn () => new Cmyk(100, 100, -1, 100))->toThrow(InvalidArgumentException::class, 'Yellow must be between 0 and 100.');
    expect(fn () => new Cmyk(100, 100, 100, -1))->toThrow(InvalidArgumentException::class, 'Black must be between 0 and 100.');
});

test('it dynamically assigns and returns alpha boundaries', function () {
    expect((new Cmyk(0, 0, 0, 0, 50))->getAlpha())->toBe(50);
    expect(fn () => new Cmyk(0, 0, 0, 0, 101))->toThrow(InvalidArgumentException::class, 'Alpha must be between 0 and 100.');
});

test('it converts CMYK to RGB correctly', function () {
    $cmyk = new Cmyk(100, 0, 0, 0);
    expect($cmyk->toRgb())->toBeInstanceOf(Rgb::class);
    expect($cmyk->toRgb()->red)->toBe(0);
    expect($cmyk->toRgb()->green)->toBe(255);
    expect($cmyk->toRgb()->blue)->toBe(255);
    expect($cmyk->toRgb()->alpha)->toBe(100);
});

test('it converts CMYK to RGB correctly with alpha', function () {
    $cmyk = new Cmyk(100, 0, 0, 0, 50);
    expect($cmyk->toRgb())->toBeInstanceOf(Rgb::class);
    expect($cmyk->toRgb()->red)->toBe(0);
    expect($cmyk->toRgb()->green)->toBe(255);
    expect($cmyk->toRgb()->blue)->toBe(255);
    expect($cmyk->toRgb()->alpha)->toBe(50);
});

test('it converts CMYK to Grayscale', function () {
    $cmyk = new Cmyk(100, 0, 0, 0);
    expect($cmyk->toGray())->toBeInstanceOf(Gray::class);
    expect($cmyk->toGray()->gray)->toBe(70);
    expect($cmyk->toGray()->alpha)->toBe(100);
});

test('it converts CMYK to Grayscale with alpha', function () {
    $cmyk = new Cmyk(100, 0, 0, 0, 50);
    expect($cmyk->toGray())->toBeInstanceOf(Gray::class);
    expect($cmyk->toGray()->gray)->toBe(70);
    expect($cmyk->toGray()->alpha)->toBe(50);
});

test('it returns itself when calling toCmyk', function () {
    $cmyk = new Cmyk(100, 0, 0, 0);
    expect($cmyk->toCmyk())->toBe($cmyk);
});

test('it converts cmyk to bacon color', function () {
    $cmyk = new Cmyk(100, 0, 0, 0);
    expect($cmyk->toBaconColor())->toBeInstanceOf(BaconCmyk::class);
    expect($cmyk->toBaconColor()->getCyan())->toBe(100);
    expect($cmyk->toBaconColor()->getMagenta())->toBe(0);
    expect($cmyk->toBaconColor()->getYellow())->toBe(0);
    expect($cmyk->toBaconColor()->getBlack())->toBe(0);
});

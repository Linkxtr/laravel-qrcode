<?php

declare(strict_types=1);

use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\Gray as BaconGray;
use Linkxtr\QrCode\ValueObjects\Colors\Cmyk;
use Linkxtr\QrCode\ValueObjects\Colors\Gray;
use Linkxtr\QrCode\ValueObjects\Colors\Rgb;

covers(Gray::class);

test('it creates valid gray color and respects default alpha', function () {
    $color = new Gray(50);
    expect($color->gray)->toBe(50)
        ->and($color->getAlpha())->toBe(100);

    $colorWithAlpha = new Gray(100, 25);
    expect($colorWithAlpha->getAlpha())->toBe(25);
});

test('it throws exception on boundary violations for gray channel', function () {
    expect(fn () => new Gray(-1))->toThrow(InvalidArgumentException::class, 'Gray must be between 0 and 100.')
        ->and(fn () => new Gray(101))->toThrow(InvalidArgumentException::class, 'Gray must be between 0 and 100.');
});

test('it throws exception on boundary violations for alpha', function () {
    expect(fn () => new Gray(0, -1))->toThrow(InvalidArgumentException::class, 'Alpha must be between 0 and 100.')
        ->and(fn () => new Gray(0, 101))->toThrow(InvalidArgumentException::class, 'Alpha must be between 0 and 100.');
});

test('it converts to bacon gray color', function () {
    $color = new Gray(75, 100);
    $baconColor = $color->toBaconColor();

    expect($baconColor)->toBeInstanceOf(BaconGray::class)
        ->and($baconColor->getGray())->toBe(75);

    $colorWithAlpha = new Gray(100, 99);
    $baconColorWithAlpha = $colorWithAlpha->toBaconColor();

    expect($baconColorWithAlpha)->toBeInstanceOf(Alpha::class)
        ->and($baconColorWithAlpha->getAlpha())->toBe(99)
        ->and($baconColorWithAlpha->getBaseColor())->toBeInstanceOf(BaconGray::class);
});

test('toGray returns itself', function () {
    $color = new Gray(50);
    expect($color->toGray())->toBe($color);
});

test('it converts to rgb accurately', function () {
    $white = (new Gray(100, 60))->toRgb();
    expect($white)->toBeInstanceOf(Rgb::class)
        ->and($white->red)->toBe(255)
        ->and($white->green)->toBe(255)
        ->and($white->blue)->toBe(255)
        ->and($white->getAlpha())->toBe(60);

    $black = (new Gray(0))->toRgb();
    expect($black->red)->toBe(0)
        ->and($black->green)->toBe(0)
        ->and($black->blue)->toBe(0);

    $mid = (new Gray(50))->toRgb();
    expect($mid->red)->toBe(128)
        ->and($mid->green)->toBe(128)
        ->and($mid->blue)->toBe(128);

    $dark = (new Gray(2))->toRgb();
    expect($dark->red)->toBe(5)
        ->and($dark->green)->toBe(5)
        ->and($dark->blue)->toBe(5);
});

test('it converts to cmyk accurately', function () {
    $white = (new Gray(100, 90))->toCmyk();
    expect($white)->toBeInstanceOf(Cmyk::class)
        ->and($white->cyan)->toBe(0)
        ->and($white->magenta)->toBe(0)
        ->and($white->yellow)->toBe(0)
        ->and($white->black)->toBe(0)
        ->and($white->getAlpha())->toBe(90);

    $black = (new Gray(0))->toCmyk();
    expect($black->cyan)->toBe(0)
        ->and($black->magenta)->toBe(0)
        ->and($black->yellow)->toBe(0)
        ->and($black->black)->toBe(100);

    $mid = (new Gray(50))->toCmyk();
    expect($mid->cyan)->toBe(0)
        ->and($mid->magenta)->toBe(0)
        ->and($mid->yellow)->toBe(0)
        ->and($mid->black)->toBe(50);
});

test('it allows exact boundary values for alpha without throwing exceptions', function () {
    $colorMinAlpha = new Gray(50, 0);
    expect($colorMinAlpha->getAlpha())->toBe(0);

    $colorMaxAlpha = new Gray(50, 100);
    expect($colorMaxAlpha->getAlpha())->toBe(100);
});

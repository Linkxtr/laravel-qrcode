<?php

declare(strict_types=1);

use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\Cmyk as BaconCmyk;
use Linkxtr\QrCode\Exceptions\InvalidConfigurationException;
use Linkxtr\QrCode\ValueObjects\Colors\Cmyk;
use Linkxtr\QrCode\ValueObjects\Colors\Gray;
use Linkxtr\QrCode\ValueObjects\Colors\Rgb;

covers(Cmyk::class);

test('it creates valid cmyk color and respects default alpha', function (): void {
    $color = new Cmyk(0, 25, 50, 100);
    expect($color->cyan)->toBe(0)
        ->and($color->magenta)->toBe(25)
        ->and($color->yellow)->toBe(50)
        ->and($color->black)->toBe(100)
        ->and($color->getAlpha())->toBe(100);

    $colorWithAlpha = new Cmyk(0, 0, 0, 0, 50);
    expect($colorWithAlpha->getAlpha())->toBe(50);
});

test('it throws exception on boundary violations for cmyk channels', function (): void {
    expect(fn (): Cmyk => new Cmyk(-1, 0, 0, 0))->toThrow(InvalidConfigurationException::class, 'Cyan must be between 0 and 100.')
        ->and(fn (): Cmyk => new Cmyk(101, 0, 0, 0))->toThrow(InvalidConfigurationException::class, 'Cyan must be between 0 and 100.')
        ->and(fn (): Cmyk => new Cmyk(0, -1, 0, 0))->toThrow(InvalidConfigurationException::class, 'Magenta must be between 0 and 100.')
        ->and(fn (): Cmyk => new Cmyk(0, 101, 0, 0))->toThrow(InvalidConfigurationException::class, 'Magenta must be between 0 and 100.')
        ->and(fn (): Cmyk => new Cmyk(0, 0, -1, 0))->toThrow(InvalidConfigurationException::class, 'Yellow must be between 0 and 100.')
        ->and(fn (): Cmyk => new Cmyk(0, 0, 101, 0))->toThrow(InvalidConfigurationException::class, 'Yellow must be between 0 and 100.')
        ->and(fn (): Cmyk => new Cmyk(0, 0, 0, -1))->toThrow(InvalidConfigurationException::class, 'Black must be between 0 and 100.')
        ->and(fn (): Cmyk => new Cmyk(0, 0, 0, 101))->toThrow(InvalidConfigurationException::class, 'Black must be between 0 and 100.');
});

test('it throws exception on boundary violations for alpha', function (): void {
    expect(fn (): Cmyk => new Cmyk(0, 0, 0, 0, -1))->toThrow(InvalidConfigurationException::class, 'Alpha must be between 0 and 100.')
        ->and(fn (): Cmyk => new Cmyk(0, 0, 0, 0, 101))->toThrow(InvalidConfigurationException::class, 'Alpha must be between 0 and 100.');
});

test('it converts to bacon cmyk color', function (): void {
    $color = new Cmyk(10, 20, 30, 40);
    $baconColor = $color->toBaconColor();

    expect($baconColor)->toBeInstanceOf(BaconCmyk::class)
        ->and($baconColor->getCyan())->toBe(10)
        ->and($baconColor->getMagenta())->toBe(20)
        ->and($baconColor->getYellow())->toBe(30)
        ->and($baconColor->getBlack())->toBe(40);

    $colorWithAlpha = new Cmyk(0, 0, 0, 0, 50);
    $baconColorWithAlpha = $colorWithAlpha->toBaconColor();

    expect($baconColorWithAlpha)->toBeInstanceOf(Alpha::class)
        ->and($baconColorWithAlpha->getAlpha())->toBe(50)
        ->and($baconColorWithAlpha->getBaseColor())->toBeInstanceOf(BaconCmyk::class);
});

test('toCmyk returns itself', function (): void {
    $color = new Cmyk(0, 0, 0, 0);
    expect($color->toCmyk())->toBe($color);
});

test('it converts to rgb accurately', function (): void {
    $rgb = (new Cmyk(0, 0, 0, 0, 75))->toRgb();
    expect($rgb)->toBeInstanceOf(Rgb::class)
        ->and($rgb->red)->toBe(255)
        ->and($rgb->green)->toBe(255)
        ->and($rgb->blue)->toBe(255)
        ->and($rgb->getAlpha())->toBe(75);

    $black = (new Cmyk(0, 0, 0, 100))->toRgb();
    expect($black->red)->toBe(0)
        ->and($black->green)->toBe(0)
        ->and($black->blue)->toBe(0);

    $cyan = (new Cmyk(100, 0, 0, 0))->toRgb();
    expect($cyan->red)->toBe(0)
        ->and($cyan->green)->toBe(255)
        ->and($cyan->blue)->toBe(255);
});

test('it converts to gray accurately using luminance', function (): void {
    $gray = (new Cmyk(0, 0, 0, 0, 0))->toGray();
    expect($gray)->toBeInstanceOf(Gray::class)
        ->and($gray->gray)->toBe(100)
        ->and($gray->getAlpha())->toBe(0);

    $black = (new Cmyk(0, 0, 0, 100))->toGray();
    expect($black->gray)->toBe(0);

    $mixed = (new Cmyk(50, 50, 50, 0))->toGray();
    expect($mixed->gray)->toBe(50);
});
test('it validates exactly 99 alpha boundary for bacon color to kill DecrementInteger', function (): void {
    $color = new Cmyk(0, 0, 0, 0, 99);
    $baconColor = $color->toBaconColor();

    expect($baconColor)->toBeInstanceOf(Alpha::class)
        ->and($baconColor->getAlpha())->toBe(99);
});
test('it securely calculates rgb from cmyk to kill denominator, arithmetic, and rounding mutations', function (): void {
    $rgb = (new Cmyk(50, 50, 50, 0))->toRgb();
    expect($rgb->red)->toBe(128)
        ->and($rgb->green)->toBe(128)
        ->and($rgb->blue)->toBe(128);

    $floorKiller = (new Cmyk(20, 20, 20, 10))->toRgb();
    expect($floorKiller->red)->toBe(184)
        ->and($floorKiller->green)->toBe(184)
        ->and($floorKiller->blue)->toBe(184);

    $ceilKiller = (new Cmyk(61, 61, 61, 0))->toRgb();
    expect($ceilKiller->red)->toBe(99)
        ->and($ceilKiller->green)->toBe(99)
        ->and($ceilKiller->blue)->toBe(99);
});
test('it securely calculates gray to kill denominator and rounding mutations', function (): void {
    $gray = (new Cmyk(51, 100, 100, 0))->toGray();
    expect($gray->gray)->toBe(15);

    $cyan101Killer = (new Cmyk(52, 100, 100, 0))->toGray();
    expect($cyan101Killer->gray)->toBe(14);

    $magenta99Killer = (new Cmyk(100, 51, 100, 0))->toGray();
    expect($magenta99Killer->gray)->toBe(29);

    $magenta101Killer = (new Cmyk(100, 50, 100, 0))->toGray();
    expect($magenta101Killer->gray)->toBe(29);

    $yellow99Killer = (new Cmyk(100, 100, 60, 0))->toGray();
    expect($yellow99Killer->gray)->toBe(5);

    $yellow101Killer = (new Cmyk(100, 100, 52, 0))->toGray();
    expect($yellow101Killer->gray)->toBe(5);
});

<?php

declare(strict_types=1);

use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\Rgb as BaconRgb;
use Linkxtr\QrCode\ValueObjects\Colors\Cmyk;
use Linkxtr\QrCode\ValueObjects\Colors\Gray;
use Linkxtr\QrCode\ValueObjects\Colors\Rgb;

covers(Rgb::class);

it('creates valid rgb color and respects default alpha', function (): void {
    $color = new Rgb(0, 128, 255);
    expect($color->red)->toBe(0)
        ->and($color->green)->toBe(128)
        ->and($color->blue)->toBe(255)
        ->and($color->getAlpha())->toBe(100);

    $colorWithAlpha = new Rgb(255, 255, 255, 50);
    expect($colorWithAlpha->getAlpha())->toBe(50);
});

it('throws exception on boundary violations for rgb channels', function (): void {
    expect(fn (): Rgb => new Rgb(-1, 0, 0))->toThrow(InvalidArgumentException::class, 'Red must be between 0 and 255.')
        ->and(fn (): Rgb => new Rgb(256, 0, 0))->toThrow(InvalidArgumentException::class, 'Red must be between 0 and 255.')
        ->and(fn (): Rgb => new Rgb(0, -1, 0))->toThrow(InvalidArgumentException::class, 'Green must be between 0 and 255.')
        ->and(fn (): Rgb => new Rgb(0, 256, 0))->toThrow(InvalidArgumentException::class, 'Green must be between 0 and 255.')
        ->and(fn (): Rgb => new Rgb(0, 0, -1))->toThrow(InvalidArgumentException::class, 'Blue must be between 0 and 255.')
        ->and(fn (): Rgb => new Rgb(0, 0, 256))->toThrow(InvalidArgumentException::class, 'Blue must be between 0 and 255.');
});

it('throws exception on boundary violations for alpha', function (): void {
    expect(fn (): Rgb => new Rgb(0, 0, 0, -1))->toThrow(InvalidArgumentException::class, 'Alpha must be between 0 and 100.')
        ->and(fn (): Rgb => new Rgb(0, 0, 0, 101))->toThrow(InvalidArgumentException::class, 'Alpha must be between 0 and 100.');
});

it('converts from hex string properly', function (): void {
    $rgb = Rgb::fromHex('#FF0000');
    expect($rgb->red)->toBe(255)->and($rgb->green)->toBe(0)->and($rgb->blue)->toBe(0)->and($rgb->getAlpha())->toBe(100);

    $color2 = Rgb::fromHex('0F0', 50);
    expect($color2->red)->toBe(0)->and($color2->green)->toBe(255)->and($color2->blue)->toBe(0)->and($color2->getAlpha())->toBe(50);

    $color3 = Rgb::fromHex('#123');
    expect($color3->red)->toBe(17)
        ->and($color3->green)->toBe(34)
        ->and($color3->blue)->toBe(51);

    $color6 = Rgb::fromHex('#1A2B3C');
    expect($color6->red)->toBe(26)
        ->and($color6->green)->toBe(43)
        ->and($color6->blue)->toBe(60);

    expect(fn (): Rgb => Rgb::fromHex('#FFFF'))->toThrow(InvalidArgumentException::class, 'Invalid hex color format. Must be 3 or 6 characters.')
        ->and(fn (): Rgb => Rgb::fromHex('ZZZZZZ'))->toThrow(InvalidArgumentException::class, 'Invalid hex color string provided.');
});

it('converts to bacon rgb color', function (): void {
    $color = new Rgb(10, 20, 30);
    $baconColor = $color->toBaconColor();

    expect($baconColor)->toBeInstanceOf(BaconRgb::class)
        ->and($baconColor->getRed())->toBe(10)
        ->and($baconColor->getGreen())->toBe(20)
        ->and($baconColor->getBlue())->toBe(30);

    $colorWithAlpha = new Rgb(255, 255, 255, 50);
    $baconColorWithAlpha = $colorWithAlpha->toBaconColor();

    expect($baconColorWithAlpha)->toBeInstanceOf(Alpha::class)
        ->and($baconColorWithAlpha->getAlpha())->toBe(50)
        ->and($baconColorWithAlpha->getBaseColor())->toBeInstanceOf(BaconRgb::class)
        ->and($baconColorWithAlpha->getBaseColor()->getRed())->toBe(255)
        ->and($baconColorWithAlpha->getBaseColor()->getGreen())->toBe(255)
        ->and($baconColorWithAlpha->getBaseColor()->getBlue())->toBe(255);
});

test('toRgb returns itself', function (): void {
    $color = new Rgb(0, 0, 0);
    expect($color->toRgb())->toBe($color);
});

it('converts to cmyk accurately', function (): void {
    $black = (new Rgb(0, 0, 0, 50))->toCmyk();
    expect($black)->toBeInstanceOf(Cmyk::class)
        ->and($black->cyan)->toBe(0)
        ->and($black->magenta)->toBe(0)
        ->and($black->yellow)->toBe(0)
        ->and($black->black)->toBe(100)
        ->and($black->getAlpha())->toBe(50);

    $red = (new Rgb(255, 0, 0))->toCmyk();
    expect($red->cyan)->toBe(0)
        ->and($red->magenta)->toBe(100)
        ->and($red->yellow)->toBe(100)
        ->and($red->black)->toBe(0);

    $gray = (new Rgb(128, 128, 128))->toCmyk();
    expect($gray->cyan)->toBe(0)
        ->and($gray->magenta)->toBe(0)
        ->and($gray->yellow)->toBe(0)
        ->and($gray->black)->toBe(50);

    $white = (new Rgb(255, 255, 255))->toCmyk();
    expect($white->cyan)->toBe(0)
        ->and($white->magenta)->toBe(0)
        ->and($white->yellow)->toBe(0)
        ->and($white->black)->toBe(0);

    $cmyk = (new Rgb(100, 150, 180))->toCmyk();
    expect($cmyk->cyan)->toBe(44)
        ->and($cmyk->magenta)->toBe(17)
        ->and($cmyk->yellow)->toBe(0)
        ->and($cmyk->black)->toBe(29);
});

it('converts to gray accurately using luminance', function (): void {
    $gray = (new Rgb(255, 255, 255, 75))->toGray();
    expect($gray)->toBeInstanceOf(Gray::class)
        ->and($gray->gray)->toBe(100)
        ->and($gray->getAlpha())->toBe(75);
    $black = (new Rgb(0, 0, 0))->toGray();
    expect($black->gray)->toBe(0);

    $mixed = (new Rgb(100, 150, 200))->toGray();
    expect($mixed->gray)->toBe(55);
});

it('allows exact boundary values for alpha without throwing exceptions', function (): void {
    $colorMinAlpha = new Rgb(50, 50, 50, 0);
    expect($colorMinAlpha->getAlpha())->toBe(0);

    $colorMaxAlpha = new Rgb(50, 50, 50, 100);
    expect($colorMaxAlpha->getAlpha())->toBe(100);

    $color99 = new Rgb(50, 50, 50, 99);
    expect($color99->getAlpha())->toBe(99);
});

it('converts to gray accurately', function (): void {
    $gray = (new Rgb(200, 100, 50))->toGray();
    expect($gray->gray)->toBe(49);
});

it('strictly calculates cmyk accurately', function (): void {
    $cmyk = (new Rgb(100, 150, 50))->toCmyk();
    expect($cmyk->cyan)->toBe(33)
        ->and($cmyk->yellow)->toBe(67);

    $color2 = (new Rgb(75, 130, 20))->toCmyk();
    expect($color2->cyan)->toBe(42)
        ->and($color2->magenta)->toBe(0)
        ->and($color2->yellow)->toBe(85)
        ->and($color2->black)->toBe(49);
});

it('kills denominator mutations in gray conversion', function (): void {
    $gray254Killer = (new Rgb(126, 126, 126))->toGray();
    expect($gray254Killer->gray)->toBe(49);

    $gray256Killer = (new Rgb(129, 129, 129))->toGray();
    expect($gray256Killer->gray)->toBe(51);
});

it('converts to bacon color with alpha properly', function (): void {
    $color = new Rgb(0, 0, 0, 99);
    $baconColor = $color->toBaconColor();

    expect($baconColor)->toBeInstanceOf(Alpha::class)
        ->and($baconColor->getAlpha())->toBe(99);

    $solidColor = new Rgb(0, 0, 0, 100);
    $solidBacon = $solidColor->toBaconColor();
    expect($solidBacon)->toBeInstanceOf(BaconRgb::class);
});

it('accurately calculates cmyk', function (): void {
    $cmyk = (new Rgb(12, 175, 230))->toCmyk();

    expect($cmyk->cyan)->toBe(95)
        ->and($cmyk->magenta)->toBe(24)
        ->and($cmyk->yellow)->toBe(0)
        ->and($cmyk->black)->toBe(10);

    $color2 = (new Rgb(254, 254, 254))->toCmyk();
    expect($color2->cyan)->toBe(0)
        ->and($color2->magenta)->toBe(0)
        ->and($color2->yellow)->toBe(0)
        ->and($color2->black)->toBe(0);
});

it('kills denominator mutations on red and green channels', function (): void {
    $cmyk = (new Rgb(253, 0, 0))->toCmyk();
    expect($cmyk->black)->toBe(1);
    $greenKiller = (new Rgb(0, 254, 0))->toCmyk();
    expect($greenKiller->black)->toBe(0);
});

it('kills RoundToCeil mutations on magenta and yellow channels', function (): void {
    $cmyk = (new Rgb(100, 158, 198))->toCmyk();
    expect($cmyk->cyan)->toBe(49)
        ->and($cmyk->magenta)->toBe(20);

    $yellowKiller = (new Rgb(100, 198, 158))->toCmyk();
    expect($yellowKiller->cyan)->toBe(49)
        ->and($yellowKiller->yellow)->toBe(20);
});

test('parse array of integers', function (): void {
    $rgb = Rgb::parse([0, 128, 255]);
    expect($rgb->red)->toBe(0)
        ->and($rgb->green)->toBe(128)
        ->and($rgb->blue)->toBe(255)
        ->and($rgb->getAlpha())->toBe(100)
        ->and($rgb->toArray())->toBe([0, 128, 255, 100]);
});

test('parse array of integers with keys', function (): void {
    $rgb = Rgb::parse(['r' => 0, 'g' => 128, 'b' => 255]);
    expect($rgb->red)->toBe(0)
        ->and($rgb->green)->toBe(128)
        ->and($rgb->blue)->toBe(255)
        ->and($rgb->getAlpha())->toBe(100)
        ->and($rgb->toArray())->toBe([0, 128, 255, 100]);
});

test('parse array of integers and fallback to 0 when value is missing', function (): void {
    $rgb = Rgb::parse([]);
    expect($rgb->red)->toBe(0)
        ->and($rgb->green)->toBe(0)
        ->and($rgb->blue)->toBe(0)
        ->and($rgb->getAlpha())->toBe(100)
        ->and($rgb->toArray())->toBe([0, 0, 0, 100]);
});

test('parse array of integers with alpha', function (): void {
    $rgb = Rgb::parse([0, 128, 255, 50]);
    expect($rgb->red)->toBe(0)
        ->and($rgb->green)->toBe(128)
        ->and($rgb->blue)->toBe(255)
        ->and($rgb->getAlpha())->toBe(50)
        ->and($rgb->toArray())->toBe([0, 128, 255, 50]);
});

test('parse hex string', function (): void {
    $color = Rgb::parse('#FF0000');
    expect($color->red)->toBe(255)
        ->and($color->green)->toBe(0)
        ->and($color->blue)->toBe(0)
        ->and($color->getAlpha())->toBe(100)
        ->and($color->toArray())->toBe([255, 0, 0, 100]);

    $color = Rgb::parse('  FF0000 ');

    expect($color->red)->toBe(255)
        ->and($color->green)->toBe(0)
        ->and($color->blue)->toBe(0)
        ->and($color->getAlpha())->toBe(100)
        ->and($color->toArray())->toBe([255, 0, 0, 100]);
});

test('parse 3-char hex string', function (): void {
    $rgb = Rgb::parse('#F00');
    expect($rgb->red)->toBe(255)
        ->and($rgb->green)->toBe(0)
        ->and($rgb->blue)->toBe(0)
        ->and($rgb->getAlpha())->toBe(100)
        ->and($rgb->toArray())->toBe([255, 0, 0, 100]);
});

test('parse csv string', function (): void {
    $color = Rgb::parse('255,0,0');
    expect($color->red)->toBe(255)
        ->and($color->green)->toBe(0)
        ->and($color->blue)->toBe(0)
        ->and($color->getAlpha())->toBe(100)
        ->and($color->toArray())->toBe([255, 0, 0, 100]);

    $color = Rgb::parse('255,0,0,50');
    expect($color->red)->toBe(255)
        ->and($color->green)->toBe(0)
        ->and($color->blue)->toBe(0)
        ->and($color->getAlpha())->toBe(50)
        ->and($color->toArray())->toBe([255, 0, 0, 50]);

    $color = Rgb::fromCsv(' 255 , 0, 0, 50');
    expect($color->red)->toBe(255)
        ->and($color->green)->toBe(0)
        ->and($color->blue)->toBe(0)
        ->and($color->getAlpha())->toBe(50)
        ->and($color->toArray())->toBe([255, 0, 0, 50]);
});

it('throws excetion on invalid color format', function (): void {
    expect(fn (): Rgb => Rgb::parse('invalid'))->toThrow(InvalidArgumentException::class, 'Unrecognized color format. Please use an array, a hex string, or a comma-separated RGB string.');

    expect(fn (): Rgb => Rgb::parse([1, 2, ['invalid']]))->toThrow(InvalidArgumentException::class, 'RGB array values must be numeric. Nested arrays, objects, or invalid strings are not allowed.');

    expect(fn (): Rgb => Rgb::parse('1, 2, 3, 4, 5'))->toThrow(InvalidArgumentException::class, 'CSV color string must contain exactly 3 or 4 numeric values.');

    expect(fn (): Rgb => Rgb::parse('1, 2, 3, '))->toThrow(InvalidArgumentException::class, 'RGB array values must be numeric. Nested arrays, objects, or invalid strings are not allowed.');
});

it('routes strings starting with hash to hex parser and throws specific exception', function (): void {
    expect(fn (): Rgb => Rgb::parse('#XYZ123'))->toThrow(InvalidArgumentException::class, 'Invalid hex color string provided.');

    expect(fn (): Rgb => Rgb::parse('#12'))->toThrow(InvalidArgumentException::class, 'Invalid hex color format. Must be 3 or 6 characters.');
});

<?php

declare(strict_types=1);

use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\Rgb as BaconRgb;
use Linkxtr\QrCode\ValueObjects\Colors\Cmyk;
use Linkxtr\QrCode\ValueObjects\Colors\Gray;
use Linkxtr\QrCode\ValueObjects\Colors\Rgb;

covers(Rgb::class);

test('it creates valid rgb color and respects default alpha', function () {
    $color = new Rgb(0, 128, 255);
    expect($color->red)->toBe(0)
        ->and($color->green)->toBe(128)
        ->and($color->blue)->toBe(255)
        ->and($color->getAlpha())->toBe(100);

    $colorWithAlpha = new Rgb(255, 255, 255, 50);
    expect($colorWithAlpha->getAlpha())->toBe(50);
});

test('it throws exception on boundary violations for rgb channels', function () {
    expect(fn () => new Rgb(-1, 0, 0))->toThrow(InvalidArgumentException::class, 'Red must be between 0 and 255.')
        ->and(fn () => new Rgb(256, 0, 0))->toThrow(InvalidArgumentException::class, 'Red must be between 0 and 255.')
        ->and(fn () => new Rgb(0, -1, 0))->toThrow(InvalidArgumentException::class, 'Green must be between 0 and 255.')
        ->and(fn () => new Rgb(0, 256, 0))->toThrow(InvalidArgumentException::class, 'Green must be between 0 and 255.')
        ->and(fn () => new Rgb(0, 0, -1))->toThrow(InvalidArgumentException::class, 'Blue must be between 0 and 255.')
        ->and(fn () => new Rgb(0, 0, 256))->toThrow(InvalidArgumentException::class, 'Blue must be between 0 and 255.');
});

test('it throws exception on boundary violations for alpha', function () {
    expect(fn () => new Rgb(0, 0, 0, -1))->toThrow(InvalidArgumentException::class, 'Alpha must be between 0 and 100.')
        ->and(fn () => new Rgb(0, 0, 0, 101))->toThrow(InvalidArgumentException::class, 'Alpha must be between 0 and 100.');
});

test('it converts from hex string properly', function () {
    $color1 = Rgb::fromHex('#FF0000');
    expect($color1->red)->toBe(255)->and($color1->green)->toBe(0)->and($color1->blue)->toBe(0)->and($color1->getAlpha())->toBe(100);

    $color2 = Rgb::fromHex('0F0', 50);
    expect($color2->red)->toBe(0)->and($color2->green)->toBe(255)->and($color2->blue)->toBe(0)->and($color2->getAlpha())->toBe(50);

    expect(fn () => Rgb::fromHex('#FFFF'))->toThrow(InvalidArgumentException::class, 'Invalid hex color format. Must be 3 or 6 characters.')
        ->and(fn () => Rgb::fromHex('ZZZZZZ'))->toThrow(InvalidArgumentException::class, 'Invalid hex color string provided.');
});

test('it converts to bacon rgb color', function () {
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

test('toRgb returns itself', function () {
    $color = new Rgb(0, 0, 0);
    expect($color->toRgb())->toBe($color);
});

test('it converts to cmyk accurately', function () {
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
});

test('it converts to gray accurately using luminance', function () {
    $white = (new Rgb(255, 255, 255, 75))->toGray();
    expect($white)->toBeInstanceOf(Gray::class)
        ->and($white->gray)->toBe(100)
        ->and($white->getAlpha())->toBe(75);
    $black = (new Rgb(0, 0, 0))->toGray();
    expect($black->gray)->toBe(0);

    $mixed = (new Rgb(100, 150, 200))->toGray();
    expect($mixed->gray)->toBe(55);
});

test('it allows exact boundary values for alpha without throwing exceptions', function () {
    $colorMinAlpha = new Rgb(50, 50, 50, 0);
    expect($colorMinAlpha->getAlpha())->toBe(0);

    $colorMaxAlpha = new Rgb(50, 50, 50, 100);
    expect($colorMaxAlpha->getAlpha())->toBe(100);
});
test('it converts from hex string properly killing substring and concatenation mutations', function () {
    $color3 = Rgb::fromHex('#123');
    expect($color3->red)->toBe(17)
        ->and($color3->green)->toBe(34)
        ->and($color3->blue)->toBe(51);

    $color6 = Rgb::fromHex('#1A2B3C');
    expect($color6->red)->toBe(26)
        ->and($color6->green)->toBe(43)
        ->and($color6->blue)->toBe(60);

});
test('it converts to cmyk accurately killing rounding and math mutations', function () {
    $cmyk = (new Rgb(100, 150, 180))->toCmyk();

    expect($cmyk->cyan)->toBe(44)
        ->and($cmyk->magenta)->toBe(17)
        ->and($cmyk->yellow)->toBe(0)
        ->and($cmyk->black)->toBe(29);

    $white = (new Rgb(255, 255, 255))->toCmyk();
    expect($white->cyan)->toBe(0)
        ->and($white->magenta)->toBe(0)
        ->and($white->yellow)->toBe(0)
        ->and($white->black)->toBe(0);
});
test('it converts to gray accurately killing rounding mutations', function () {
    $darker = (new Rgb(100, 150, 200))->toGray();
    expect($darker->gray)->toBe(55);

    $lighter = (new Rgb(200, 100, 50))->toGray();
    expect($lighter->gray)->toBe(49);
});
test('it validates specific alpha boundaries to kill DecrementInteger', function () {
    $color99 = new Rgb(50, 50, 50, 99);
    expect($color99->getAlpha())->toBe(99);
});
test('it kills denominator mutations in cmyk conversion', function () {
    $white = (new Rgb(255, 255, 255))->toCmyk();

    expect($white->cyan)->toBe(0)
        ->and($white->magenta)->toBe(0)
        ->and($white->yellow)->toBe(0)
        ->and($white->black)->toBe(0);
});
test('it strictly calculates cmyk to kill rounding and arithmetic operator mutations', function () {
    $color1 = (new Rgb(100, 150, 50))->toCmyk();
    expect($color1->cyan)->toBe(33)
        ->and($color1->yellow)->toBe(67);

    $color2 = (new Rgb(75, 130, 20))->toCmyk();
    expect($color2->cyan)->toBe(42)
        ->and($color2->magenta)->toBe(0)
        ->and($color2->yellow)->toBe(85)
        ->and($color2->black)->toBe(49);
});
test('it kills denominator mutations in gray conversion', function () {
    $gray254Killer = (new Rgb(126, 126, 126))->toGray();
    expect($gray254Killer->gray)->toBe(49);

    $gray256Killer = (new Rgb(129, 129, 129))->toGray();
    expect($gray256Killer->gray)->toBe(51);
});
test('it converts to bacon color with alpha properly to kill boundary mutations', function () {
    $color = new Rgb(0, 0, 0, 99);
    $baconColor = $color->toBaconColor();

    expect($baconColor)->toBeInstanceOf(Alpha::class)
        ->and($baconColor->getAlpha())->toBe(99);

    $solidColor = new Rgb(0, 0, 0, 100);
    $solidBacon = $solidColor->toBaconColor();
    expect($solidBacon)->toBeInstanceOf(BaconRgb::class);
});
test('it accurately calculates cmyk to kill rounding and denominator mutations', function () {
    $color1 = (new Rgb(12, 175, 230))->toCmyk();

    expect($color1->cyan)->toBe(95)
        ->and($color1->magenta)->toBe(24)
        ->and($color1->yellow)->toBe(0)
        ->and($color1->black)->toBe(10);

    $color2 = (new Rgb(254, 254, 254))->toCmyk();
    expect($color2->cyan)->toBe(0)
        ->and($color2->magenta)->toBe(0)
        ->and($color2->yellow)->toBe(0)
        ->and($color2->black)->toBe(0);
});
test('it converts 3-char hex strings properly to kill ConcatSwitchSides', function () {
    $color = Rgb::fromHex('#123');

    expect($color->red)->toBe(17)
        ->and($color->green)->toBe(34)
        ->and($color->blue)->toBe(51);
});
test('it kills denominator mutations on red and green channels', function () {
    $redKiller = (new Rgb(253, 0, 0))->toCmyk();
    expect($redKiller->black)->toBe(1);
    $greenKiller = (new Rgb(0, 254, 0))->toCmyk();
    expect($greenKiller->black)->toBe(0);
});

test('it kills RoundToCeil mutations on magenta and yellow channels', function () {
    $magentaKiller = (new Rgb(100, 158, 198))->toCmyk();
    expect($magentaKiller->cyan)->toBe(49)
        ->and($magentaKiller->magenta)->toBe(20);

    $yellowKiller = (new Rgb(100, 198, 158))->toCmyk();
    expect($yellowKiller->cyan)->toBe(49)
        ->and($yellowKiller->yellow)->toBe(20);
});

test('parse array of integers', function () {
    $color = Rgb::parse([0, 128, 255]);
    expect($color->red)->toBe(0)
        ->and($color->green)->toBe(128)
        ->and($color->blue)->toBe(255)
        ->and($color->getAlpha())->toBe(100);
});

test('parse array of integers with keys', function () {
    $color = Rgb::parse(['r' => 0, 'g' => 128, 'b' => 255]);
    expect($color->red)->toBe(0)
        ->and($color->green)->toBe(128)
        ->and($color->blue)->toBe(255)
        ->and($color->getAlpha())->toBe(100);
});

test('parse array of integers and fallback to 0 when value is missing', function () {
    $color = Rgb::parse([]);
    expect($color->red)->toBe(0)
        ->and($color->green)->toBe(0)
        ->and($color->blue)->toBe(0)
        ->and($color->getAlpha())->toBe(100);
});

test('parse array of integers with alpha', function () {
    $color = Rgb::parse([0, 128, 255, 50]);
    expect($color->red)->toBe(0)
        ->and($color->green)->toBe(128)
        ->and($color->blue)->toBe(255)
        ->and($color->getAlpha())->toBe(50);
});

test('parse hex string', function () {
    $color = Rgb::parse('#FF0000');
    expect($color->red)->toBe(255)
        ->and($color->green)->toBe(0)
        ->and($color->blue)->toBe(0)
        ->and($color->getAlpha())->toBe(100);

    $color = Rgb::parse('  FF0000 ');

    expect($color->red)->toBe(255)
        ->and($color->green)->toBe(0)
        ->and($color->blue)->toBe(0)
        ->and($color->getAlpha())->toBe(100);
});

test('parse 3-char hex string', function () {
    $color = Rgb::parse('#F00');
    expect($color->red)->toBe(255)
        ->and($color->green)->toBe(0)
        ->and($color->blue)->toBe(0)
        ->and($color->getAlpha())->toBe(100);
});

test('parse csv string', function () {
    $color = Rgb::parse('255,0,0');
    expect($color->red)->toBe(255)
        ->and($color->green)->toBe(0)
        ->and($color->blue)->toBe(0)
        ->and($color->getAlpha())->toBe(100);

    $color = Rgb::parse('255,0,0,50');
    expect($color->red)->toBe(255)
        ->and($color->green)->toBe(0)
        ->and($color->blue)->toBe(0)
        ->and($color->getAlpha())->toBe(50);

    $color = Rgb::fromCsv(' 255 , 0, 0, 50');
    expect($color->red)->toBe(255)
        ->and($color->green)->toBe(0)
        ->and($color->blue)->toBe(0)
        ->and($color->getAlpha())->toBe(50);
});

test('it throw excetion on invalid color format', function () {
    expect(fn () => Rgb::parse('invalid'))->toThrow(InvalidArgumentException::class, 'Unrecognized color format. Please use an array, a hex string, or a comma-separated RGB string.');

    expect(fn () => Rgb::parse([1, 2, ['invalid']]))->toThrow(InvalidArgumentException::class, 'RGB array values must be numeric. Nested arrays, objects, or invalid strings are not allowed.');

    expect(fn () => Rgb::parse('1, 2, 3, 4, 5'))->toThrow(InvalidArgumentException::class, 'CSV color string must contain exactly 3 or 4 numeric values.');

    expect(fn () => Rgb::parse('1, 2, 3, '))->toThrow(InvalidArgumentException::class, 'RGB array values must be numeric. Nested arrays, objects, or invalid strings are not allowed.');
});

test('it routes strings starting with hash to hex parser and throws specific exception', function () {
    expect(fn () => Rgb::parse('#XYZ123'))->toThrow(InvalidArgumentException::class, 'Invalid hex color string provided.');

    expect(fn () => Rgb::parse('#12'))->toThrow(InvalidArgumentException::class, 'Invalid hex color format. Must be 3 or 6 characters.');
});

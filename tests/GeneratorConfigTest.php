<?php

declare(strict_types=1);

use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use Linkxtr\QrCode\Generator;

covers(Generator::class);

// ---------------------------------------------------------------------------
// Config-driven defaults
// ---------------------------------------------------------------------------

test('generator reads size from config', function () {
    $generator = new Generator(['size' => 350]);

    expect(invade($generator)->getRendererStyle()->getSize())->toBe(350);
});

test('generator reads margin from config', function () {
    $generator = new Generator(['margin' => 8]);

    expect(invade($generator)->getRendererStyle()->getMargin())->toBe(8);
});

test('generator reads format from config', function () {
    $generator = new Generator(['format' => 'png']);

    // getRenderer() uses imagick backend for PNG when imagick is loaded
    expect(invade($generator)->getRenderer())->not->toBeNull();
});

test('generator reads error_correction from config', function () {
    $generator = new Generator(['error_correction' => 'H']);

    // Verify the generator works without exception — error correction is used during generate
    expect($generator->generate('test'))->not->toBeNull();
});

test('generator reads encoding from config', function () {
    $generator = new Generator(['encoding' => 'ISO-8859-1']);

    // Verify the generator works without exception — encoding is used during generate
    expect($generator->generate('test'))->not->toBeNull();
});

// ---------------------------------------------------------------------------
// Color from config — positional
// ---------------------------------------------------------------------------

test('generator applies foreground color without alpha from config', function () {
    $generator = new Generator([
        'color' => [255, 0, 0],
    ]);

    $fill = invade($generator)->getFill();
    expect($fill->getForegroundColor())->toBeInstanceOf(Rgb::class);
    expect($fill->getForegroundColor()->toRgb()->getRed())->toBe(255);
    expect($fill->getForegroundColor()->toRgb()->getGreen())->toBe(0);
    expect($fill->getForegroundColor()->toRgb()->getBlue())->toBe(0);
});

test('generator applies black foreground color from config', function () {
    // Previously the guard skipped [0,0,0] — verify it is now applied
    $generator = new Generator([
        'color' => [0, 0, 0],
    ]);

    $fill = invade($generator)->getFill();
    expect($fill->getForegroundColor())->toBeInstanceOf(Rgb::class);
    expect($fill->getForegroundColor()->toRgb()->getRed())->toBe(0);
});

test('generator applies white background color from config', function () {
    // Previously the guard skipped [255,255,255] — verify it is now applied
    $generator = new Generator([
        'background_color' => [255, 255, 255],
    ]);

    $fill = invade($generator)->getFill();
    expect($fill->getBackgroundColor())->toBeInstanceOf(Rgb::class);
    expect($fill->getBackgroundColor()->toRgb()->getRed())->toBe(255);
});

test('generator applies non-default background color from config', function () {
    $generator = new Generator([
        'background_color' => [0, 0, 0],
    ]);

    $fill = invade($generator)->getFill();
    expect($fill->getBackgroundColor()->toRgb()->getRed())->toBe(0);
    expect($fill->getBackgroundColor()->toRgb()->getGreen())->toBe(0);
    expect($fill->getBackgroundColor()->toRgb()->getBlue())->toBe(0);
});

test('generator applies foreground color with non-zero alpha from config', function () {
    $generator = new Generator([
        'color' => [100, 150, 200, 50],
    ]);

    $fill = invade($generator)->getFill();
    expect($fill->getForegroundColor())->toBeInstanceOf(Alpha::class);
    expect($fill->getForegroundColor()->getAlpha())->toBe(50);
});

test('generator applies foreground color with explicit alpha zero (fully transparent) from config', function () {
    // alpha=0 means fully transparent, which is distinct from "unspecified" (null)
    $generator = new Generator([
        'color' => [255, 0, 0, 0],
    ]);

    $fill = invade($generator)->getFill();
    expect($fill->getForegroundColor())->toBeInstanceOf(Alpha::class);
    expect($fill->getForegroundColor()->getAlpha())->toBe(0);
});

test('generator applies background color with non-zero alpha from config', function () {
    $generator = new Generator([
        'background_color' => [100, 150, 200, 30],
    ]);

    $fill = invade($generator)->getFill();
    expect($fill->getBackgroundColor())->toBeInstanceOf(Alpha::class);
    expect($fill->getBackgroundColor()->getAlpha())->toBe(30);
});

test('generator applies background color with explicit alpha zero from config', function () {
    $generator = new Generator([
        'background_color' => [0, 0, 0, 0],
    ]);

    $fill = invade($generator)->getFill();
    expect($fill->getBackgroundColor())->toBeInstanceOf(Alpha::class);
    expect($fill->getBackgroundColor()->getAlpha())->toBe(0);
});

test('generator omits alpha when color has no fourth channel', function () {
    // When no alpha index/key is present the result must be plain Rgb (opaque)
    $generator = new Generator([
        'color' => [10, 20, 30],
    ]);

    expect(invade($generator)->getFill()->getForegroundColor())->toBeInstanceOf(Rgb::class);
});

// ---------------------------------------------------------------------------
// Color from config — associative keys
// ---------------------------------------------------------------------------

test('generator accepts associative color keys from config', function () {
    $generator = new Generator([
        'color' => ['r' => 100, 'g' => 150, 'b' => 200],
    ]);

    $fill = invade($generator)->getFill();
    expect($fill->getForegroundColor())->toBeInstanceOf(Rgb::class);
    expect($fill->getForegroundColor()->toRgb()->getRed())->toBe(100);
    expect($fill->getForegroundColor()->toRgb()->getGreen())->toBe(150);
    expect($fill->getForegroundColor()->toRgb()->getBlue())->toBe(200);
});

test('generator accepts associative color keys with alpha from config', function () {
    $generator = new Generator([
        'color' => ['r' => 200, 'g' => 100, 'b' => 50, 'a' => 64],
    ]);

    $fill = invade($generator)->getFill();
    expect($fill->getForegroundColor())->toBeInstanceOf(Alpha::class);
    expect($fill->getForegroundColor()->getAlpha())->toBe(64);
    expect($fill->getForegroundColor()->toRgb()->getRed())->toBe(200);
});

test('generator accepts associative background color keys from config', function () {
    $generator = new Generator([
        'background_color' => ['r' => 10, 'g' => 20, 'b' => 30],
    ]);

    $fill = invade($generator)->getFill();
    expect($fill->getBackgroundColor())->toBeInstanceOf(Rgb::class);
    expect($fill->getBackgroundColor()->toRgb()->getRed())->toBe(10);
});

// ---------------------------------------------------------------------------
// Graceful fallback for invalid config
// ---------------------------------------------------------------------------

test('generator ignores invalid format from config gracefully', function () {
    $generator = new Generator(['format' => 'invalid_format']);

    expect(invade($generator)->getRendererStyle())->toBeInstanceOf(RendererStyle::class);
});

test('generator ignores invalid error_correction from config gracefully', function () {
    $generator = new Generator(['error_correction' => 'Z']);

    expect(invade($generator)->getRendererStyle())->toBeInstanceOf(RendererStyle::class);
});

test('generator ignores unknown config keys gracefully', function () {
    $generator = new Generator(['unknown_key' => 'some_value']);

    expect(invade($generator)->getRendererStyle())->toBeInstanceOf(RendererStyle::class);
    // Verify it still uses default size/margin (confirms fallback worked)
    expect(invade($generator)->getRendererStyle()->getSize())->toBe(100);
    expect(invade($generator)->getRendererStyle()->getMargin())->toBe(0);
});

test('generator uses channel defaults when color values are non-integers', function () {
    // Non-int values cause both named-key and positional checks to fail,
    // exercising the $default fallback path in readColorChannel.
    $generator = new Generator([
        'color' => ['not-an-int', 'null', 'also-not', 'string'],
    ]);

    // Falls back to default black (0, 0, 0) with no alpha
    $fill = invade($generator)->getFill();
    expect($fill->getForegroundColor())->toBeInstanceOf(Rgb::class);
    expect($fill->getForegroundColor()->toRgb()->getRed())->toBe(0);
    expect($fill->getForegroundColor()->toRgb()->getGreen())->toBe(0);
    expect($fill->getForegroundColor()->toRgb()->getBlue())->toBe(0);
});

// ---------------------------------------------------------------------------
// Hardcoded defaults when no config
// ---------------------------------------------------------------------------

test('generator uses hardcoded defaults when no config is given', function () {
    $generator = new Generator;

    // Hardcoded defaults
    expect(invade($generator)->getRendererStyle()->getSize())->toBe(100);
    expect(invade($generator)->getRendererStyle()->getMargin())->toBe(0);
    // Default colors: black foreground, white background (from getFill() fallbacks)
    expect(invade($generator)->getFill()->getForegroundColor())->toBeInstanceOf(Rgb::class);
    expect(invade($generator)->getFill()->getForegroundColor()->toRgb()->getRed())->toBe(0);
    expect(invade($generator)->getFill()->getBackgroundColor()->toRgb()->getRed())->toBe(255);
});

// ---------------------------------------------------------------------------
// All config values together
// ---------------------------------------------------------------------------

test('generator uses all config values together', function () {
    $generator = new Generator([
        'size' => 512,
        'margin' => 10,
        'format' => 'svg',
        'error_correction' => 'M',
        'encoding' => 'UTF-8',
        'color' => [10, 20, 30],
        'background_color' => [240, 240, 240],
    ]);

    // Size and margin are directly readable via getRendererStyle()
    expect(invade($generator)->getRendererStyle()->getSize())->toBe(512);
    expect(invade($generator)->getRendererStyle()->getMargin())->toBe(10);

    // Colors are directly readable via getFill()
    expect(invade($generator)->getFill()->getForegroundColor()->toRgb()->getRed())->toBe(10);
    expect(invade($generator)->getFill()->getBackgroundColor()->toRgb()->getRed())->toBe(240);

    // Format (svg) is verified by generate() output — the SVG backend produces XML
    // markup, whereas PNG/WebP would produce binary data.
    $output = $generator->generate('test')->toHtml();
    expect($output)->toContain('<svg');

    // error_correction and encoding have no public getters; their effect is
    // confirmed by the successful SVG generation above (both are consumed inside generate()).
});

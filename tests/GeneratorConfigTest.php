<?php

declare(strict_types=1);

use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Generator;

covers(Generator::class);

beforeEach(function () {
    global $mockImagickLoaded;
    $mockImagickLoaded = true;
});

// ---------------------------------------------------------------------------
// Config-driven defaults
// ---------------------------------------------------------------------------

test('generator reads size from config', function () {
    $generator = new Generator(['size' => 350]);

    expect($generator->getRendererStyle()->getSize())->toBe(350);
});

test('generator reads margin from config', function () {
    $generator = new Generator(['margin' => 8]);

    expect($generator->getRendererStyle()->getMargin())->toBe(8);
});

test('generator reads format from config', function () {
    $generator = new Generator(['format' => 'png']);

    // getRenderer() uses imagick backend for PNG when imagick is loaded
    expect($generator->getRenderer())->not->toBeNull();
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

test('generator reads non-default foreground color from config', function () {
    $generator = new Generator([
        'color' => [255, 0, 0, 0],
    ]);

    $fill = $generator->getFill();
    expect($fill->getForegroundColor()->toRgb()->getRed())->toBe(255);
    expect($fill->getForegroundColor()->toRgb()->getGreen())->toBe(0);
    expect($fill->getForegroundColor()->toRgb()->getBlue())->toBe(0);
});

test('generator reads non-default background color from config', function () {
    $generator = new Generator([
        'background_color' => [0, 0, 0, 0],
    ]);

    $fill = $generator->getFill();
    expect($fill->getBackgroundColor()->toRgb()->getRed())->toBe(0);
    expect($fill->getBackgroundColor()->toRgb()->getGreen())->toBe(0);
    expect($fill->getBackgroundColor()->toRgb()->getBlue())->toBe(0);
});

test('generator reads foreground color with alpha from config', function () {
    $generator = new Generator([
        'color' => [100, 150, 200, 50],
    ]);

    $fill = $generator->getFill();
    expect($fill->getForegroundColor())->toBeInstanceOf(Alpha::class);
    expect($fill->getForegroundColor()->getAlpha())->toBe(50);
});

test('generator reads background color with alpha from config', function () {
    $generator = new Generator([
        'background_color' => [100, 150, 200, 30],
    ]);

    $fill = $generator->getFill();
    expect($fill->getBackgroundColor())->toBeInstanceOf(Alpha::class);
    expect($fill->getBackgroundColor()->getAlpha())->toBe(30);
});

test('generator ignores invalid format from config gracefully', function () {
    // Unknown format string should just fall back to the hardcoded default
    $generator = new Generator(['format' => 'invalid_format']);

    expect($generator->getRendererStyle())->toBeInstanceOf(RendererStyle::class);
});

test('generator ignores invalid error_correction from config gracefully', function () {
    $generator = new Generator(['error_correction' => 'Z']);

    expect($generator->getRendererStyle())->toBeInstanceOf(RendererStyle::class);
});

test('generator ignores unknown config keys gracefully', function () {
    $generator = new Generator(['unknown_key' => 'some_value']);

    expect($generator->getRendererStyle())->toBeInstanceOf(RendererStyle::class);
});

test('generator uses hardcoded defaults when no config is given', function () {
    $generator = new Generator;

    // Hardcoded defaults
    expect($generator->getRendererStyle()->getSize())->toBe(100);
    expect($generator->getRendererStyle()->getMargin())->toBe(0);
    // Default colors: black foreground, white background
    expect($generator->getFill()->getForegroundColor())->toBeInstanceOf(Rgb::class);
    expect($generator->getFill()->getForegroundColor()->toRgb()->getRed())->toBe(0);
    expect($generator->getFill()->getBackgroundColor()->toRgb()->getRed())->toBe(255);
});

test('generator uses all config values together', function () {
    $generator = new Generator([
        'size' => 512,
        'margin' => 10,
        'format' => 'svg',
        'error_correction' => 'M',
        'encoding' => 'UTF-8',
        'color' => [10, 20, 30, 0],
        'background_color' => [240, 240, 240, 0],
    ]);

    expect($generator->getRendererStyle()->getSize())->toBe(512);
    expect($generator->getRendererStyle()->getMargin())->toBe(10);
    expect($generator->getFill()->getForegroundColor()->toRgb()->getRed())->toBe(10);
    expect($generator->getFill()->getBackgroundColor()->toRgb()->getRed())->toBe(240);
});

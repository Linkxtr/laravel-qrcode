<?php

declare(strict_types=1);

use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\Cmyk;
use BaconQrCode\Renderer\Color\Gray;
use BaconQrCode\Renderer\Color\Rgb as BaconRgb;
use BaconQrCode\Renderer\Eye\CompositeEye;
use BaconQrCode\Renderer\Eye\ModuleEye;
use BaconQrCode\Renderer\Eye\PointyEye;
use BaconQrCode\Renderer\Eye\SimpleCircleEye;
use BaconQrCode\Renderer\Eye\SquareEye;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Renderer\Module\DotsModule;
use BaconQrCode\Renderer\Module\RoundnessModule;
use BaconQrCode\Renderer\Module\SquareModule;
use BaconQrCode\Renderer\RendererStyle\EyeFill;
use Illuminate\Support\HtmlString;
use Linkxtr\QrCode\DTOs\Config;
use Linkxtr\QrCode\Enums\ColorModel;
use Linkxtr\QrCode\Enums\EyeStyle;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Enums\Style;
use Linkxtr\QrCode\Mergers\EpsMerger;
use Linkxtr\QrCode\Mergers\ImagickMerger;
use Linkxtr\QrCode\Mergers\RasterMerger;
use Linkxtr\QrCode\Mergers\SvgMerger;
use Linkxtr\QrCode\Renderers\BaconRenderer;
use Linkxtr\QrCode\ValueObjects\Colors\Rgb;

covers(BaconRenderer::class);

$tinyPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');

afterEach(function () {
    global $mockImagickLoaded, $mockGdLoaded;
    $mockImagickLoaded = true;
    $mockGdLoaded = true;
});

it('throws exception if required extensions are not loaded', function () {
    global $mockImagickLoaded, $mockGdLoaded;
    $mockImagickLoaded = false;
    $mockGdLoaded = false;

    $config = new Config;
    $renderer = new BaconRenderer($config);

    $config->setFormat(Format::SVG);

    expect(fn () => $renderer->render('test'))->not->toThrow(RuntimeException::class);

    $config->setFormat(Format::EPS);

    expect(fn () => $renderer->render('test'))->not->toThrow(RuntimeException::class);

    $config->setFormat(Format::PNG);
    expect(fn () => $renderer->render('test'))->toThrow(RuntimeException::class, 'The imagick or gd extension is required to generate raster QR codes');

    $config->setFormat(Format::WEBP);
    expect(fn () => $renderer->render('test'))->toThrow(RuntimeException::class, 'The imagick or gd extension is required to generate raster QR codes');
});

it('throws an exception if trying to generate a non-PNG raster using only GD', function () {
    global $mockImagickLoaded, $mockGdLoaded;
    $mockImagickLoaded = false;
    $mockGdLoaded = true;

    $config = new Config;
    $renderer = new BaconRenderer($config);
    $config->setFormat(Format::WEBP);

    expect(fn () => $renderer->render('test'))
        ->toThrow(RuntimeException::class, 'Format "webp" requires the Imagick extension.');
});

it('falls back to GDLibRenderer for PNG if imagick is missing', function () {
    global $mockImagickLoaded;
    $mockImagickLoaded = false;

    $config = new Config;
    $config->setFormat(Format::PNG);
    $renderer = new BaconRenderer($config);

    expect(invade($renderer)->getRenderer())->toBeInstanceOf(GDLibRenderer::class);
});

it('successfully generates SVG and EPS without requiring any image extensions', function () {
    global $mockImagickLoaded, $mockGdLoaded;
    $mockImagickLoaded = false;
    $mockGdLoaded = false;

    $config = new Config;
    $renderer = new BaconRenderer($config);

    // SVG should succeed
    $config->setFormat(Format::SVG);
    $svgQr = $renderer->render('test');
    expect((string) $svgQr)->toContain('<svg');

    // EPS should succeed
    $config->setFormat(Format::EPS);
    $epsQr = $renderer->render('test');
    expect((string) $epsQr)->toContain('%!PS-Adobe');
});

it('resolves the correct module styles', function () {
    $config = new Config;
    $renderer = new BaconRenderer($config);

    $config->setupStyle(Style::DOT);
    expect(invade($renderer)->getModule())->toBeInstanceOf(DotsModule::class);

    $config->setupStyle(Style::ROUND);
    expect(invade($renderer)->getModule())->toBeInstanceOf(RoundnessModule::class);

    $config->setupStyle(Style::SQUARE);
    expect(invade($renderer)->getModule())->toBeInstanceOf(SquareModule::class);
});

it('builds the fill correctly with and without gradient', function () {
    $config = new Config;
    $renderer = new BaconRenderer($config);

    expect(invade($renderer)->getFill()->hasGradientFill())->toBeFalse();

    $config->setupGradient(Rgb::fromArray([255, 0, 0]), Rgb::fromArray([0, 255, 0]), 'diagonal');
    expect(invade($renderer)->getFill()->hasGradientFill())->toBeTrue();
});

it('builds the correct eye fills', function () {
    $config = new Config;
    $renderer = new BaconRenderer($config);

    $config->setupEyeColor(0, Rgb::fromArray([10, 20, 30]));
    $config->setupEyeColor(1, Rgb::fromArray([40, 50, 60]));
    $config->setupEyeColor(2, Rgb::fromArray([70, 80, 90]));

    $fill = invade($renderer)->getFill();

    expect($fill->getTopLeftEyeFill())->toBeInstanceOf(EyeFill::class)
        ->and($fill->getTopLeftEyeFill()->getExternalColor())->toBeInstanceOf(BaconRgb::class)
        ->and($fill->getTopLeftEyeFill()->getExternalColor()->getRed())->toBe(10)
        ->and($fill->getTopLeftEyeFill()->getExternalColor()->getGreen())->toBe(20)
        ->and($fill->getTopLeftEyeFill()->getExternalColor()->getBlue())->toBe(30)
        ->and($fill->getTopRightEyeFill())->toBeInstanceOf(EyeFill::class)
        ->and($fill->getTopRightEyeFill()->getExternalColor())->toBeInstanceOf(BaconRgb::class)
        ->and($fill->getTopRightEyeFill()->getExternalColor()->getRed())->toBe(40)
        ->and($fill->getTopRightEyeFill()->getExternalColor()->getGreen())->toBe(50)
        ->and($fill->getTopRightEyeFill()->getExternalColor()->getBlue())->toBe(60)
        ->and($fill->getBottomLeftEyeFill())->toBeInstanceOf(EyeFill::class)
        ->and($fill->getBottomLeftEyeFill()->getExternalColor())->toBeInstanceOf(BaconRgb::class)
        ->and($fill->getBottomLeftEyeFill()->getExternalColor()->getRed())->toBe(70)
        ->and($fill->getBottomLeftEyeFill()->getExternalColor()->getGreen())->toBe(80)
        ->and($fill->getBottomLeftEyeFill()->getExternalColor()->getBlue())->toBe(90);
});

it('builds the correct color models', function () {
    $config = new Config;
    $renderer = new BaconRenderer($config);

    $config->setupColor(255, 0, 0, 99);
    expect(invade($renderer)->buildColor($config->getColorValue()))->toBeInstanceOf(Alpha::class);

    $config->setupColor(255, 0, 0);
    expect(invade($renderer)->buildColor($config->getColorValue()))->toBeInstanceOf(BaconRgb::class);

    $config->setColorModel(ColorModel::CMYK);
    $config->setupColor(10, 20, 30, 40);
    expect(invade($renderer)->buildColor($config->getColorValue()))->toBeInstanceOf(Cmyk::class)
        ->and(invade($renderer)->buildColor($config->getColorValue())->getBlack())->toBe(40);

    $config->setupColor(10, 20, 30);
    expect(invade($renderer)->buildColor($config->getColorValue()))->toBeInstanceOf(Cmyk::class)
        ->and(invade($renderer)->buildColor($config->getColorValue())->getBlack())->toBe(100);

    $config->setGrayscale(50);
    expect(invade($renderer)->buildColor($config->getColorValue()))->toBeInstanceOf(Gray::class);
});

it('resolves single eye styles', function () {
    $config = new Config;
    $renderer = new BaconRenderer($config);

    expect(invade($renderer)->getEye())->toBeInstanceOf(ModuleEye::class);

    $config->setEyeStyle(EyeStyle::SQUARE);
    expect(invade($renderer)->getEye())->toBeInstanceOf(SquareEye::class);

    $config->setEyeStyle(EyeStyle::CIRCLE);
    expect(invade($renderer)->getEye())->toBeInstanceOf(SimpleCircleEye::class);

    $config->setEyeStyle(EyeStyle::POINTY);
    expect(invade($renderer)->getEye())->toBeInstanceOf(PointyEye::class);

    $config->setInternalEyeStyle(EyeStyle::SQUARE);
    expect(invade($renderer)->getEye())->toBeInstanceOf(CompositeEye::class);
});

it('renders an html string without merged image', function () {
    $config = new Config;
    $renderer = new BaconRenderer($config);

    $result = $renderer->render('test payload');

    expect($result)->toBeInstanceOf(HtmlString::class)
        ->and($result->toHtml())->toContain('<svg')
        ->and($result->toHtml())->not->toContain('href="data:image/png;base64');
});

it('calls the correct merger based on format', function () use ($tinyPng) {
    $config = new Config;
    $renderer = new BaconRenderer($config);

    $config->setFormat(Format::SVG);
    $config->setupMergeString($tinyPng);
    expect(invade($renderer)->getMerger('test'))->toBeInstanceOf(SvgMerger::class);

    $config->setFormat(Format::EPS);
    $config->setupMergeString($tinyPng);
    expect(invade($renderer)->getMerger('test'))->toBeInstanceOf(EpsMerger::class);

    $config->setFormat(Format::PNG);
    $config->setupMergeString($tinyPng);
    expect(invade($renderer)->getMerger($tinyPng))->toBeInstanceOf(ImagickMerger::class);

    global $mockImagickLoaded;
    $mockImagickLoaded = false;
    expect(invade($renderer)->getMerger($tinyPng))->toBeInstanceOf(RasterMerger::class);
});

it('throws an exception when trying to merge images into EPS format without gd extension', function () use ($tinyPng) {
    global $mockGdLoaded;
    $mockGdLoaded = false;

    $config = new Config;
    $config->setFormat(Format::EPS);
    $config->setupMergeString($tinyPng);
    $renderer = new BaconRenderer($config);

    expect(fn () => $renderer->render('test'))->toThrow(RuntimeException::class, 'The "gd" extension is required to merge images into EPS format.');
});

it('renders an html string with merged image', function () use ($tinyPng) {
    $config = new Config;
    $config->setFormat(Format::SVG);
    $config->setupMergeString($tinyPng, 0.2);
    $renderer = new BaconRenderer($config);

    $result = $renderer->render('test payload');

    expect($result)->toBeInstanceOf(HtmlString::class)
        ->and($result->toHtml())->toContain('<svg')
        ->and($result->toHtml())->toContain('href="data:image/png;base64');
});

it('throws a RuntimeException when using GD library with a non-square style', function () {
    global $mockImagickLoaded, $mockGdLoaded;
    $mockImagickLoaded = false;
    $mockGdLoaded = true;

    $config = new Config;
    $config->setFormat(Format::PNG);

    $config->setupStyle(Style::DOT);

    $renderer = new BaconRenderer($config);

    expect(fn () => $renderer->render('https://linkxtr.com'))
        ->toThrow(
            RuntimeException::class,
            'The GD library does not support non-square module styles (e.g., DOT, ROUND). Please enable the Imagick extension or use the SQUARE style.'
        );
});

it('throws a RuntimeException when using GD library with a gradient', function () {
    global $mockImagickLoaded, $mockGdLoaded;
    $mockImagickLoaded = false;
    $mockGdLoaded = true;

    $config = new Config;
    $config->setFormat(Format::PNG);

    $config->setupGradient(new Rgb(255, 0, 0), new Rgb(0, 0, 255));

    $renderer = new BaconRenderer($config);

    expect(fn () => $renderer->render('https://linkxtr.com'))->toThrow(
        RuntimeException::class,
        'The GD library does not support gradients. Please enable the Imagick extension or use solid colors.'
    );
});

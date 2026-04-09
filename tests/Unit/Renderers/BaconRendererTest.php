<?php

declare(strict_types=1);

use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\Cmyk;
use BaconQrCode\Renderer\Color\Gray;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Eye\CompositeEye;
use BaconQrCode\Renderer\Eye\ModuleEye;
use BaconQrCode\Renderer\Eye\PointyEye;
use BaconQrCode\Renderer\Eye\SimpleCircleEye;
use BaconQrCode\Renderer\Eye\SquareEye;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Renderer\Module\DotsModule;
use BaconQrCode\Renderer\Module\RoundnessModule;
use BaconQrCode\Renderer\Module\SquareModule;
use Illuminate\Support\HtmlString;
use Linkxtr\QrCode\DTOs\Config;
use Linkxtr\QrCode\Enums\ColorModel;
use Linkxtr\QrCode\Enums\EyeStyle;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Enums\Style;
use Linkxtr\QrCode\Renderers\BaconRenderer;

covers(BaconRenderer::class);

$tinyPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');

beforeEach(function () {
    global $mockImagickLoaded, $mockGdLoaded;
    $mockImagickLoaded = true;
    $mockGdLoaded = true;
});

it('renders an html string with merged image', function () use ($tinyPng) {
    $config = new Config;
    $config->setupMergeString($tinyPng, 0.2);
    $renderer = new BaconRenderer($config);

    $result = $renderer->render('test payload');

    expect($result)->toBeInstanceOf(HtmlString::class)
        ->and($result->toHtml())->toContain('<svg')
        ->and($result->toHtml())->toContain('href="data:image/png;base64');
});

it('renders an html string without merged image', function () {
    $config = new Config;
    $renderer = new BaconRenderer($config);

    $result = $renderer->render('test payload');

    expect($result)->toBeInstanceOf(HtmlString::class)
        ->and($result->toHtml())->toContain('<svg')
        ->and($result->toHtml())->not->toContain('href="data:image/png;base64');
});

it('throws exception if required extensions are not loaded', function () {
    global $mockImagickLoaded, $mockGdLoaded;
    $mockImagickLoaded = false;
    $mockGdLoaded = false;

    $config = new Config;
    $renderer = new BaconRenderer($config);

    expect(fn () => $renderer->render('test'))
        ->toThrow(RuntimeException::class, 'The imagick or gd extension is required to generate QR codes.');

    $mockGdLoaded = true;
    expect(fn () => $renderer->render('test'))->toThrow(RuntimeException::class, 'The imagick extension is required to generate QR codes in svg format.');
});

it('falls back to GDLibRenderer for PNG if imagick is missing', function () {
    global $mockImagickLoaded;
    $mockImagickLoaded = false;

    $config = new Config;
    $config->setFormat(Format::PNG);
    $renderer = new BaconRenderer($config);

    expect(invade($renderer)->getRenderer())->toBeInstanceOf(GDLibRenderer::class);
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

    $config->setupGradient(255, 0, 0, 0, 255, 0, 'diagonal');
    expect(invade($renderer)->getFill()->hasGradientFill())->toBeTrue();
});

it('builds the correct color models', function () {
    $config = new Config;
    $renderer = new BaconRenderer($config);

    expect(invade($renderer)->buildColor(null))->toBeNull();

    $config->setupColor(255, 0, 0, 50);
    expect(invade($renderer)->buildColor($config->getColorValue()))->toBeInstanceOf(Alpha::class);

    $config->setupColor(255, 0, 0);
    expect(invade($renderer)->buildColor($config->getColorValue()))->toBeInstanceOf(Rgb::class);

    $config->setColorModel(ColorModel::CMYK);
    $config->setupColor(10, 20, 30, 40);
    expect(invade($renderer)->buildColor($config->getColorValue()))->toBeInstanceOf(Cmyk::class);

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

it('calls the correct merger based on format', function () use ($tinyPng) {
    $config = new Config;
    $renderer = new BaconRenderer($config);

    $config->setFormat(Format::SVG);
    $config->setupMergeString($tinyPng, 0.2);
    expect(invade($renderer)->mergeImage('<svg id="qr" width="100" height="100"></svg>'))->toBeString();

    $config->setFormat(Format::EPS);
    $config->setupMergeString($tinyPng, 0.2);
    expect(invade($renderer)->mergeImage('%%BoundingBox: 0 0 420 595'))->toBeString();

    $config->setFormat(Format::PNG);
    $config->setupMergeString($tinyPng, 0.2);
    expect(invade($renderer)->mergeImage($tinyPng))->toBeString();

    global $mockImagickLoaded;
    $mockImagickLoaded = false;
    expect(invade($renderer)->mergeImage($tinyPng))->toBeString();
});

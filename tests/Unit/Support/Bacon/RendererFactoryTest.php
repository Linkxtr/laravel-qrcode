<?php

declare(strict_types=1);

use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererInterface;
use Linkxtr\QrCode\DTOs\Config;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Enums\Style;
use Linkxtr\QrCode\Exceptions\MissingExtensionException;
use Linkxtr\QrCode\Support\Bacon\RendererFactory;
use Linkxtr\QrCode\ValueObjects\Colors\Rgb;

covers(RendererFactory::class);

it('uses ImageRenderer when imagick is loaded', function (Format $format): void {
    global $mockImagickLoaded;
    $mockImagickLoaded = true;

    $config = new Config;
    $config->setFormat($format);

    $renderer = RendererFactory::make($config);

    expect($renderer)->toBeInstanceOf(ImageRenderer::class);
})->with(Format::cases());

it('falls back to GDLibRenderer for PNG if imagick is missing', function (): void {
    global $mockImagickLoaded;
    $mockImagickLoaded = false;

    $config = new Config;
    $config->setFormat(Format::PNG);

    $renderer = RendererFactory::make($config);

    expect($renderer)->toBeInstanceOf(GDLibRenderer::class);
});

it('throws exception if required extensions are not loaded', function (): void {
    global $mockImagickLoaded, $mockGdLoaded;
    $mockImagickLoaded = false;
    $mockGdLoaded = false;

    $config = new Config;
    $config->setFormat(Format::SVG);

    expect(fn (): RendererInterface => RendererFactory::make($config))->not->toThrow(MissingExtensionException::class);

    $config->setFormat(Format::EPS);

    expect(fn (): RendererInterface => RendererFactory::make($config))->not->toThrow(MissingExtensionException::class);

    $config->setFormat(Format::PNG);
    expect(fn (): RendererInterface => RendererFactory::make($config))->toThrow(MissingExtensionException::class, 'The imagick or gd extension is required to generate raster QR codes');

    $config->setFormat(Format::WEBP);
    expect(fn (): RendererInterface => RendererFactory::make($config))->toThrow(MissingExtensionException::class, 'The imagick or gd extension is required to generate raster QR codes');
});

it('throws an exception if trying to generate a non-PNG raster using only GD', function (): void {
    global $mockImagickLoaded, $mockGdLoaded;
    $mockImagickLoaded = false;
    $mockGdLoaded = true;

    $config = new Config;
    $config->setFormat(Format::WEBP);

    expect(fn (): RendererInterface => RendererFactory::make($config))
        ->toThrow(MissingExtensionException::class, 'The Imagick extension is required to generate the webp format.');
});

it('throws a MissingExtensionException when using GD library with a non-square style', function (): void {
    global $mockImagickLoaded, $mockGdLoaded;
    $mockImagickLoaded = false;
    $mockGdLoaded = true;

    $config = new Config;
    $config->setFormat(Format::PNG);

    $config->setupStyle(Style::DOT);

    expect(fn (): RendererInterface => RendererFactory::make($config))
        ->toThrow(
            MissingExtensionException::class,
            'The Imagick extension is required to use non-square module styles (e.g., DOT, ROUND). Please enable the Imagick extension or use the SQUARE style.'
        );
});

it('throws a MissingExtensionException when using GD library with a gradient', function (): void {
    global $mockImagickLoaded, $mockGdLoaded;
    $mockImagickLoaded = false;
    $mockGdLoaded = true;

    $config = new Config;
    $config->setFormat(Format::PNG);

    $config->setupGradient(new Rgb(255, 0, 0), new Rgb(0, 0, 255));

    expect(fn (): RendererInterface => RendererFactory::make($config))
        ->toThrow(
            MissingExtensionException::class,
            'The Imagick extension is required to use gradients. Please enable the Imagick extension or use solid colors.'
        );
});

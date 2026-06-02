<?php

declare(strict_types=1);

use Linkxtr\QrCode\DTOs\Config;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Enums\Style;
use Linkxtr\QrCode\Exceptions\MissingExtensionException;
use Linkxtr\QrCode\Renderers\BaconRenderer;
use Linkxtr\QrCode\Support\Environment;
use Linkxtr\QrCode\Support\QrCodeResult;
use Linkxtr\QrCode\ValueObjects\Colors\Rgb;

covers(BaconRenderer::class);

$tinyPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');

it('throws exception if required extensions are not loaded', function (): void {
    Environment::disableExtension('imagick');
    Environment::disableExtension('gd');

    $config = new Config;
    $renderer = new BaconRenderer($config);

    $config->setFormat(Format::SVG);

    expect(fn (): QrCodeResult => $renderer->render('test'))->not->toThrow(MissingExtensionException::class);

    $config->setFormat(Format::EPS);

    expect(fn (): QrCodeResult => $renderer->render('test'))->not->toThrow(MissingExtensionException::class);

    $config->setFormat(Format::PNG);
    expect(fn (): QrCodeResult => $renderer->render('test'))->toThrow(MissingExtensionException::class, 'The imagick or gd extension is required to generate raster QR codes');

    $config->setFormat(Format::WEBP);
    expect(fn (): QrCodeResult => $renderer->render('test'))->toThrow(MissingExtensionException::class, 'The imagick or gd extension is required to generate raster QR codes');
});

it('throws an exception if trying to generate a non-PNG raster using only GD', function (): void {
    Environment::enableExtension('gd');
    Environment::disableExtension('imagick');

    $config = new Config;
    $renderer = new BaconRenderer($config);
    $config->setFormat(Format::WEBP);

    expect(fn (): QrCodeResult => $renderer->render('test'))
        ->toThrow(MissingExtensionException::class, 'The Imagick extension is required to generate the webp format.');
});

it('successfully generates SVG and EPS without requiring any image extensions', function (): void {
    Environment::disableExtension('imagick');
    Environment::disableExtension('gd');

    $config = new Config;
    $renderer = new BaconRenderer($config);

    // SVG should succeed
    $config->setFormat(Format::SVG);
    $QrCodeResult = $renderer->render('test');
    expect((string) $QrCodeResult)->toContain('<svg');

    // EPS should succeed
    $config->setFormat(Format::EPS);
    $epsQr = $renderer->render('test');
    expect((string) $epsQr)->toContain('%!PS-Adobe');
});

it('renders an html string without merged image', function (): void {
    $config = new Config;
    $renderer = new BaconRenderer($config);

    $QrCodeResult = $renderer->render('test payload');

    expect($QrCodeResult)->toBeInstanceOf(QrCodeResult::class)
        ->and($QrCodeResult->toHtml())->toContain('<svg')
        ->and($QrCodeResult->toHtml())->not->toContain('href="data:image/png;base64');
});

it('throws an exception when trying to merge images into EPS format without gd extension', function () use ($tinyPng): void {
    Environment::disableExtension('gd');

    $config = new Config;
    $config->setFormat(Format::EPS);
    $config->setupMergeString($tinyPng);

    $renderer = new BaconRenderer($config);

    expect(fn (): QrCodeResult => $renderer->render('test'))->toThrow(MissingExtensionException::class, 'The GD extension is required to merge images into EPS format.');
});

it('renders an html string with merged image', function () use ($tinyPng): void {
    $config = new Config;
    $config->setFormat(Format::SVG);
    $config->setupMergeString($tinyPng);

    $renderer = new BaconRenderer($config);

    $QrCodeResult = $renderer->render('test payload');

    expect($QrCodeResult)->toBeInstanceOf(QrCodeResult::class)
        ->and($QrCodeResult->toHtml())->toContain('<svg')
        ->and($QrCodeResult->toHtml())->toContain('href="data:image/png;base64');
});

it('throws a MissingExtensionException when using GD library with a non-square style', function (): void {
    Environment::enableExtension('gd');
    Environment::disableExtension('imagick');

    $config = new Config;
    $config->setFormat(Format::PNG);

    $config->setupStyle(Style::DOT);

    $renderer = new BaconRenderer($config);

    expect(fn (): QrCodeResult => $renderer->render('https://linkxtr.com'))
        ->toThrow(
            MissingExtensionException::class,
            'The Imagick extension is required to use non-square module styles (e.g., DOT, ROUND). Please enable the Imagick extension or use the SQUARE style.'
        );
});

it('throws a MissingExtensionException when using GD library with a gradient', function (): void {
    Environment::enableExtension('gd');
    Environment::disableExtension('imagick');

    $config = new Config;
    $config->setFormat(Format::PNG);

    $config->setupGradient(new Rgb(255, 0, 0), new Rgb(0, 0, 255));

    $renderer = new BaconRenderer($config);

    expect(fn (): QrCodeResult => $renderer->render('https://linkxtr.com'))->toThrow(
        MissingExtensionException::class,
        'The Imagick extension is required to use gradients. Please enable the Imagick extension or use solid colors.'
    );
});

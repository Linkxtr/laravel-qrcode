<?php

declare(strict_types=1);

use Linkxtr\QrCode\DTOs\Config;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Exceptions\MissingExtensionException;
use Linkxtr\QrCode\Mergers\EpsMerger;
use Linkxtr\QrCode\Mergers\ImagickMerger;
use Linkxtr\QrCode\Mergers\RasterMerger;
use Linkxtr\QrCode\Mergers\SvgMerger;
use Linkxtr\QrCode\Support\Bacon\MergerFactory;

covers(MergerFactory::class);

$tinyPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');

it('calls the correct merger based on format', function () use ($tinyPng): void {
    global $mockImagickLoaded, $mockGdLoaded;
    $mockImagickLoaded = true;
    $mockGdLoaded = true;

    $config = new Config;

    $config->setFormat(Format::SVG);
    $config->setupMergeString($tinyPng);

    $mergerFactory = new MergerFactory($config);
    expect(invade($mergerFactory)->getMerger('test'))->toBeInstanceOf(SvgMerger::class);

    $config->setFormat(Format::EPS);
    $config->setupMergeString($tinyPng);

    $mergerFactory = new MergerFactory($config);
    expect(invade($mergerFactory)->getMerger('test'))->toBeInstanceOf(EpsMerger::class);

    $config->setFormat(Format::PNG);
    $config->setupMergeString($tinyPng);

    $mergerFactory = new MergerFactory($config);
    expect(invade($mergerFactory)->getMerger($tinyPng))->toBeInstanceOf(ImagickMerger::class);

    $mockImagickLoaded = false;
    expect(invade($mergerFactory)->getMerger($tinyPng))->toBeInstanceOf(RasterMerger::class);
});

it('throws missing extension exception when gd is not loaded and format is eps', function (): void {
    global $mockGdLoaded;
    $mockGdLoaded = false;

    $config = new Config;
    $config->setFormat(Format::EPS);
    $config->setupMergeString('');

    expect(fn (): string => MergerFactory::make($config)->merge('test'))->toThrow(
        MissingExtensionException::class,
        'The GD extension is required to merge images into EPS format.'
    );
});

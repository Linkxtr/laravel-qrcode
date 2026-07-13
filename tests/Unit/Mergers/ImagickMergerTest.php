<?php

declare(strict_types=1);

use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Exceptions\ImageMergeException;
use Linkxtr\QrCode\Mergers\ImagickMerger;

covers(ImagickMerger::class);

beforeEach(function (): void {
    if (! extension_loaded('imagick')) {
        $this->markTestSkipped('The imagick extension is required for ImagickMerger tests.');
    }
});

$tinyPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');

$getTallPng = function (): string {
    $image = new Imagick;
    $image->newImage(10, 100, new ImagickPixel('black'));
    $image->setImageFormat('png');

    return $image->getImageBlob();
};

$getComplexPng = function (): string {
    $image = new Imagick;
    $image->newImage(20, 20, new ImagickPixel('white'));
    $image->addNoiseImage(Imagick::NOISE_GAUSSIAN, Imagick::CHANNEL_ALL);
    $image->setImageFormat('png');

    return $image->getImageBlob();
};

test('it throws exception for invalid percentages', function () use ($tinyPng): void {
    expect(fn (): ImagickMerger => new ImagickMerger($tinyPng, $tinyPng, 0))
        ->toThrow(ImageMergeException::class, 'Percentage for merging the image must be between 0 and 1.');

    expect(fn (): ImagickMerger => new ImagickMerger($tinyPng, $tinyPng, 1))
        ->toThrow(ImageMergeException::class, 'Percentage for merging the image must be between 0 and 1.');
});

test('it throws exception for unsupported formats', function () use ($tinyPng): void {
    $merger = new ImagickMerger($tinyPng, $tinyPng, 0.2);

    expect(fn (): ImagickMerger => $merger->setFormat(Format::SVG))
        ->toThrow(ImageMergeException::class, 'ImagickMerger only supports "png" or "webp" formats.');
});

test('it successfully merges two images as PNG', function () use ($tinyPng): void {
    $merger = new ImagickMerger($tinyPng, $tinyPng, 0.9);
    $merger->setFormat(Format::PNG);

    $result = $merger->merge();

    expect(substr($result, 1, 3))->toBe('PNG');
});

it('use default percentage if not set', function () use ($tinyPng): void {
    $merger = new ImagickMerger($tinyPng, $tinyPng);
    expect(invade($merger)->percentage)->toBe(0.2);
});

test('it successfully merges and sets compression for WEBP format', function () use ($tinyPng): void {
    if (! in_array('WEBP', Imagick::queryFormats('WEBP'), true)) {
        $this->markTestSkipped('ImageMagick WEBP support is required for this assertion.');
    }

    $merger = new ImagickMerger($tinyPng, $tinyPng, 0.1);
    $merger->setFormat(Format::WEBP);

    $result = $merger->merge();

    expect($result)->toContain('WEBP');
});

test('it constrains the merge image if height exceeds canvas bounds', function () use ($tinyPng, $getTallPng): void {
    $merger = new ImagickMerger($tinyPng, $getTallPng(), 0.5);

    $result = $merger->merge();

    expect(substr($result, 1, 3))->toBe('PNG');
});

test('it properly concatenates the Imagick exception message', function (): void {
    $merger = new ImagickMerger('not-an-image', 'not-an-image', 0.2);

    $caughtException = null;

    try {
        $merger->merge();
    } catch (RuntimeException $runtimeException) {
        $caughtException = $runtimeException;
    }

    expect($caughtException)->not->toBeNull();

    $message = $caughtException->getMessage();

    expect($message)->toStartWith('Imagick merge failed: ')
        ->and($message)->not->toBe('Imagick merge failed: ');
});

test('it strictly maintains aspect ratio during calculations', function () use ($getTallPng): void {
    $image = new Imagick;
    $image->newImage(100, 100, new ImagickPixel('white'));
    $image->setImageFormat('png');

    $whiteSquareImage = $image->getImageBlob();

    $merger = new ImagickMerger($whiteSquareImage, $getTallPng(), 0.5);
    $result = $merger->merge();

    $resultImage = new Imagick;
    $resultImage->readImageBlob($result);

    $colors = $resultImage->getImagePixelColor(50, 30)->getColor();

    expect($colors['r'])->toBe(0)
        ->and($colors['g'])->toBe(0)
        ->and($colors['b'])->toBe(0);
});

test('it strictly truncates composite coordinates and positions logo exactly in the center', function (): void {
    $source = new Imagick;
    $source->newImage(101, 101, new ImagickPixel('white'));
    $source->setImageFormat('png');

    $merge = new Imagick;
    $merge->newImage(20, 20, new ImagickPixel('black'));
    $merge->setImageFormat('png');

    $merger = new ImagickMerger($source->getImageBlob(), $merge->getImageBlob(), 0.2);
    $result = $merger->merge();

    $img = new Imagick;
    $img->readImageBlob($result);

    $colors = $img->getImagePixelColor(40, 39)->getColor();
    expect($colors['r'])->toBe(255)->and($colors['g'])->toBe(255)->and($colors['b'])->toBe(255);

    $colorsLogo = $img->getImagePixelColor(40, 40)->getColor();
    expect($colorsLogo['r'])->toBe(0)->and($colorsLogo['g'])->toBe(0)->and($colorsLogo['b'])->toBe(0);
});

test('it strictly resizes the merge image', function () use ($getTallPng): void {
    $source = new Imagick;
    $source->newImage(100, 100, new ImagickPixel('white'));
    $source->setImageFormat('png');

    $merger = new ImagickMerger($source->getImageBlob(), $getTallPng(), 0.5);
    $result = $merger->merge();

    $img = new Imagick;
    $img->readImageBlob($result);

    $colors = $img->getImagePixelColor(50, 10)->getColor();

    expect($colors['r'])->toBe(255)->and($colors['g'])->toBe(255)->and($colors['b'])->toBe(255);
});

test('it strictly scales wide images using the width boundary', function (): void {
    $source = new Imagick;
    $source->newImage(100, 100, new ImagickPixel('white'));
    $source->setImageFormat('png');

    $merge = new Imagick;
    $merge->newImage(100, 10, new ImagickPixel('black'));
    $merge->setImageFormat('png');

    $merger = new ImagickMerger($source->getImageBlob(), $merge->getImageBlob(), 0.2);
    $result = $merger->merge();

    $img = new Imagick;
    $img->readImageBlob($result);

    expect($img->getImagePixelColor(60, 49)->getColor()['r'])->toBe(255);
    expect($img->getImagePixelColor(59, 49)->getColor()['r'])->toBe(0);
});

test('it enforces a strict minimum dimension of 1 pixel', function (): void {
    $source = new Imagick;
    $source->newImage(100, 100, new ImagickPixel('white'));
    $source->setImageFormat('png');

    $merge = new Imagick;
    $merge->newImage(10, 10, new ImagickPixel('black'));
    $merge->setImageFormat('png');

    $merger = new ImagickMerger($source->getImageBlob(), $merge->getImageBlob(), 0.001);
    $result = $merger->merge();

    $img = new Imagick;
    $img->readImageBlob($result);

    expect($img->getImagePixelColor(49, 49)->getColor()['r'])->toBe(0);

    expect($img->getImagePixelColor(50, 49)->getColor()['r'])->toBe(255);

    expect($img->getImagePixelColor(49, 50)->getColor()['r'])->toBe(255);
});

test('it strictly applies a blur factor of exactly 1 during resize', function () use ($getComplexPng): void {
    $complexImage = $getComplexPng();

    $merger = new ImagickMerger($complexImage, $complexImage, 0.5);
    $resultBlob = $merger->merge();

    // Baseline with exact blur of 1
    $expectedSource = new Imagick;
    $expectedSource->readImageBlob($complexImage);

    $expectedMerge = new Imagick;
    $expectedMerge->readImageBlob($complexImage);
    $expectedMerge->resizeImage(10, 10, Imagick::FILTER_LANCZOS, 1);

    $expectedSource->compositeImage($expectedMerge, Imagick::COMPOSITE_DEFAULT, 5, 5);
    $expectedSource->setImageFormat('png');

    expect($resultBlob)->toBe($expectedSource->getImageBlob());
});

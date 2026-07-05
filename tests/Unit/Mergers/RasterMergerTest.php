<?php

declare(strict_types=1);

use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Exceptions\ImageMergeException;
use Linkxtr\QrCode\Mergers\RasterMerger;
use Linkxtr\QrCode\Support\Image;

covers(RasterMerger::class, Image::class);

$tinyPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');

test('it throws exception for invalid percentages', function () use ($tinyPng): void {
    expect(fn (): RasterMerger => new RasterMerger($tinyPng, $tinyPng, 0))
        ->toThrow(ImageMergeException::class, 'Percentage for merging the image must be between 0 and 1.');

    expect(fn (): RasterMerger => new RasterMerger($tinyPng, $tinyPng, 1))
        ->toThrow(ImageMergeException::class, 'Percentage for merging the image must be between 0 and 1.');
});

test('it throws exception for unsupported formats', function () use ($tinyPng): void {
    expect(fn (): RasterMerger => (new RasterMerger($tinyPng, $tinyPng, 0.2))->setFormat(Format::SVG))
        ->toThrow(ImageMergeException::class, 'RasterMerger only supports "png" or "webp" formats.');
});

test('it properly propagates ImageMergeException for invalid image data', function () use ($tinyPng): void {
    expect(fn (): RasterMerger => new RasterMerger('not-an-image', $tinyPng, 0.2))
        ->toThrow(ImageMergeException::class, 'Invalid image data provided to Image.');
});

test('it successfully merges two images as PNG', function () use ($tinyPng): void {
    $merger = new RasterMerger($tinyPng, $tinyPng, 0.1);
    $result = $merger->merge();

    expect(substr($result, 1, 3))->toBe('PNG');
});

it('use default percentage if not set', function () use ($tinyPng): void {
    $merger = new RasterMerger($tinyPng, $tinyPng);
    expect(invade($merger)->percentage)->toBe(0.2);
});

test('it successfully merges and sets formatting for WEBP', function () use ($tinyPng): void {
    $result = (new RasterMerger($tinyPng, $tinyPng, 0.9))->setFormat(Format::WEBP)->merge();

    expect($result)->toBeString()->toContain('WEBP');
});

it('throws exception if transparent color cannot be created', function () use ($tinyPng): void {
    global $mockImageColorAllocateAlpha;
    $mockImageColorAllocateAlpha = false;

    expect(fn (): string => (new RasterMerger($tinyPng, $tinyPng, 0.2))->merge())
        ->toThrow(ImageMergeException::class, 'Failed to create transparent color.');
});

it('throws exception if image canvas cannot be created', function () use ($tinyPng): void {
    global $mockImageCreateTrueColor;
    $mockImageCreateTrueColor = false;

    expect(fn (): string => (new RasterMerger($tinyPng, $tinyPng, 0.2))->merge())
        ->toThrow(ImageMergeException::class, 'Failed to create image canvas.');
});

it('throws exception if image creation fails returning false', function (): void {
    global $mockImageCreateFromString;
    $mockImageCreateFromString = false;

    expect(fn (): Image => new Image('valid string but mock fails'))
        ->toThrow(ImageMergeException::class, 'Invalid image data provided to Image.');
});

it('constrains merge image if it exceeds vertical bounds', function (): void {
    $tallCanvas = imagecreatetruecolor(10, 200);
    ob_start();
    imagepng($tallCanvas);
    $tallImageData = ob_get_clean();
    unset($tallCanvas);

    $canvas = imagecreatetruecolor(100, 100);
    $white = imagecolorallocate($canvas, 255, 255, 255);
    imagefill($canvas, 0, 0, $white);
    ob_start();
    imagepng($canvas);
    $squarePng = ob_get_clean();

    $merge = (string) $tallImageData;

    $merger = new RasterMerger($squarePng, $merge, 0.5);
    $output = $merger->setFormat(Format::PNG)->merge();

    expect($output)->toBeString();

    $resultImage = imagecreatefromstring($output);
    $colorIndex = imagecolorat($resultImage, 50, 30);
    $colors = imagecolorsforindex($resultImage, $colorIndex);

    expect($colors['red'])->toBe(0)
        ->and($colors['green'])->toBe(0)
        ->and($colors['blue'])->toBe(0);

    // Verify it constrained width as well (mutant check)
    $bgIndex = imagecolorat($resultImage, 25, 30);
    $bgColors = imagecolorsforindex($resultImage, $bgIndex);

    expect($bgColors['red'])->toBe(255)
        ->and($bgColors['green'])->toBe(255)
        ->and($bgColors['blue'])->toBe(255);
});

it('preserves full alpha transparency on the canvas to kill alpha blending and savealpha mutants', function (): void {
    $canvas = \imagecreatetruecolor(100, 100);
    \imagealphablending($canvas, false);
    \imagesavealpha($canvas, true);
    $transparent = \imagecolorallocatealpha($canvas, 0, 0, 0, 127);
    \imagefill($canvas, 0, 0, $transparent);
    \ob_start();
    \imagepng($canvas);
    $transparentSource = \ob_get_clean();

    $logo = \imagecreatetruecolor(10, 10);
    $black = \imagecolorallocate($logo, 0, 0, 0);
    \imagefill($logo, 0, 0, $black);
    \ob_start();
    \imagepng($logo);
    $blackLogo = \ob_get_clean();

    $result = (new RasterMerger($transparentSource, $blackLogo, 0.2))->merge();

    $resultImage = \imagecreatefromstring($result);

    $colorIndex = \imagecolorat($resultImage, 10, 10);
    $colors = \imagecolorsforindex($resultImage, $colorIndex);

    expect($colors['alpha'])->toBe(127);
});

it('throws exception when output content is an empty string', function () use ($tinyPng): void {
    global $mock_imagepng_empty;
    $mock_imagepng_empty = true;

    $merger = new RasterMerger($tinyPng, $tinyPng, 0.2);
    expect(fn (): string => $merger->merge())
        ->toThrow(ImageMergeException::class, 'Failed to render image binary.');
});

test('it throws a logic exception if an unsupported format bypasses validation', function () use ($tinyPng): void {
    $merger = new RasterMerger($tinyPng, $tinyPng, 0.2);
    invade($merger)->format = Format::EPS;

    expect(fn (): string => $merger->merge())
        ->toThrow(ImageMergeException::class, 'RasterMerger only supports "png" or "webp" formats.');
})->after(function (): void {
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
});

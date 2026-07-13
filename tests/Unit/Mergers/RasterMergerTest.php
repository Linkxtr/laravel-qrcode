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

it('throws exception if output buffer capture fails', function () use ($tinyPng): void {
    global $mockObGetClean;
    $mockObGetClean = false;

    expect(fn (): string => (new RasterMerger($tinyPng, $tinyPng, 0.2))->merge())
        ->toThrow(ImageMergeException::class, 'Failed to render image binary.');
})->after(function (): void {
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
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

test('it strictly scales images, casts coordinates, and blends alpha channels to kill all visual mutants', function (): void {
    $sourceCanvas = \imagecreatetruecolor(101, 101);
    \imagealphablending($sourceCanvas, false);
    \imagesavealpha($sourceCanvas, true);
    \imagefill($sourceCanvas, 0, 0, \imagecolorallocatealpha($sourceCanvas, 0, 0, 0, 127));
    \ob_start();
    \imagepng($sourceCanvas);
    $sourcePng = \ob_get_clean();

    $mergeCanvas = \imagecreatetruecolor(100, 20);
    \imagefill($mergeCanvas, 0, 0, \imagecolorallocate($mergeCanvas, 0, 255, 0));
    \ob_start();
    \imagepng($mergeCanvas);
    $mergePng = \ob_get_clean();

    $merger = new RasterMerger($sourcePng, $mergePng, 0.2);
    $merger->setFormat(Format::PNG);

    $resultBlob = $merger->merge();

    $expectedCanvas = \imagecreatetruecolor(101, 101);
    \imagealphablending($expectedCanvas, false);
    \imagesavealpha($expectedCanvas, true);
    \imagefill($expectedCanvas, 0, 0, \imagecolorallocatealpha($expectedCanvas, 0, 0, 0, 127));
    \imagealphablending($expectedCanvas, true);

    $srcObj = \imagecreatefromstring($sourcePng);
    \imagecopy($expectedCanvas, $srcObj, 0, 0, 0, 0, 101, 101);

    $mergeObj = \imagecreatefromstring($mergePng);
    \imagecopyresampled($expectedCanvas, $mergeObj, 40, 48, 0, 0, 20, 4, 100, 20);
    \imagesavealpha($expectedCanvas, true);

    \ob_start();
    \imagepng($expectedCanvas);
    $expectedBlob = \ob_get_clean();

    expect(md5($resultBlob))->toBe(md5($expectedBlob));
});

test('it enforces a strict minimum dimension of 1 pixel to kill max() boundary mutants', function (): void {
    $source = \imagecreatetruecolor(100, 100);
    \imagefill($source, 0, 0, \imagecolorallocate($source, 255, 255, 255));
    \ob_start();
    \imagepng($source);
    $sourcePng = \ob_get_clean();

    $merge = \imagecreatetruecolor(10, 10);
    \imagefill($merge, 0, 0, \imagecolorallocate($merge, 0, 0, 0));
    \ob_start();
    \imagepng($merge);
    $mergePng = \ob_get_clean();

    $merger = new RasterMerger($sourcePng, $mergePng, 0.001);
    $result = $merger->merge();

    $img = \imagecreatefromstring($result);

    expect(\imagecolorsforindex($img, \imagecolorat($img, 48, 48))['red'])->toBe(255);

    expect(\imagecolorsforindex($img, \imagecolorat($img, 49, 49))['red'])->toBe(0);

    expect(\imagecolorsforindex($img, \imagecolorat($img, 50, 50))['red'])->toBe(255);
});

test('it strictly scales tall images using the height boundary to kill math mutants', function (): void {
    $source = \imagecreatetruecolor(100, 100);
    \imagefill($source, 0, 0, \imagecolorallocate($source, 255, 255, 255));
    \ob_start();
    \imagepng($source);
    $sourcePng = \ob_get_clean();

    $merge = \imagecreatetruecolor(10, 100);
    \imagefill($merge, 0, 0, \imagecolorallocate($merge, 0, 0, 0));
    \ob_start();
    \imagepng($merge);
    $mergePng = \ob_get_clean();

    $merger = new RasterMerger($sourcePng, $mergePng, 0.2);
    $resultBlob = $merger->merge();
    $img = \imagecreatefromstring($resultBlob);

    expect(\imagecolorsforindex($img, \imagecolorat($img, 50, 39))['red'])->toBe(255);
});

test('it enforces a strict minimum dimension of exactly 1 pixel for both axes to kill max(2) mutants', function (): void {
    $source = \imagecreatetruecolor(100, 100);
    \imagefill($source, 0, 0, \imagecolorallocate($source, 0, 0, 255));
    \ob_start();
    \imagepng($source);
    $sourcePng = \ob_get_clean();

    $merge = \imagecreatetruecolor(10, 10);
    \imagefill($merge, 0, 0, \imagecolorallocate($merge, 255, 0, 0));
    \ob_start();
    \imagepng($merge);
    $mergePng = \ob_get_clean();

    $merger = new RasterMerger($sourcePng, $mergePng, 0.001);
    $img = \imagecreatefromstring($merger->merge());

    expect(\imagecolorsforindex($img, \imagecolorat($img, 49, 49))['red'])->toBe(255);

    expect(\imagecolorsforindex($img, \imagecolorat($img, 50, 49))['blue'])->toBe(255);

    expect(\imagecolorsforindex($img, \imagecolorat($img, 49, 50))['blue'])->toBe(255);
});

test('it strictly disables alpha blending before canvas fill to guarantee a transparent background', function (): void {
    $source = \imagecreatetruecolor(100, 100);
    \imagealphablending($source, false);
    \imagesavealpha($source, true);
    \imagefill($source, 0, 0, \imagecolorallocatealpha($source, 0, 0, 0, 127));
    \ob_start();
    \imagepng($source);
    $sourcePng = \ob_get_clean();

    $merge = \imagecreatetruecolor(10, 10);
    \imagealphablending($merge, false);
    \imagesavealpha($merge, true);
    \imagefill($merge, 0, 0, \imagecolorallocatealpha($merge, 0, 0, 0, 127));
    \ob_start();
    \imagepng($merge);
    $mergePng = \ob_get_clean();

    $merger = new RasterMerger($sourcePng, $mergePng, 0.2);
    $img = \imagecreatefromstring($merger->merge());

    expect(\imagecolorsforindex($img, \imagecolorat($img, 50, 50))['alpha'])->toBe(127);
});

test('it strictly maps all operations and coordinates to the canvas to kill GD rendering mutants', function (): void {
    $source = \imagecreatetruecolor(10, 10);
    \imagealphablending($source, false);
    \imagesavealpha($source, true);
    $transparent = \imagecolorallocatealpha($source, 0, 0, 0, 127);
    \imagefill($source, 0, 0, $transparent);

    $red = \imagecolorallocate($source, 255, 0, 0);
    \imagesetpixel($source, 0, 0, $red);
    \ob_start();
    \imagepng($source);
    $sourcePng = \ob_get_clean();
    $merge = \imagecreatetruecolor(2, 2);
    $green = \imagecolorallocate($merge, 0, 255, 0);
    \imagefill($merge, 0, 0, $green);
    \ob_start();
    \imagepng($merge);
    $mergePng = \ob_get_clean();

    $merger = new RasterMerger($sourcePng, $mergePng, 0.2);
    $result = \imagecreatefromstring($merger->merge());

    $c00 = \imagecolorsforindex($result, \imagecolorat($result, 0, 0));
    expect($c00['red'])->toBe(255)->and($c00['alpha'])->toBe(0);

    $c01 = \imagecolorsforindex($result, \imagecolorat($result, 0, 1));
    expect($c01['alpha'])->toBe(127);

    $c10 = \imagecolorsforindex($result, \imagecolorat($result, 1, 0));
    expect($c10['alpha'])->toBe(127);
});

test('it strictly enables alpha blending to composite transparent logos and saves the alpha channel', function (): void {
    $source = \imagecreatetruecolor(10, 10);
    \imagefill($source, 0, 0, \imagecolorallocate($source, 255, 255, 255));
    \ob_start();
    \imagepng($source);
    $sourcePng = \ob_get_clean();

    $merge = \imagecreatetruecolor(2, 2);
    \imagealphablending($merge, false);
    \imagesavealpha($merge, true);
    \imagefill($merge, 0, 0, \imagecolorallocatealpha($merge, 0, 0, 0, 127));
    \ob_start();
    \imagepng($merge);
    $mergePng = \ob_get_clean();

    $merger = new RasterMerger($sourcePng, $mergePng, 0.2);
    $merger->setFormat(Format::PNG);

    $result = \imagecreatefromstring($merger->merge());

    $color = \imagecolorsforindex($result, \imagecolorat($result, 4, 4));

    expect($color['alpha'])->toBe(0)
        ->and($color['red'])->toBe(255);
});

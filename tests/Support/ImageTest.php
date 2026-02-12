<?php

use Linkxtr\QrCode\Support\Image;

function getImageTestAssetPath(): string
{
    $path = __DIR__.'/../images/linkxtr.png';

    if (! file_exists($path)) {
        throw new \RuntimeException('Image not found at '.$path);
    }

    return $path;
}

function getImageTestAsset(): Image
{
    return new Image(file_get_contents(getImageTestAssetPath()));
}

it('loads an image string into a resource', function () {
    $path = getImageTestAssetPath();
    $image = getImageTestAsset();

    $expected = imagecreatefrompng($path);
    $actual = $image->getImageResource();

    $w = imagesx($expected);
    $h = imagesy($expected);
    expect(imagesx($actual))->toBe($w);
    expect(imagesy($actual))->toBe($h);

    // Sample a grid of pixels for equality
    $xStep = max(1, intdiv($w, 10));
    $yStep = max(1, intdiv($h, 10));
    for ($y = 0; $y < $h; $y += $yStep) {
        for ($x = 0; $x < $w; $x += $xStep) {
            expect(imagecolorat($actual, $x, $y))
                ->toBe(imagecolorat($expected, $x, $y));
        }
    }
    unset($expected);
});

it('gets the width of the image', function () {
    $path = getImageTestAssetPath();
    $image = getImageTestAsset();

    $expected = imagecreatefrompng($path);
    expect($image->getWidth())->toBe(imagesx($expected));
    unset($expected);
});

it('gets the height of the image', function () {
    $path = getImageTestAssetPath();
    $image = getImageTestAsset();

    $expected = imagecreatefrompng($path);
    expect($image->getHeight())->toBe(imagesy($expected));
    unset($expected);
});

it('throws exception for invalid image data', function () {
    expect(fn () => new Image('invalid data'))
        ->toThrow(\InvalidArgumentException::class, 'Invalid image data provided to Image.');
});

it('can replace the image resource', function () {
    $image = getImageTestAsset();

    $newImg = imagecreate(100, 100);
    $image->setImageResource($newImg);

    expect($image->getWidth())->toBe(100);
    expect($image->getHeight())->toBe(100);
    expect($image->getImageResource())->toBe($newImg);
});

it('throws exception when accessing methods after destruction', function () {
    $image = new Image(file_get_contents(getImageTestAssetPath()));
    $image->__destruct();

    expect(fn () => $image->getWidth())->toThrow(\RuntimeException::class);
    expect(fn () => $image->getHeight())->toThrow(\RuntimeException::class);
    expect(fn () => $image->getImageResource())->toThrow(\RuntimeException::class);
});

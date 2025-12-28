<?php

namespace Linkxtr\QrCode\Renderer\Image;

// Globals to control mocks
$mockImageCreateTrueColor = true;
$mockImageColorAllocate = true;

function imagecreatetruecolor(int $width, int $height)
{
    global $mockImageCreateTrueColor;
    if (isset($mockImageCreateTrueColor) && ! $mockImageCreateTrueColor) {
        return false;
    }

    return \imagecreatetruecolor($width, $height);
}

function imagecolorallocate(\GdImage $image, int $red, int $green, int $blue)
{
    global $mockImageColorAllocate;
    if (isset($mockImageColorAllocate) && ! $mockImageColorAllocate) {
        return false;
    }

    return \imagecolorallocate($image, $red, $green, $blue);
}

function imagecolorallocatealpha(\GdImage $image, int $red, int $green, int $blue, int $alpha)
{
    global $mockImageColorAllocate;
    if (isset($mockImageColorAllocate) && ! $mockImageColorAllocate) {
        return false;
    }

    return \imagecolorallocatealpha($image, $red, $green, $blue, $alpha);
}

namespace Tests\GdImageBackEnd;

use BaconQrCode\Exception\RuntimeException;
use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Path\Move;
use BaconQrCode\Renderer\Path\Path;
use Linkxtr\QrCode\Renderer\Image\GdImageBackEnd;

test('GdImageBackEnd exceptions and edge cases', function () {
    $backend = new GdImageBackEnd;
    $color = new Rgb(0, 0, 0);

    // Exception: Image size must be at least 1 pixel
    expect(fn () => $backend->new(0, $color))->toThrow(RuntimeException::class, 'Image size must be at least 1 pixel');

    // Exception: No image started (drawPathWithColor)
    expect(fn () => $backend->drawPathWithColor(new Path, $color))->toThrow(RuntimeException::class, 'No image started');

    // Exception: No image started (done)
    expect(fn () => $backend->done())->toThrow(RuntimeException::class, 'No image started');

    // Matrix stack operations
    $backend->push();
    $backend->pop();
    // Exception: Matrix stack empty
    expect(fn () => $backend->pop())->toThrow(RuntimeException::class, 'Matrix stack is empty');
});

test('GdImageBackEnd transformations and drawing', function () {
    $backend = new GdImageBackEnd;
    $color = new Rgb(0, 0, 0);

    $backend->new(100, $color);
    $backend->scale(0.5);
    $backend->translate(10, 10);
    $backend->rotate(45);
    $backend->push();
    $backend->pop();

    // Draw path with multiple moves to trigger internal flush logic
    $path = new Path;
    $path = $path->move(0, 0);
    $path = $path->line(10, 0);
    $path = $path->line(10, 10);
    // Move again WITHOUT closing, so previous polygon is flushed
    $path = $path->move(20, 20);
    $path = $path->line(30, 30);
    $path = $path->close();

    $backend->drawPathWithColor($path, $color);

    $result = $backend->done();
    expect($result)->toBeString();
});

test('GdImageBackEnd creation failure', function () {
    global $mockImageCreateTrueColor;
    $mockImageCreateTrueColor = false;

    $backend = new GdImageBackEnd;
    $color = new Rgb(0, 0, 0);

    try {
        $backend->new(100, $color);
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('Could not create GD image resource');
    } finally {
        $mockImageCreateTrueColor = true;
    }
});

test('GdImageBackEnd allocation failure', function () {
    global $mockImageColorAllocate;
    $mockImageColorAllocate = false;

    $backend = new GdImageBackEnd;
    $color = new Rgb(0, 0, 0);

    try {
        $backend->new(100, $color);
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('Could not allocate background color');
    } finally {
        $mockImageColorAllocate = true;
    }
});

test('GdImageBackEnd draws path with Alpha color', function () {
    $backend = new GdImageBackEnd;
    $backend->new(100, new Rgb(255, 255, 255));

    $color = new Alpha(50, new Rgb(0, 0, 0));
    $path = (new Path)->move(0, 0)->line(10, 10);

    $backend->drawPathWithColor($path, $color);
    expect($backend->done())->toBeString();
});

test('GdImageBackEnd drawPath allocation failure', function () {
    global $mockImageColorAllocate;

    $backend = new GdImageBackEnd;
    $backend->new(100, new Rgb(255, 255, 255));
    $path = (new Path)->move(0, 0)->line(10, 10);
    $color = new Rgb(0, 0, 0);

    $mockImageColorAllocate = false;

    try {
        $backend->drawPathWithColor($path, $color);
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('Could not allocate color');
    } finally {
        $mockImageColorAllocate = true;
    }
});

test('GdImageBackEnd flushPolygon defensive check', function () {
    $backend = new GdImageBackEnd;
    // Image is null by default

    $reflection = new \ReflectionClass($backend);
    $method = $reflection->getMethod('flushPolygon');
    $method->setAccessible(true);

    // Call with some points, should return early and not error
    $method->invoke($backend, [0, 0, 10, 10, 20, 20], 0);

    expect(true)->toBeTrue(); // Just assert we reached here without error
});

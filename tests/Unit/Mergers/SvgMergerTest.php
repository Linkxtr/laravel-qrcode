<?php

declare(strict_types=1);

use Linkxtr\QrCode\Mergers\SvgMerger;

covers(SvgMerger::class);

$tinyPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');

test('it successfully merges a raster image into an svg', function () use ($tinyPng) {
    $svg = '<svg width="100px" height="100px" viewBox="0 0 100 100"></svg>';

    $merger = new SvgMerger($svg, $tinyPng, 0.2);
    $result = $merger->merge();

    expect($result)->toContain('<image')
        ->and($result)->toContain('href="data:image/png;base64,')
        ->and($result)->toContain('</svg>')
        ->and(strpos($result, '<image'))->toBeLessThan(strpos($result, '</svg>'));
});

test('it calculates correct image coordinates and sizes', function () use ($tinyPng) {
    $svg = '<svg width="100" height="100"></svg>';

    $merger = new SvgMerger($svg, $tinyPng, 0.2);
    $result = $merger->merge();

    expect($result)->toContain('x="40"')
        ->and($result)->toContain('y="40"')
        ->and($result)->toContain('width="20"')
        ->and($result)->toContain('height="20"');
});

test('it throws exception for invalid percentages', function () use ($tinyPng) {
    $svg = '<svg width="100" height="100"></svg>';

    expect(fn () => (new SvgMerger($svg, $tinyPng, 0))->merge())
        ->toThrow(InvalidArgumentException::class, '$percentage must be between 0 and 1');

    expect(fn () => (new SvgMerger($svg, $tinyPng, 1))->merge())
        ->toThrow(InvalidArgumentException::class, '$percentage must be between 0 and 1');
});

test('it throws exception if svg is missing dimensions', function () use ($tinyPng) {
    $svg = '<svg viewBox="0 0 100 100"></svg>';

    expect(fn () => (new SvgMerger($svg, $tinyPng, 0.2))->merge())
        ->toThrow(InvalidArgumentException::class, 'Could not determine SVG dimensions.');

    $svg = '<svg height="100"></svg>';
    expect(fn () => (new SvgMerger($svg, $tinyPng, 0.2))->merge())
        ->toThrow(InvalidArgumentException::class, 'Could not determine SVG dimensions.');

    $svg = '<svg width="100"></svg>';
    expect(fn () => (new SvgMerger($svg, $tinyPng, 0.2))->merge())
        ->toThrow(InvalidArgumentException::class, 'Could not determine SVG dimensions.');
});

test('it throws exception for invalid image data', function () {
    $svg = '<svg width="100" height="100"></svg>';

    expect(fn () => (new SvgMerger($svg, 'not-an-image', 0.2))->merge())
        ->toThrow(InvalidArgumentException::class, 'Invalid image data provided for merge.');
});

test('it throws exception if svg is missing closing tag', function () use ($tinyPng) {
    $svg = '<svg width="100" height="100">';

    expect(fn () => (new SvgMerger($svg, $tinyPng, 0.2))->merge())
        ->toThrow(InvalidArgumentException::class, 'Invalid SVG content: closing tag not found.');
});

test('it strictly truncates float dimensions before calculating percentages', function () use ($tinyPng) {
    $svg = '<svg width="11.9" height="22.9"></svg>';

    $merger = new SvgMerger($svg, $tinyPng, 0.9);
    $result = $merger->merge();

    expect($result)->toContain('x="1"')
        ->and($result)->toContain('y="6"')
        ->and($result)->toContain('width="9"')
        ->and($result)->toContain('height="9"');
});

test('it accurately calculates ratio for non-square images', function () {
    $rectPng = file_get_contents(__DIR__.'/../../Support/Fixtures/images/300X200.png');

    $svg = '<svg width="100" height="100"></svg>';

    $merger = new SvgMerger($svg, $rectPng, 0.2);
    $result = $merger->merge();

    expect($result)->toContain('width="20"')
        ->and($result)->toContain('height="13"');
});

test('it strictly restrains tall images to the percentage limit on the Y-axis to protect error correction', function () {
    $tallPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAACCAYAAACZgbYnAAAAEElEQVQImWNgYGD4z8DAwAABiwEAqD4w+wAAAABJRU5ErkJggg==');

    $svg = '<svg width="100" height="100"></svg>';

    $merger = new SvgMerger($svg, $tallPng, 0.2);
    $result = $merger->merge();
    expect($result)->toContain('height="20"')
        ->and($result)->toContain('width="10"')
        ->and($result)->toContain('x="45"')
        ->and($result)->toContain('y="40"');
});

test('it strictly validates image dimensions to prevent division by zero errors', function () {
    $svg = '<svg width="100" height="100"></svg>';

    $zeroDimensionGif = hex2bin('4749463839610000000000000021f90401000000002c00000000000000000000');

    expect(fn () => (new SvgMerger($svg, $zeroDimensionGif, 0.2))->merge())
        ->toThrow(InvalidArgumentException::class, 'Invalid image dimensions for merge.');
});

it('throws an exception when the merge image has zero width or zero height', function (int $width, int $height) {
    $svgContent = '<svg width="100px" height="100px"></svg>';

    $dummyGif = 'GIF89a'.pack('v', $width).pack('v', $height)."\x00\x00\x00";

    $merger = new SvgMerger($svgContent, $dummyGif, 0.5);

    expect(fn () => $merger->merge())
        ->toThrow(InvalidArgumentException::class, 'Invalid image dimensions for merge.');
})->with([
    'zero width, valid height' => [0, 10],
    'valid width, zero height' => [10, 0],
    'zero width, zero height' => [0, 0],
]);

<?php

use Linkxtr\QrCode\Generator;
use Linkxtr\QrCode\SvgImageMerge;

it('injects an image tag into the svg', function () {
    $qr = new Generator;

    $svg = $qr->format('svg')
        ->size(300)
        ->mergeString(file_get_contents(__DIR__.'/images/linkxtr.png'), 0.2)
        ->generate('test');

    expect((string) $svg)->toContain('<image x="');
    expect((string) $svg)->toContain('href="data:image/png;base64,');
});

it('throw exception if percentage is greater than 1', function () {
    $qr = new Generator;

    $qr->format('svg')
        ->size(300)
        ->mergeString(file_get_contents(__DIR__.'/images/linkxtr.png'), 1.2)
        ->generate('test');
})->throws(InvalidArgumentException::class);

it('throw exception if percentage is less than 0', function () {
    $qr = new Generator;

    $qr->format('svg')
        ->size(300)
        ->mergeString(file_get_contents(__DIR__.'/images/linkxtr.png'), -0.2)
        ->generate('test');
})->throws(InvalidArgumentException::class);

it('throws exception if svg dimensions are missing', function () {
    $merger = new SvgImageMerge('<svg>content</svg>', 'image data', 0.2);
    $merger->merge();
})->throws(InvalidArgumentException::class, 'Could not determine SVG dimensions. Ensure the SVG has width and height attributes.');

it('throws exception if merged image data is invalid', function () {
    $merger = new SvgImageMerge('<svg width="100" height="100"></svg>', 'invalid image data', 0.2);
    $merger->merge();
})->throws(InvalidArgumentException::class, 'Invalid image data provided for merge. Could not determine image type/size.');

it('throws exception if svg closing tag is missing', function () {
    $validImage = file_get_contents(__DIR__.'/images/linkxtr.png');
    $merger = new SvgImageMerge('<svg width="100" height="100">', $validImage, 0.2);
    $merger->merge();
})->throws(InvalidArgumentException::class, 'Invalid SVG content: closing tag not found.');

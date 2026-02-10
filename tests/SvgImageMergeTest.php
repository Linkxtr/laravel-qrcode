<?php

use Linkxtr\QrCode\Generator;

test('it merges image into svg qrcode', function () {
    $svgData = (new Generator)
        ->format('svg')
        ->size(300)
        ->merge(__DIR__.'/images/linkxtr.png', 0.2, true)
        ->generate('test');

    expect((string) $svgData)->toContain('<image');
    expect((string) $svgData)->toContain('base64');
    expect((string) $svgData)->toContain('href="data:image/png;base64,');
});

test('it throws exception for unsupported merge format', function () {
    (new Generator)
        ->format('eps')
        ->merge(__DIR__.'/images/linkxtr.png', 0.2, true)
        ->generate('test');
})->throws(\InvalidArgumentException::class, 'Image merge is not supported for eps format.');

test('it throws exception for invalid percentage', function () {
    $svgContent = '<svg width="100" height="100"></svg>';
    $imageContent = 'fake_image_content';

    (new \Linkxtr\QrCode\SvgImageMerge($svgContent, $imageContent, 1.5))->merge();
})->throws(\InvalidArgumentException::class, '$percentage must be greater than 0 and less than or equal to 1');

test('it throws exception if svg dimensions cannot be determined', function () {
    $svgContent = '<svg></svg>';
    $imageContent = 'fake_image_content';

    (new \Linkxtr\QrCode\SvgImageMerge($svgContent, $imageContent, 0.2))->merge();
})->throws(\InvalidArgumentException::class, 'Could not determine SVG dimensions.');

test('it throws exception for invalid merge image data', function () {
    $svgContent = '<svg width="100" height="100"></svg>';
    $imageContent = 'not_an_image';

    (new \Linkxtr\QrCode\SvgImageMerge($svgContent, $imageContent, 0.2))->merge();
})->throws(\InvalidArgumentException::class, 'Invalid image data provided for merge.');

test('it throws exception if svg closure is missing', function () {
    $svgContent = '<svg width="100" height="100">';
    $imageContent = file_get_contents(__DIR__.'/images/linkxtr.png');

    (new \Linkxtr\QrCode\SvgImageMerge($svgContent, $imageContent, 0.2))->merge();
})->throws(\InvalidArgumentException::class, 'Invalid SVG content.');

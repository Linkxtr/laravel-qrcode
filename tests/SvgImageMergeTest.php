<?php

namespace Linkxtr\QrCode\Tests;

use Linkxtr\QrCode\Generator;
use Linkxtr\QrCode\Image;

test('it merges image into svg qrcode', function () {
    $svgData = (new Generator)
        ->format('svg')
        ->size(300)
        ->merge(__DIR__.'/images/linkxtr.png', 0.2, true)
        ->generate('test');

    expect($svgData)->toContain('<image');
    expect($svgData)->toContain('base64');
    expect($svgData)->toContain('href="data:image/png;base64,');
});

test('it throws exception for unsupported merge format', function () {
    (new Generator)
        ->format('eps')
        ->merge(__DIR__.'/images/linkxtr.png', 0.2, true)
        ->generate('test');
})->throws(\InvalidArgumentException::class, 'Image merge is not supported for eps format.');

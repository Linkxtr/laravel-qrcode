<?php

namespace Linkxtr\QrCode\Tests;

use Linkxtr\QrCode\Generator;
use Linkxtr\QrCode\Image;

test('it merges image into webp qrcode', function () {
    if (! extension_loaded('gd')) {
        $this->markTestSkipped('The GD extension is required for this test.');
    }

    $webpData = (new Generator)
        ->format('webp')
        ->size(300)
        ->merge(__DIR__.'/images/linkxtr.png', 0.2, true)
        ->generate('test');

    $image = new Image($webpData);
    expect($image->getWidth())->toBe(300);
});

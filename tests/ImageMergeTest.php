<?php

use Linkxtr\QrCode\Generator;
use Linkxtr\QrCode\Image;
use Linkxtr\QrCode\ImageMerge;

it('it merges image into webp qrcode', function () {
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

it('can merge 2 images into one and center them', function () {
    $src_image = imagecreatefrompng(__DIR__.'/images/linkxtr.png');
    $dst_image = imagecreatefrompng(__DIR__.'/images/300X200.png');

    $mergedPath = __DIR__.'/images/compareImage1.png';
    $comparedPath = __DIR__.'/images/compareImage2.png';

    imagecopyresampled(
        $dst_image,
        $src_image,
        120,
        70,
        0,
        0,
        60,
        60,
        imagesx($src_image),
        imagesy($src_image)
    );

    imagepng($dst_image, $mergedPath);

    $source = new Image(file_get_contents(__DIR__.'/images/linkxtr.png'));
    $merge = new Image(file_get_contents(__DIR__.'/images/300X200.png'));

    $test = new ImageMerge($merge, $source);
    $result2 = $test->merge(.2);

    file_put_contents($comparedPath, $result2);

    expect(file_get_contents($mergedPath))->toBe(file_get_contents($comparedPath));

    unlink(__DIR__.'/images/compareImage1.png');
    unlink(__DIR__.'/images/compareImage2.png');
});

it('throw exception if percentage is greater than 1', function () {
    $source = new Image(file_get_contents(__DIR__.'/images/linkxtr.png'));
    $merge = new Image(file_get_contents(__DIR__.'/images/300X200.png'));

    $test = new ImageMerge($merge, $source);
    $test->merge(2.1);
})->throws(InvalidArgumentException::class);

<?php

use Linkxtr\QrCode\Image;
use Linkxtr\QrCode\ImageMerge;

require_once __DIR__.'/Overrides.php';

it('throw exception if percentage is greater than 1', function () {
    $source = new Image(file_get_contents(__DIR__.'/images/linkxtr.png'));
    $merge = new Image(file_get_contents(__DIR__.'/images/300X200.png'));

    $test = new ImageMerge($merge, $source);
    $test->merge(2.1);
})->throws(InvalidArgumentException::class);

it('throw exception if percentage is less than 0', function () {
    $source = new Image(file_get_contents(__DIR__.'/images/linkxtr.png'));
    $merge = new Image(file_get_contents(__DIR__.'/images/300X200.png'));

    $test = new ImageMerge($merge, $source);
    $test->merge(-0.1);
})->throws(InvalidArgumentException::class);

it('throws exception if format is not png or webp', function () {
    $source = new Image(file_get_contents(__DIR__.'/images/linkxtr.png'));
    $merge = new Image(file_get_contents(__DIR__.'/images/300X200.png'));

    new ImageMerge($source, $merge, 'jpg');
})->throws(InvalidArgumentException::class, 'ImageMerge only supports "png" or "webp" formats.');

it('throws exception if transparent color cannot be created', function () {
    global $mockImageColorAllocateAlpha;
    $mockImageColorAllocateAlpha = false;

    $source = new Image(file_get_contents(__DIR__.'/images/linkxtr.png'));
    $merge = new Image(file_get_contents(__DIR__.'/images/300X200.png'));

    try {
        $test = new ImageMerge($merge, $source);
        $test->merge(0.2);
    } finally {
        $mockImageColorAllocateAlpha = null;
    }
})->throws(RuntimeException::class, 'Failed to create transparent color.');

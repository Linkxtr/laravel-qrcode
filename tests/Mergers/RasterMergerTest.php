<?php

use Linkxtr\QrCode\Mergers\RasterMerger;
use Linkxtr\QrCode\Support\Image;

require_once __DIR__.'/../Support/Overrides.php';

it('throw exception if percentage is greater than 1', function () {
    $source = new Image(file_get_contents(__DIR__.'/../images/linkxtr.png'));
    $merge = new Image(file_get_contents(__DIR__.'/../images/300X200.png'));

    $test = new RasterMerger($source, $merge, 'png', 2.1);
    $test->merge();
})->throws(InvalidArgumentException::class);

it('throw exception if percentage is less than 0', function () {
    $source = new Image(file_get_contents(__DIR__.'/../images/linkxtr.png'));
    $merge = new Image(file_get_contents(__DIR__.'/../images/300X200.png'));

    $test = new RasterMerger($source, $merge, 'png', -0.1);
    $test->merge();
})->throws(InvalidArgumentException::class);

it('throws exception if format is not png or webp', function () {
    $source = new Image(file_get_contents(__DIR__.'/../images/linkxtr.png'));
    $merge = new Image(file_get_contents(__DIR__.'/../images/300X200.png'));

    new RasterMerger($source, $merge, 'jpg');
})->throws(InvalidArgumentException::class, 'ImageMerge only supports "png" or "webp" formats.');

it('throws exception if transparent color cannot be created', function () {
    global $mockImageColorAllocateAlpha;
    $mockImageColorAllocateAlpha = false;

    $source = new Image(file_get_contents(__DIR__.'/../images/linkxtr.png'));
    $merge = new Image(file_get_contents(__DIR__.'/../images/300X200.png'));

    try {
        $test = new RasterMerger($source, $merge, 'png', 0.2);
        $test->merge();
    } finally {
        $mockImageColorAllocateAlpha = null;
    }
})->throws(RuntimeException::class, 'Failed to create transparent color.');

it('throws exception if image canvas cannot be created', function () {
    global $mockImageCreateTrueColor;
    $mockImageCreateTrueColor = false;

    $source = new Image(file_get_contents(__DIR__.'/../images/linkxtr.png'));
    $merge = new Image(file_get_contents(__DIR__.'/../images/300X200.png'));

    try {
        $test = new RasterMerger($source, $merge, 'png', 0.2);
        $test->merge();
    } finally {
        $mockImageCreateTrueColor = null;
    }
})->throws(RuntimeException::class, 'Failed to create image canvas.');

<?php

use Linkxtr\QrCode\Generator;
use Linkxtr\QrCode\Mergers\EpsMerger;

require_once __DIR__.'/../Support/Overrides.php';

it('throw exception if percentage is greater than 1', function () {
    $qr = new Generator;

    $qr->format('eps')
        ->size(300)
        ->mergeString(file_get_contents(__DIR__.'/../images/linkxtr.png'), 1.2)
        ->generate('test');
})->throws(InvalidArgumentException::class);

it('throw exception if percentage is less than 0', function () {
    $qr = new Generator;

    $qr->format('eps')
        ->size(300)
        ->mergeString(file_get_contents(__DIR__.'/../images/linkxtr.png'), -0.2)
        ->generate('test');
})->throws(InvalidArgumentException::class);

it('throws exception if eps dimensions are missing', function () {
    $merger = new EpsMerger('Invalid EPS content', 'image data', 0.2);
    $merger->merge();
})->throws(InvalidArgumentException::class, 'Could not determine EPS dimensions (Missing %%BoundingBox).');

it('throws exception if merge image is invalid', function () {
    $eps = "%!PS-Adobe-3.0 EPSF-3.0\n%%BoundingBox: 0 0 100 100";
    $merger = new EpsMerger($eps, 'invalid image data', 0.2);
    $merger->merge();
})->throws(InvalidArgumentException::class, 'Invalid merge image provided.');

it('throws exception if color allocation fails', function () {
    global $mockImageColorAllocate;
    $mockImageColorAllocate = false;

    $eps = "%!PS-Adobe-3.0 EPSF-3.0\n%%BoundingBox: 0 0 100 100";
    $validImage = file_get_contents(__DIR__.'/../images/linkxtr.png');

    try {
        $merger = new EpsMerger($eps, $validImage, 0.2);
        $merger->merge();
    } finally {
        $mockImageColorAllocate = null;
    }
})->throws(InvalidArgumentException::class, 'Could not allocate white color for the logo.');

it('replaces showpage with merged logo', function () {
    $eps = "%!PS-Adobe-3.0 EPSF-3.0\n%%BoundingBox: 0 0 100 100\nshowpage";
    $validImage = file_get_contents(__DIR__.'/../images/linkxtr.png');

    $merger = new EpsMerger($eps, $validImage, 0.2);
    $result = $merger->merge();

    expect($result)->toContain('% MERGED LOGO START');
    expect($result)->toContain('showpage');
    expect($result)->not->toBe($eps);
});

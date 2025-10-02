<?php

use Linkxtr\QrCode\Image;

beforeEach(function () {
    $this->imagePath = __DIR__.'/images/linkxtr.png';
    $this->image = new Image(file_get_contents($this->imagePath));
});

it('loads an image string into a resource', function () {
    $expected = imagecreatefrompng($this->imagePath);
    $actual = $this->image->getImageResource();

    $w = imagesx($expected);
    $h = imagesy($expected);
    expect(imagesx($actual))->toBe($w);
    expect(imagesy($actual))->toBe($h);

    // Sample a grid of pixels for equality
    $xStep = max(1, intdiv($w, 10));
    $yStep = max(1, intdiv($h, 10));
    for ($y = 0; $y < $h; $y += $yStep) {
        for ($x = 0; $x < $w; $x += $xStep) {
            expect(imagecolorat($actual, $x, $y))->toBe(imagecolorat($expected, $x, $y));
        }
    }
});

it('gets the width of the image', function () {
    expect($this->image->getWidth())->toBe(325);
});

it('gets the height of the image', function () {
    expect($this->image->getHeight())->toBe(326);
});

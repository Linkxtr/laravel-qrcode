<?php

use Linkxtr\QrCode\Image;

beforeEach(function () {
    $this->imagePath = __DIR__.'/images/linkxtr.png';
    $this->image = new Image(file_get_contents($this->imagePath));

    $this->testImageSaveLocation = __DIR__.'/images/testImage.png';
    $this->compareTestSaveLocation = __DIR__.'/images/compareImage.png';
});

it('loads an image string into a resource', function () {
    imagepng(imagecreatefrompng($this->imagePath), $this->compareTestSaveLocation);
    imagepng($this->image->getImageResource(), $this->testImageSaveLocation);

    $correctImage = file_get_contents($this->compareTestSaveLocation);
    $testImage = file_get_contents($this->testImageSaveLocation);

    expect($correctImage)->toBe($testImage);

    unlink($this->testImageSaveLocation);
    unlink($this->compareTestSaveLocation);
});

it('gets the width of the image', function () {
    expect($this->image->getWidth())->toBe(325);
});

it('gets the height of the image', function () {
    expect($this->image->getHeight())->toBe(326);
});

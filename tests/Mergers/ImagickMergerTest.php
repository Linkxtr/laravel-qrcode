<?php

declare(strict_types=1);

use Linkxtr\QrCode\Mergers\ImagickMerger;

beforeEach(function () {
    if (! extension_loaded('imagick')) {
        $this->markTestSkipped('The imagick extension is not available.');
    }
});

it('throw exception if percentage is greater than 1', function () {
    $source = file_get_contents(__DIR__.'/../images/linkxtr.png');
    $merge = file_get_contents(__DIR__.'/../images/300X200.png');

    new ImagickMerger((string) $source, (string) $merge, 'png', 2.1);
})->throws(InvalidArgumentException::class);

it('throw exception if percentage is less than 0', function () {
    $source = file_get_contents(__DIR__.'/../images/linkxtr.png');
    $merge = file_get_contents(__DIR__.'/../images/300X200.png');

    new ImagickMerger((string) $source, (string) $merge, 'png', -0.1);
})->throws(InvalidArgumentException::class);

it('throws exception if format is not png or webp', function () {
    $source = file_get_contents(__DIR__.'/../images/linkxtr.png');
    $merge = file_get_contents(__DIR__.'/../images/300X200.png');

    new ImagickMerger((string) $source, (string) $merge, 'jpg');
})->throws(InvalidArgumentException::class, 'ImagickMerger only supports "png" or "webp" formats.');

it('throws exception if image data is invalid', function () {
    $source = 'invalid-data';
    $merge = 'invalid-data';

    $merger = new ImagickMerger($source, $merge, 'png', 0.2);
    $merger->merge();
})->throws(RuntimeException::class, 'Imagick merge failed:');

it('merges images successfully for png', function () {
    $source = file_get_contents(__DIR__.'/../images/linkxtr.png');
    $merge = file_get_contents(__DIR__.'/../images/300X200.png');

    $merger = new ImagickMerger((string) $source, (string) $merge, 'png', 0.2);
    $output = $merger->merge();

    expect($output)->toBeString();

    // Verify it's a valid png
    $imagick = new Imagick;
    $imagick->readImageBlob($output);
    expect($imagick->getImageFormat())->toBe('PNG');
});

it('merges images successfully for webp', function () {
    $source = file_get_contents(__DIR__.'/../images/linkxtr.png');
    $merge = file_get_contents(__DIR__.'/../images/300X200.png');

    $merger = new ImagickMerger((string) $source, (string) $merge, 'webp', 0.2);
    $output = $merger->merge();

    expect($output)->toBeString();

    // Verify it's a valid webp
    $imagick = new Imagick;
    $imagick->readImageBlob($output);
    expect($imagick->getImageFormat())->toBe('WEBP');
});

it('constrains merge image if it exceeds vertical bounds', function () {
    $imagick = new Imagick;
    $imagick->newImage(10, 200, new ImagickPixel('white'));
    $imagick->setImageFormat('png');
    $tallImageData = $imagick->getImageBlob();

    $source = file_get_contents(__DIR__.'/../images/linkxtr.png');

    $merger = new ImagickMerger((string) $source, $tallImageData, 'png', 0.2);
    $output = $merger->merge();

    expect($output)->toBeString();
});

<?php

declare(strict_types=1);

use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Mergers\ImagickMerger;

covers(ImagickMerger::class);

$tinyPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');

$getTallPng = function () {
    $image = new Imagick;
    $image->newImage(10, 100, new ImagickPixel('black'));
    $image->setImageFormat('png');

    return $image->getImageBlob();
};

test('it throws exception for invalid percentages', function () use ($tinyPng) {
    expect(fn () => new ImagickMerger($tinyPng, $tinyPng, 0))
        ->toThrow(InvalidArgumentException::class, '$percentage must be between 0 and 1');

    expect(fn () => new ImagickMerger($tinyPng, $tinyPng, 1))
        ->toThrow(InvalidArgumentException::class, '$percentage must be between 0 and 1');
});

test('it throws exception for unsupported formats', function () use ($tinyPng) {
    $merger = new ImagickMerger($tinyPng, $tinyPng, 0.2);

    expect(fn () => $merger->setFormat(Format::SVG))
        ->toThrow(InvalidArgumentException::class, 'ImagickMerger only supports "png" or "webp" formats.');
});

test('it successfully merges two images as PNG', function () use ($tinyPng) {
    $merger = new ImagickMerger($tinyPng, $tinyPng, 0.2);
    $merger->setFormat(Format::PNG);
    $result = $merger->merge();

    expect(substr($result, 1, 3))->toBe('PNG');
});

test('it successfully merges and sets compression for WEBP format', function () use ($tinyPng) {
    $merger = new ImagickMerger($tinyPng, $tinyPng, 0.2);
    $merger->setFormat(Format::WEBP);

    $result = $merger->merge();

    expect($result)->toContain('WEBP');
});

test('it constrains the merge image if height exceeds canvas bounds', function () use ($tinyPng, $getTallPng) {
    $merger = new ImagickMerger($tinyPng, $getTallPng(), 0.5);

    $result = $merger->merge();

    expect(substr($result, 1, 3))->toBe('PNG');
});

test('it properly concatenates the Imagick exception message', function () {
    $merger = new ImagickMerger('not-an-image', 'not-an-image', 0.2);

    $caughtException = null;

    try {
        $merger->merge();
    } catch (RuntimeException $exception) {
        $caughtException = $exception;
    }

    expect($caughtException)->not->toBeNull();

    $message = $caughtException->getMessage();

    expect($message)->toStartWith('Imagick merge failed: ')
        ->and($message)->not->toBe('Imagick merge failed: ');
});

test('it strictly maintains aspect ratio during calculations (kills ratio mutants)', function () use ($getTallPng) {
    $image = new Imagick;
    $image->newImage(100, 100, new ImagickPixel('white'));
    $image->setImageFormat('png');
    $whiteSquareImage = $image->getImageBlob();

    $merger = new ImagickMerger($whiteSquareImage, $getTallPng(), 0.5);
    $result = $merger->merge();

    $resultImage = new Imagick;
    $resultImage->readImageBlob($result);

    $colors = $resultImage->getImagePixelColor(50, 30)->getColor();

    expect($colors['r'])->toBe(0)
        ->and($colors['g'])->toBe(0)
        ->and($colors['b'])->toBe(0);
});

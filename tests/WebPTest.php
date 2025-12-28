<?php

use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use Illuminate\Support\HtmlString;
use Linkxtr\QrCode\Generator;

test('webp format is supported', function () {
    $qrCode = (new Generator)->format('webp');
    expect($qrCode->getFormatter())->toBeInstanceOf(ImagickImageBackEnd::class);
});

test('can generate webp', function () {
    $qrCode = (new Generator)->format('webp')->generate('test');
    expect($qrCode)->toBeInstanceOf(HtmlString::class);

    $data = $qrCode->toHtml();
    // Check magic bytes for WebP (RIFF....WEBP)
    // RIFF is bytes 0-3, WEBP is bytes 8-11
    expect(substr($data, 0, 4))->toBe('RIFF');
    expect(substr($data, 8, 4))->toBe('WEBP');
});

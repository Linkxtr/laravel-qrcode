<?php

namespace Tests\GdBackendCoverage;

use Illuminate\Support\HtmlString;
use Linkxtr\QrCode\Generator;
use Linkxtr\QrCode\Renderer\Image\GdImageBackEnd;

require_once __DIR__.'/Overrides.php';

test('GD backend full coverage', function () {
    if (! extension_loaded('gd')) {
        $this->markTestSkipped('The GD extension is required for this test.');
    }

    global $mockImagickLoaded;
    $mockImagickLoaded = false;

    try {
        // Standard generation (PNG)
        $generator = new Generator;
        $generator->format('png');
        expect($generator->getFormatter())->toBeInstanceOf(GdImageBackEnd::class);
        $result = $generator->generate('test content');
        expect($result)->toBeInstanceOf(HtmlString::class);

        // Standard generation (WebP)
        $generator->format('webp');
        expect($generator->getFormatter())->toBeInstanceOf(GdImageBackEnd::class);
        $generator->generate('test content');

        // Gradient generation (covers drawPathWithGradient)
        $generator->format('png')
            ->gradient(0, 0, 0, 255, 255, 255, 'vertical')
            ->generate('gradient test');

        // Alpha transparency (covers background color alpha logic)
        $generator->format('png')
            ->backgroundColor(255, 255, 255, 50)
            ->generate('alpha test');

    } finally {
        $mockImagickLoaded = true;
    }
});

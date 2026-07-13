<?php

declare(strict_types=1);

use Linkxtr\QrCode\Exceptions\GenerationException;

covers(GenerationException::class);

test('invalidSvgString creates exception with correct error code and helper message', function (): void {
    $generationException = GenerationException::invalidSvgString();

    expect($generationException)
        ->toBeInstanceOf(GenerationException::class)
        ->and($generationException->getMessage())
        ->toBe('Generated QR code SVG is corrupted or invalid.')
        ->and($generationException->getErrorCode())
        ->toBe('INVALID_SVG_STRING')
        ->and($generationException->getHelperMessage())
        ->toBe('Invalid SVG string provided.');
});

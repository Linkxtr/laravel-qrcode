<?php

declare(strict_types=1);

use Linkxtr\QrCode\Exceptions\MissingExtensionException;

covers(MissingExtensionException::class);

test('imagickRequired sets correct error code and message', function (): void {
    $missingExtensionException = MissingExtensionException::imagickRequired('for a reason');

    expect($missingExtensionException)->toBeInstanceOf(MissingExtensionException::class)
        ->and($missingExtensionException->getErrorCode())->toBe('MISSING_IMAGICK')
        ->and($missingExtensionException->getMessage())->toBe('The Imagick extension is required for a reason.');
});

test('gdRequired sets correct error code and message', function (): void {
    $missingExtensionException = MissingExtensionException::gdRequired('for a reason');

    expect($missingExtensionException)->toBeInstanceOf(MissingExtensionException::class)
        ->and($missingExtensionException->getErrorCode())->toBe('MISSING_GD')
        ->and($missingExtensionException->getMessage())->toBe('The GD extension is required for a reason.');
});

test('neitherImagickNorGdAvailable sets correct error code and message', function (): void {
    $missingExtensionException = MissingExtensionException::neitherImagickNorGdAvailable();

    expect($missingExtensionException)->toBeInstanceOf(MissingExtensionException::class)
        ->and($missingExtensionException->getErrorCode())->toBe('MISSING_IMAGICK_AND_GD')
        ->and($missingExtensionException->getMessage())->toBe('The imagick or gd extension is required to generate raster QR codes.');
});

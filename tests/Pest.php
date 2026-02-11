<?php

declare(strict_types=1);

use chillerlan\QRCode\QRCode as QRCodeDecoder;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific test case class.
|
*/

uses(TestCase::class)->in(__DIR__);

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to make them available in all your test files.
|
*/

function read_qr_code(string $imageContent): string
{
    $isSvg = str_contains($imageContent, '<svg') || str_contains($imageContent, '<?xml');
    $isEps = str_starts_with($imageContent, '%!PS-Adobe');
    if (! $isSvg && ! $isEps) {
        return (string) (new QRCodeDecoder)->readFromBlob($imageContent);
    }

    try {
        $imagick = new Imagick;

        if ($isEps) {
            $imagick->setResolution(144, 144);
        }

        $imagick->setBackgroundColor(new ImagickPixel('white'));
        $imagick->readImageBlob($imageContent);
        $imagick->setImageFormat('png');
        $imageContent = $imagick->getImageBlob();
    } catch (Throwable $e) {
        $format = $isSvg ? 'SVG' : 'EPS';

        return "ERROR: Could not rasterize {$format}. ".$e->getMessage();
    }

    return (string) (new QRCodeDecoder)->readFromBlob($imageContent);
}

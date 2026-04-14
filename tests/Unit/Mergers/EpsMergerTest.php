<?php

declare(strict_types=1);

use Linkxtr\QrCode\Mergers\EpsMerger;

require_once __DIR__.'/../../Support/Overrides.php';

covers(EpsMerger::class);

$tinyPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
$epsBase = "%!PS-Adobe-3.0 EPSF-3.0\n%%BoundingBox: 0 0 100 100\nshowpage";

test('it throws exception for invalid percentages', function () use ($tinyPng, $epsBase) {
    expect(fn () => new EpsMerger($epsBase, $tinyPng, 0))
        ->toThrow(InvalidArgumentException::class, '$percentage must be between 0 and 1');

    expect(fn () => new EpsMerger($epsBase, $tinyPng, 1))
        ->toThrow(InvalidArgumentException::class, '$percentage must be between 0 and 1');
});

test('it throws exception if eps is missing bounding box', function () use ($tinyPng) {
    $epsInvalid = "%!PS-Adobe-3.0 EPSF-3.0\n%%Creator: Test\nshowpage";

    expect(fn () => (new EpsMerger($epsInvalid, $tinyPng, 0.2))->merge())
        ->toThrow(InvalidArgumentException::class, 'Could not determine EPS dimensions');
});

test('it properly propagates InvalidArgumentException for invalid raster image data', function () use ($epsBase) {
    expect(fn () => (new EpsMerger($epsBase, 'not-an-image', 0.2))->merge())
        ->toThrow(InvalidArgumentException::class, 'Invalid merge image provided.');
});

test('it successfully merges an image into an eps with a showpage tag', function () use ($tinyPng, $epsBase) {
    $result = (new EpsMerger($epsBase, $tinyPng, 0.2))->merge();

    expect($result)->toContain('% MERGED LOGO START')
        ->and($result)->toContain('colorimage')
        ->and($result)->toContain('showpage')
        ->and(strpos($result, '% MERGED LOGO END'))->toBeLessThan(strpos($result, 'showpage'));
});

test('it successfully appends to eps if showpage tag is missing', function () use ($tinyPng) {
    $epsNoShowpage = "%!PS-Adobe-3.0 EPSF-3.0\n%%BoundingBox: 0 0 100 100";
    $result = (new EpsMerger($epsNoShowpage, $tinyPng, 0.2))->merge();

    expect($result)->toStartWith($epsNoShowpage)
        ->and($result)->toContain($epsNoShowpage."\n% MERGED LOGO START")
        ->and($result)->not->toContain('showpage')
        ->and($result)->toEndWith('% MERGED LOGO END');
});

test('it accurately renders hex data from pixels', function () use ($epsBase) {
    $logo = imagecreatetruecolor(10, 10);
    $red = imagecolorallocate($logo, 255, 0, 0);
    imagefill($logo, 0, 0, $red);
    ob_start();
    imagepng($logo);
    $redPng = ob_get_clean();
    unset($logo);

    $result = (new EpsMerger($epsBase, $redPng, 0.2))->merge();

    expect($result)->toContain('ff0000');
});

it('strictly maintains aspect ratio without triggering vertical constraints', function () use ($epsBase) {
    $logo = imagecreatetruecolor(100, 10);
    $black = imagecolorallocate($logo, 0, 0, 0);
    imagefill($logo, 0, 0, $black);
    ob_start();
    imagepng($logo);
    $widePng = ob_get_clean();
    unset($logo);

    $result20 = (new EpsMerger($epsBase, $widePng, 0.2))->merge();
    $result80 = (new EpsMerger($epsBase, $widePng, 0.8))->merge();

    expect($result20)->not->toBe($result80);
});

it('constrains merge image if it exceeds vertical bounds', function () use ($epsBase) {
    $logo = imagecreatetruecolor(10, 100);
    $black = imagecolorallocate($logo, 0, 0, 0);
    imagefill($logo, 0, 0, $black);
    ob_start();
    imagepng($logo);
    $tallPng = ob_get_clean();
    unset($logo);

    $merger = new EpsMerger($epsBase, $tallPng, 0.2);
    $output = $merger->merge();

    expect($output)->toBeString()->toContain('colorimage')
        ->and($output)->toContain('2 20 scale')
        ->and($output)->not->toContain('200 scale');
});

it('throws exception if image canvas cannot be created', function () use ($tinyPng, $epsBase) {
    global $mockImageCreateTrueColor;
    $mockImageCreateTrueColor = false;

    expect(fn () => (new EpsMerger($epsBase, $tinyPng, 0.2))->merge())
        ->toThrow(RuntimeException::class, 'Failed to create resized logo canvas.');
})->after(function () {
    global $mockImageCreateTrueColor;
    $mockImageCreateTrueColor = null;
});

it('throws exception if white color cannot be allocated', function () use ($tinyPng, $epsBase) {
    global $mockImageColorAllocate;
    $mockImageColorAllocate = false;

    expect(fn () => (new EpsMerger($epsBase, $tinyPng, 0.2))->merge())
        ->toThrow(RuntimeException::class, 'Could not allocate white color for the logo.');
})->after(function () {
    global $mockImageColorAllocate;
    $mockImageColorAllocate = null;
});

it('throws exception if output buffer capture fails', function () use ($tinyPng, $epsBase) {
    global $mockObGetClean;
    $mockObGetClean = false;

    expect(fn () => (new EpsMerger($epsBase, $tinyPng, 0.2))->merge())
        ->toThrow(RuntimeException::class, 'Failed to capture hex data from output buffer.');
})->after(function () {
    global $mockObGetClean;
    $mockObGetClean = null;
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
});

test('it strictly loops and calculates accurate bitwise hex data for every pixel', function () {
    $tinyEps = "%!PS-Adobe-3.0 EPSF-3.0\n%%BoundingBox: 0 0 10 10\nshowpage";

    $logo = \imagecreatetruecolor(2, 2);
    $color = \imagecolorallocate($logo, 171, 187, 205);
    \imagefill($logo, 0, 0, $color);
    \ob_start();
    \imagepng($logo);
    $customPng = \ob_get_clean();
    unset($logo);

    $result = (new EpsMerger($tinyEps, $customPng, 0.2))->merge();

    expect($result)->toContain("colorimage\nabbbcdabbbcdabbbcdabbbcd\ngrestore");
});

test('it strictly fills the background with white to preserve transparent logos', function () {
    $tinyEps = "%!PS-Adobe-3.0 EPSF-3.0\n%%BoundingBox: 0 0 10 10\nshowpage";

    $logo = \imagecreatetruecolor(2, 2);
    \imagealphablending($logo, false);
    \imagesavealpha($logo, true);
    $transparent = \imagecolorallocatealpha($logo, 0, 0, 0, 127);
    \imagefill($logo, 0, 0, $transparent);
    \ob_start();
    \imagepng($logo);
    $transparentPng = \ob_get_clean();
    unset($logo);

    $result = (new EpsMerger($tinyEps, $transparentPng, 0.2))->merge();

    expect($result)->toContain("colorimage\nffffffffffffffffffffffff\ngrestore");
});

it('strictly calculates qr dimensions from shifted bounding boxes to kill math mutants', function () {
    $epsShifted = "%!PS-Adobe-3.0 EPSF-3.0\n%%BoundingBox: 10 20 110 120\nshowpage";

    $logo = imagecreatetruecolor(10, 10);
    $black = imagecolorallocate($logo, 0, 0, 0);
    imagefill($logo, 0, 0, $black);
    ob_start();
    imagepng($logo);
    $squarePng = ob_get_clean();
    unset($logo);

    $result = (new EpsMerger($epsShifted, $squarePng, 0.2))->merge();

    expect($result)->toContain('50 60 translate');
});

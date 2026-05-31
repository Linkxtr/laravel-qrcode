<?php

declare(strict_types=1);

use BaconQrCode\Renderer\Image\EpsImageBackEnd;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Support\Bacon\BackendFactory;

covers(BackendFactory::class);

it('returns eps backend for eps format', function (): void {
    $imageBackEnd = BackendFactory::make(Format::EPS);

    expect($imageBackEnd)->toBeInstanceOf(EpsImageBackEnd::class);
});

it('returns svg backend for svg format', function (): void {
    $imageBackEnd = BackendFactory::make(Format::SVG);

    expect($imageBackEnd)->toBeInstanceOf(SvgImageBackEnd::class);
});

it('returns imagick backend for png format when imagick is loaded', function (): void {
    $imageBackEnd = BackendFactory::make(Format::PNG);

    expect($imageBackEnd)->toBeInstanceOf(ImagickImageBackEnd::class);
});

it('returns imagick backend for webp format when imagick is loaded', function (): void {
    $imageBackEnd = BackendFactory::make(Format::WEBP);

    expect($imageBackEnd)->toBeInstanceOf(ImagickImageBackEnd::class);
});

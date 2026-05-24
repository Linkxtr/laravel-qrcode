<?php

declare(strict_types=1);

use Illuminate\Http\Response;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Support\QrCodeResult;

it('can be cast to a string', function (): void {
    $result = new QrCodeResult('raw-binary-data', Format::PNG);

    expect((string) $result)->toBe('raw-binary-data');
});

it('can identify if it is an svg', function (): void {
    $svg = new QrCodeResult('<svg></svg>', Format::SVG);
    $png = new QrCodeResult('binary-data', Format::PNG);

    expect($svg->isSvg())->toBeTrue()
        ->and($png->isSvg())->toBeFalse();
});

it('can generate a data uri for svg', function (): void {
    $content = '<svg></svg>';
    $result = new QrCodeResult($content, Format::SVG);
    $base64 = base64_encode($content);

    expect($result->toDataUri())->toBe('data:image/svg+xml;base64,'.$base64);
});

it('can generate a data uri for binary formats', function (): void {
    $content = 'binary-data';
    $result = new QrCodeResult($content, Format::PNG);
    $base64 = base64_encode($content);

    expect($result->toDataUri())->toBe('data:image/png;base64,'.$base64);
});

it('returns raw content for toHtml when format is svg', function (): void {
    $result = new QrCodeResult('<svg></svg>', Format::SVG);

    expect($result->toHtml())->toBe('<svg></svg>');
});

it('wraps binary formats in an img tag for toHtml', function (): void {
    $content = 'binary-data';
    $result = new QrCodeResult($content, Format::PNG);
    $base64 = base64_encode($content);

    $expectedHtml = sprintf('<img src="data:image/png;base64,%s" alt="QR Code" />', $base64);

    expect($result->toHtml())->toBe($expectedHtml);
});

it('can convert to a laravel http response', function (): void {
    $result = new QrCodeResult('binary-data', Format::PNG);

    // Using Laravel's request() helper as a mock argument
    $response = $result->toResponse(request());

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())->toBe(200)
        ->and($response->getContent())->toBe('binary-data')
        ->and($response->headers->get('Content-Type'))->toBe('image/png');
});

it('sets the correct content type for svg responses', function (): void {
    $result = new QrCodeResult('<svg></svg>', Format::SVG);

    $response = $result->toResponse(request());

    expect($response->headers->get('Content-Type'))->toBe('image/svg+xml');
});

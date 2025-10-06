<?php

use Illuminate\Support\Arr;
use Linkxtr\QrCode\QrCode as LinkxtrQr;
use SimpleSoftwareIO\QrCode\Generator as SimpleQr;

pest()->group('compatibility');

test('can generate QR code with same output as Simple QrCode', function (string $content) {
    $linkxtr = (new LinkxtrQr)->format('svg')->generate($content);
    $simple = (new SimpleQr)->format('svg')->generate($content);

    expect($linkxtr->toHtml())->toBe($simple->toHtml());
})->with('content');

test('supports same methods as Simple QrCode', function (string $method, mixed $param) {
    $qrCode = (new LinkxtrQr)->{$method}(...Arr::wrap($param));
    expect($qrCode)->toBeInstanceOf(LinkxtrQr::class);
})->with('supported_methods');

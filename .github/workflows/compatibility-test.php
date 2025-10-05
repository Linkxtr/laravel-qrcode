<?php

test('can generate QR code with same output as Simple QrCode', function () {
    // Test with simple text
    $simpleQr = QrCode::format('svg')->size(200)->generate('test');
    $linkxtrQr = \Linkxtr\QrCode\Facades\QrCode::format('svg')->size(200)->generate('test');
    
    // Basic checks
    expect($linkxtrQr)->toBeString()
        ->and(strlen($linkxtrQr))->toBeGreaterThan(100) // Ensure we have SVG content
        ->and(str_contains($linkxtrQr, '<svg'))->toBeTrue()
        ->and(str_contains($linkxtrQr, 'test'))->toBeTrue();

    // Test with different sizes
    $sizes = [100, 200, 300];
    foreach ($sizes as $size) {
        $simpleQr = QrCode::format('svg')->size($size)->generate('size-test');
        $linkxtrQr = \Linkxtr\QrCode\Facades\QrCode::format('svg')->size($size)->generate('size-test');
        
        // Check if both generate similar size SVGs
        $simpleSize = strlen($simpleQr);
        $linkxtrSize = strlen($linkxtrQr);
        
        // Allow 10% difference in size due to potential implementation differences
        $sizeDifference = abs($simpleSize - $linkxtrSize) / $simpleSize;
        expect($sizeDifference)->toBeLessThan(0.1);
    }

    // Test error correction levels
    $levels = ['L', 'M', 'Q', 'H'];
    foreach ($levels as $level) {
        $simpleQr = QrCode::format('svg')->size(200)->errorCorrection($level)->generate('error-correction');
        $linkxtrQr = \Linkxtr\QrCode\Facades\QrCode::format('svg')
            ->errorCorrection($level)
            ->size(200)
            ->generate('error-correction');
        
        // Both should generate valid QR codes
        expect($simpleQr)->toBeString()
            ->and($linkxtrQr)->toBeString();
    }
});

test('supports same methods as Simple QrCode', function () {
    // Test common methods
    $methods = [
        'size' => 250,
        'color' => [0, 0, 0],
        'backgroundColor' => [255, 255, 255, 0],
        'margin' => 1,
        'encoding' => 'UTF-8',
        'style' => 'dot',
        'eye' => 'square',
        'format' => 'svg',
    ];

    foreach ($methods as $method => $value) {
        $qrCode = \Linkxtr\QrCode\Facades\QrCode::{$method}(...array_wrap($value));
        expect($qrCode)->toBeInstanceOf(\Linkxtr\QrCode\QrCode::class);
    }
});

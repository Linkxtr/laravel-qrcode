<?php

use Linkxtr\QrCode\SvgImageMerge;

beforeEach(function () {
    // 1x1 Transparent PNG
    $this->pngData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
});

it('parses dimensions from integer attributes', function () {
    $svg = '<svg width="200" height="200"></svg>';
    $merger = new SvgImageMerge($svg, $this->pngData, 0.5);
    $result = $merger->merge();
    
    expect($result)->toContain('width="100"');
    expect($result)->toContain('height="100"');
});

it('parses dimensions from attributes with units', function () {
    $svg = '<svg width="200px" height="200px"></svg>';
    $merger = new SvgImageMerge($svg, $this->pngData, 0.5);
    $result = $merger->merge();
    
    expect($result)->toContain('width="100"');
});

it('parses dimensions from float attributes', function () {
    $svg = '<svg width="200.5" height="200.9"></svg>';
    $merger = new SvgImageMerge($svg, $this->pngData, 0.5);
    $result = $merger->merge();
    
    expect($result)->toContain('width="100"');
});

it('parses dimensions from viewBox if attributes missing', function () {
    $svg = '<svg viewBox="0 0 400 400"></svg>';
    $merger = new SvgImageMerge($svg, $this->pngData, 0.5);
    $result = $merger->merge();
    
    // 400 * 0.5 = 200
    expect($result)->toContain('width="200"');
});

it('parses dimensions from style attribute', function () {
    $svg = '<svg style="width: 300px; height: 300px;"></svg>';
    $merger = new SvgImageMerge($svg, $this->pngData, 0.5);
    $result = $merger->merge();
    
    // 300 * 0.5 = 150
    expect($result)->toContain('width="150"');
});

it('throws exception if dimensions cannot be determined', function () {
    $svg = '<svg></svg>';
    $merger = new SvgImageMerge($svg, $this->pngData, 0.5);
    $merger->merge();
})->throws(InvalidArgumentException::class, 'Could not determine SVG dimensions.');

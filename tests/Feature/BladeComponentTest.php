<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\View\ViewException;
use Linkxtr\QrCode\Components\QrCodeComponent;

it('renders a default qr code component', function () {
    $blade_string = '<x-qr-code data="https://example.com" />';

    $rendered = Blade::render($blade_string);

    expect($rendered)->toContain('<svg', 'xmlns="http://www.w3.org/2000/svg"');
});

it('renders a qr code component with custom size', function () {
    $blade_string = '<x-qr-code data="https://example.com" size="300" />';

    $rendered = Blade::render($blade_string);

    expect($rendered)->toContain('<svg', 'width="300"');
});

it('throws an exception when rendering eps format via component', function () {
    $blade_string = '<x-qr-code data="https://example.com" format="eps" />';

    Blade::render($blade_string);
})->throws(ViewException::class, 'EPS format is not supported for HTML embedding in the Blade component.');

it('throws an exception when rendering invalid format via component', function () {
    $blade_string = '<x-qr-code data="https://example.com" format="invalid" />';

    Blade::render($blade_string);
})->throws(ViewException::class, 'Invalid format.');

it('renders a qr code component with rgb color', function () {
    $blade_string = '<x-qr-code data="https://example.com" color="255,0,0" />';

    $rendered = Blade::render($blade_string);

    expect($rendered)->toContain('fill="#ff0000"');
});

it('renders a qr code component with hex color', function () {
    $blade_string = '<x-qr-code data="https://example.com" color="#ff0000" />';

    $rendered = Blade::render($blade_string);

    expect($rendered)->toContain('fill="#ff0000"');
});

it('renders a qr code component with shorthand hex color', function () {
    $blade_string = '<x-qr-code data="https://example.com" color="#f00" />';

    $rendered = Blade::render($blade_string);

    expect($rendered)->toContain('fill="#ff0000"');
});

it('renders a qr code component with background rgb color', function () {
    $blade_string = '<x-qr-code data="https://example.com" background-color="0,0,255" />';

    $rendered = Blade::render($blade_string);

    expect($rendered)->toContain('fill="#0000ff"');
});

it('renders a qr code component with hex background color', function () {
    $blade_string = '<x-qr-code data="https://example.com" background-color="#0000FF" />';

    $rendered = Blade::render($blade_string);

    expect($rendered)->toContain('fill="#0000ff"');
});

it('renders a qr code component with style, margin, error correction, and encoding', function () {
    $blade_string = '<x-qr-code data="https://example.com" style="round" margin="2" error-correction="h" encoding="UTF-8" />';

    $rendered = Blade::render($blade_string);

    expect($rendered)->toContain('<svg');
});

it('ignores invalid rgb color string', function () {
    $blade_string = '<x-qr-code data="https://example.com" color="255,0" />';
    $rendered = Blade::render($blade_string);
    expect($rendered)->toContain('<svg');
});

it('ignores completely invalid color string', function () {
    $blade_string = '<x-qr-code data="https://example.com" color="not-a-color" />';
    $rendered = Blade::render($blade_string);
    expect($rendered)->toContain('<svg')->and($rendered)->toContain('fill="#000000"'); // Verify default black color is used;
});

it('ignores invalid hex color strings', function () {
    $blade_string = '<x-qr-code data="https://example.com" color="#gg0000" />';
    $rendered = Blade::render($blade_string);
    expect($rendered)->toContain('<svg')->and($rendered)->toContain('fill="#000000"');
});

it('renders a qr code component with eye style', function () {
    $blade_string = '<x-qr-code data="https://example.com" eye="circle" />';
    $rendered = Blade::render($blade_string);
    // Verifying it renders without crashing
    expect($rendered)->toContain('<svg');
});

it('renders a qr code component with eye colors', function () {
    $blade_string = '<x-qr-code data="https://example.com" eye-color0="#ff0000, #00ff00" eye-color1="#0000ff|#ffffff" eye-color2="#000000|#ff0000" />';
    $rendered = Blade::render($blade_string);
    expect($rendered)->toContain('<svg');
});

it('ignores eye color definitions with insufficient colors', function () {
    $blade_string = '<x-qr-code data="https://example.com" eye-color1="#ff0000" eye-color2="#00ff00" gradient="#ff0000" />';
    $rendered = Blade::render($blade_string);
    expect($rendered)->toContain('<svg');
});

it('preserves spaces in RGB tuples for multi-colors', function () {
    $blade_string = '<x-qr-code data="https://example.com" eye-color0="255, 0, 0 | 0, 255, 0" />';
    $rendered = Blade::render($blade_string);
    expect($rendered)->toContain('<svg');
});

it('renders a qr code component with gradient', function () {
    $blade_string = '<x-qr-code data="https://example.com" gradient="#ff0000, #0000ff" gradient-type="vertical" />';
    $rendered = Blade::render($blade_string);
    expect($rendered)->toContain('<svg');
});

it('renders a qr code component with default gradient type', function () {
    $blade_string = '<x-qr-code data="https://example.com" gradient="#ff0000, #0000ff" />';
    $rendered = Blade::render($blade_string);
    expect($rendered)->toContain('<svg');
});

it('renders a qr code component with merge', function () {
    $blade_string = '<x-qr-code data="https://example.com" format="png" merge="images/linkxtr.png" merge-percentage=".3" />';
    $rendered = Blade::render($blade_string);
    expect($rendered)->toStartWith('<img src="data:image/png;base64,');
});

it('renders a qr code component with absolute merge path', function () {
    $absolutePath = realpath(__DIR__.'/../images/linkxtr.png');
    expect($absolutePath)->not->toBeFalse('Test image file not found: '.__DIR__.'/../images/linkxtr.png');
    $blade_string = '<x-qr-code data="https://example.com" format="png" merge="'.$absolutePath.'" merge-percentage=".3" merge-absolute="true" />';
    $rendered = Blade::render($blade_string);
    expect($rendered)->toStartWith('<img src="data:image/png;base64,');
});

it('renders a qr code component with merge string', function () {
    // The previous test failed because BaconQrCode relies on getimagesizefromstring() which ONLY supports bitmap/raster formats (PNG/JPEG/etc), even when generating an SVG.
    // We pass a raw valid PNG string here.
    $imgData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');

    // We can't render raw binary inside Blade templates easily without corruption, so let's use the Component class directly to verify.
    $component = new QrCodeComponent('https://example.com', 100, 'svg', mergeString: $imgData, mergePercentage: .3);
    $rendered = $component->render()->toHtml();

    expect($rendered)->toContain('<svg', 'xmlns="http://www.w3.org/2000/svg"');
});

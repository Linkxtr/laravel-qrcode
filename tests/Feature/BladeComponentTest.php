<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

test('the blade component renders a real SVG directly from a view', function () {
    $html = Blade::render(
        '<x-qr-code data="https://linkxtr.com" size="150" color="255,0,0" style="round" />'
    );

    expect($html)->toContain('<svg')
        ->toContain('width="150"')
        ->toContain('height="150"')
        ->toContain('#ff0000');
});

test('the blade component handles base64 image formats correctly', function () {
    $html = Blade::render(
        '<x-qr-code data="test" format="png" class="my-qr-class" />'
    );

    expect($html)->toContain('<img')
        ->toContain('class="my-qr-class"')
        ->toContain('src="data:image/png;base64,');
});

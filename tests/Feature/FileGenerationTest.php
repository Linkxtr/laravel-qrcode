<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Linkxtr\QrCode\Facades\QrCode;

beforeEach(function () {
    $this->tempDir = __DIR__.'/temp';
    if (! File::exists($this->tempDir)) {
        File::makeDirectory($this->tempDir);
    }
});

afterEach(function () {
    File::deleteDirectory($this->tempDir);
});

test('it can generate and save a real SVG file to the filesystem', function () {
    $filePath = $this->tempDir.'/test.svg';

    QrCode::size(100)->generate('https://laravel.com', $filePath);

    expect(File::exists($filePath))->toBeTrue();

    $content = File::get($filePath);
    expect($content)->toContain('<svg')
        ->toContain('xmlns="http://www.w3.org/2000/svg"');
});

test('it can generate a real PNG binary string', function () {
    $pngData = (string) QrCode::format('png')->size(100)->generate('Testing PNG');

    expect(substr($pngData, 0, 4))->toBe("\x89PNG");
});

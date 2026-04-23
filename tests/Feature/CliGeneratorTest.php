<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->tempDir = __DIR__.'/temp_cli';
    if (! File::exists($this->tempDir)) {
        File::makeDirectory($this->tempDir);
    }
});

afterEach(function () {
    File::deleteDirectory($this->tempDir);
});

test('the artisan command generates a real file on the disk', function () {
    $outputPath = $this->tempDir.'/cli-test.svg';

    $this->artisan('qr:generate', [
        'data' => 'CLI Test Data',
        '--output' => $outputPath,
        '--format' => 'svg',
        '--size' => '200',
        '--color' => '0,0,255',
    ])->assertSuccessful();

    expect(File::exists($outputPath))->toBeTrue();

    $content = File::get($outputPath);
    expect($content)->toContain('width="200"')
        ->toContain('#0000ff');
});

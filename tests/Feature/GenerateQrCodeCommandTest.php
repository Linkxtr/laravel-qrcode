<?php

declare(strict_types=1);

namespace Tests\Feature;

afterEach(function () {
    if (file_exists(public_path('test-qr.svg'))) {
        unlink(public_path('test-qr.svg'));
    }
});

it('generates a qr code in non-interactive mode and outputs to console', function () {
    $this->artisan('qr:generate', ['data' => 'https://example.com'])
        ->expectsOutputToContain('<svg')
        ->assertSuccessful();
});

it('generates a qr code in non-interactive mode and saves to file', function () {
    $path = public_path('test-qr.svg');

    $this->artisan('qr:generate', [
        'data' => 'https://example.com',
        '--output' => $path,
    ])
        ->expectsOutputToContain('✨ QR Code successfully generated and saved to: '.$path)
        ->assertSuccessful();

    expect(file_exists($path))->toBeTrue();
    $content = file_get_contents($path);
    expect($content)->toContain('<svg');
});

it('prompts for data in interactive mode and outputs to console', function () {
    $this->artisan('qr:generate')
        ->expectsQuestion('What data/payload should be encoded in the QR code?', 'https://example.com')
        ->expectsQuestion('Where should the QR code be saved?', '')
        ->expectsConfirmation('Do you want to configure advanced options (size, colors, margin)?', 'no')
        ->expectsOutputToContain('<svg')
        ->assertSuccessful();
});

it('prompts for formatting and advanced options in interactive mode when output is provided', function () {
    $path = public_path('test-qr.svg');

    $this->artisan('qr:generate')
        ->expectsQuestion('What data/payload should be encoded in the QR code?', 'https://example.com')
        ->expectsQuestion('Where should the QR code be saved?', $path)
        ->expectsQuestion('What output format do you want?', 'svg')
        ->expectsConfirmation('Do you want to configure advanced options (size, colors, margin)?', 'yes')
        ->expectsQuestion('Size in pixels', '300')
        ->expectsQuestion('Foreground color (RGB or RGBA comma-separated)', '255,0,0')
        ->expectsQuestion('Background color (RGB or RGBA comma-separated)', '0,0,255')
        ->expectsQuestion('Margin', '2')
        ->expectsQuestion('Error correction level', 'H')
        ->expectsOutputToContain('✨ QR Code successfully generated and saved to: '.$path)
        ->assertSuccessful();

    expect(file_exists($path))->toBeTrue();
});

it('fails when an invalid error correction level is provided via options', function () {
    $this->artisan('qr:generate', [
        'data' => 'https://example.com',
        '--errorCorrection' => 'Z',
    ])
        ->expectsOutputToContain('Invalid error correction level.')
        ->assertFailed();
});

it('fails when an invalid foreground color is provided', function () {
    $this->artisan('qr:generate', [
        'data' => 'https://example.com',
        '--color' => 'not-a-color',
    ])
        ->expectsOutputToContain('Invalid format.')
        ->assertFailed();
});

it('fails when rgb values are out of bounds', function () {
    $this->artisan('qr:generate', [
        'data' => 'https://example.com',
        '--backgroundColor' => '256,0,0',
    ])
        ->expectsOutputToContain('RGB values must be between 0 and 255.')
        ->assertFailed();
});

it('fails when alpha value is out of bounds', function () {
    $this->artisan('qr:generate', [
        'data' => 'https://example.com',
        '--color' => '255,0,0,105',
    ])
        ->expectsOutputToContain('Alpha value must be between 0 and 100.')
        ->assertFailed();
});

it('handles generator exceptions gracefully', function () {
    // Providing a directory path as the output file to force a write exception
    $path = public_path();

    $this->artisan('qr:generate', [
        'data' => 'https://example.com',
        '--output' => $path,
    ])
        ->expectsOutputToContain('Failed to generate QR Code:')
        ->assertFailed();
});

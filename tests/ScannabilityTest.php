<?php

use Linkxtr\QrCode\Facades\QrCode;

require_once __DIR__.'/Support/Overrides.php';

beforeEach(function () {
    QrCode::setFacadeApplication(app());
    global $mockImagickLoaded, $mockGdLoaded;
    $mockImagickLoaded = extension_loaded('imagick');
    $mockGdLoaded = extension_loaded('gd');

    // Reset Override mocks to ensure clean state
    $GLOBALS['mockImageColorAllocate'] = null;
    $GLOBALS['mockImageCreateTrueColor'] = null;
    $GLOBALS['mockImageColorAllocateAlpha'] = null;
});

$drivers = [];

if (extension_loaded('imagick')) {
    $drivers['imagick_enabled'] = [true, false];
}

if (extension_loaded('gd')) {
    $drivers['gd_enabled'] = [false, true];
}

if (extension_loaded('imagick') && extension_loaded('gd')) {
    $drivers['both_enabled'] = [true, true];
}

dataset('drivers', $drivers);

function setDriver(bool $imagick, bool $gd)
{
    global $mockImagickLoaded, $mockGdLoaded;
    $mockImagickLoaded = $imagick;
    $mockGdLoaded = $gd;
}

it('can scan a generated QR code with simple text', function ($imagick, $gd) {
    setDriver($imagick, $gd);
    $qrCode = QrCode::format('png')->generate('Hello World');

    expect(read_qr_code($qrCode))->toBe('Hello World');
})->with('drivers');

it('can scan a generated QR code with url', function ($imagick, $gd) {
    setDriver($imagick, $gd);
    $qrCode = QrCode::format('png')->generate('https://example.com/path?query=param&another=value');

    expect(read_qr_code($qrCode))->toBe('https://example.com/path?query=param&another=value');
})->with('drivers');

it('can scan a generated QR code with email', function ($imagick, $gd) {
    setDriver($imagick, $gd);
    $qrCode = QrCode::format('png')->Email('mail@example.tld', 'Subject', 'Body');

    expect(read_qr_code($qrCode))->toBe('mailto:mail@example.tld?subject=Subject&body=Body');
})->with('drivers');

it('can scan a generated png QR code with merged image', function ($imagick, $gd) {
    setDriver($imagick, $gd);
    $qrCode = QrCode::format('png')->merge(__DIR__.'/images/linkxtr.png', .2, true)->generate('https://example.com/merged');

    expect(read_qr_code($qrCode))->toBe('https://example.com/merged');
})->with('drivers');

it('can scan a generated svg QR code with merged image', function ($imagick, $gd) {
    if (! $imagick) {
        $this->markTestSkipped('svg convert to png to scan requires imagick.');
    }

    setDriver($imagick, $gd);
    $qrCode = QrCode::format('svg')->merge(__DIR__.'/images/linkxtr.png', .2, true)->generate('https://example.com/merged');

    expect(read_qr_code($qrCode))->toBe('https://example.com/merged');
})->with('drivers');

it('can scan a generated webp QR code with merged image', function ($imagick, $gd) {
    if (! $imagick) {
        $this->markTestSkipped('webp merge requires imagick.');
    }

    setDriver($imagick, $gd);
    $qrCode = QrCode::format('webp')->merge(__DIR__.'/images/linkxtr.png', .2, true)->generate('https://example.com/merged');

    expect(read_qr_code($qrCode))->toBe('https://example.com/merged');
})->with('drivers');

it('can scan a generated eps QR code with merged image', function ($imagick, $gd) {
    if (! $imagick) {
        $this->markTestSkipped('eps convert to png to scan requires imagick.');
    }
    // Ghostscript is required by Imagick to rasterize EPS
    if (empty(shell_exec('which gs 2>/dev/null'))) {
        $this->markTestSkipped('ghostscript is required by imagick to rasterize eps.');
    }

    setDriver($imagick, $gd);
    $qrCode = QrCode::format('eps')->merge(__DIR__.'/images/linkxtr.png', .2, true)->generate('https://example.com/merged');

    expect(read_qr_code($qrCode))->toBe('https://example.com/merged');
})->with('drivers');

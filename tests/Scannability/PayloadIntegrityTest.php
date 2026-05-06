<?php

declare(strict_types=1);

use Linkxtr\QrCode\Facades\QrCode;

beforeEach(function (): void {
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

function setDriver(bool $imagick, bool $gd): void
{
    global $mockImagickLoaded, $mockGdLoaded;
    $mockImagickLoaded = $imagick;
    $mockGdLoaded = $gd;
}

it('can scan a generated QR code with simple text', function (bool $imagick, bool $gd): void {
    setDriver($imagick, $gd);
    $htmlString = QrCode::format('png')->generate('Hello World');

    expect(read_qr_code((string) $htmlString))->toBe('Hello World');
})->with('drivers');

it('can scan a generated QR code with url', function (bool $imagick, bool $gd): void {
    setDriver($imagick, $gd);
    $htmlString = QrCode::format('png')->generate('https://example.com/path?query=param&another=value');

    expect(read_qr_code((string) $htmlString))->toBe('https://example.com/path?query=param&another=value');
})->with('drivers');

it('can scan a generated QR code with email', function (bool $imagick, bool $gd): void {
    setDriver($imagick, $gd);
    $htmlString = QrCode::format('png')->Email('mail@example.tld', 'Subject', 'Body');

    expect(read_qr_code((string) $htmlString))->toBe('mailto:mail@example.tld?subject=Subject&body=Body');
})->with('drivers');

it('can scan a generated QR code with complex WiFi payload', function (bool $imagick, bool $gd): void {
    setDriver($imagick, $gd);
    $htmlString = QrCode::format('png')->WiFi([
        'encryption' => 'WPA',
        'ssid' => 'MyNetwork',
        'password' => 'SuperSecret',
        'hidden' => 'true',
    ]);

    expect(read_qr_code((string) $htmlString))->toBe('WIFI:S:MyNetwork;T:WPA;P:SuperSecret;H:true;;');
})->with('drivers');

it('can scan a generated png QR code with merged image', function (bool $imagick, bool $gd): void {
    setDriver($imagick, $gd);
    $htmlString = QrCode::format('png')->merge(__DIR__.'/../Support/Fixtures/images/linkxtr.png', .2)->generate('https://example.com/merged');

    expect(read_qr_code((string) $htmlString))->toBe('https://example.com/merged');
})->with('drivers');

it('can scan a generated svg QR code with merged image', function ($imagick, bool $gd): void {
    if (! $imagick) {
        $this->markTestSkipped('svg convert to png to scan requires imagick.');
    }

    setDriver($imagick, $gd);
    $htmlString = QrCode::format('svg')->merge(__DIR__.'/../Support/Fixtures/images/linkxtr.png', .2)->generate('https://example.com/merged');

    expect(read_qr_code((string) $htmlString))->toBe('https://example.com/merged');
})->with('drivers');

it('can scan a generated webp QR code with merged image', function ($imagick, bool $gd): void {
    if (! $imagick) {
        $this->markTestSkipped('webp merge requires imagick.');
    }

    setDriver($imagick, $gd);
    $htmlString = QrCode::format('webp')->merge(__DIR__.'/../Support/Fixtures/images/linkxtr.png', .2)->generate('https://example.com/merged');

    expect(read_qr_code((string) $htmlString))->toBe('https://example.com/merged');
})->with('drivers');

it('can scan a generated standard eps QR code', function ($imagick, bool $gd): void {
    if (! $imagick) {
        $this->markTestSkipped('eps convert to png to scan requires imagick.');
    }

    $gsCommand = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'gswin64c -v' : 'gs --version';
    exec($gsCommand, $output, $returnVar);
    if ($returnVar !== 0) {
        $this->markTestSkipped('ghostscript is required by imagick to rasterize eps.');
    }

    setDriver($imagick, $gd);

    $htmlString = QrCode::format('eps')->generate('https://example.com/eps');

    expect(read_qr_code((string) $htmlString))->toBe('https://example.com/eps');
})->with('drivers');

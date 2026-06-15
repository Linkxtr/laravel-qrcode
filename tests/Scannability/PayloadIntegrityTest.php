<?php

declare(strict_types=1);

use Linkxtr\QrCode\Facades\QrCode;
use Linkxtr\QrCode\Support\Environment;

beforeEach(function (): void {
    QrCode::setFacadeApplication(app());
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

it('can scan a generated QR code with simple text', function (bool $imagick, bool $gd): void {
    Environment::mockExtension('imagick', $imagick);
    Environment::mockExtension('gd', $gd);

    $qrCodeResult = QrCode::format('png')->generate('Hello World');

    expect(read_qr_code((string) $qrCodeResult))->toBe('Hello World');
})->with('drivers');

it('can scan a generated QR code with url', function (bool $imagick, bool $gd): void {
    Environment::mockExtension('imagick', $imagick);
    Environment::mockExtension('gd', $gd);

    $qrCodeResult = QrCode::format('png')->generate('https://example.com/path?query=param&another=value');

    expect(read_qr_code((string) $qrCodeResult))->toBe('https://example.com/path?query=param&another=value');
})->with('drivers');

it('can scan a generated QR code with email', function (bool $imagick, bool $gd): void {
    Environment::mockExtension('imagick', $imagick);
    Environment::mockExtension('gd', $gd);

    $qrCodeResult = QrCode::format('png')->Email('mail@example.tld', 'Subject', 'Body');

    expect(read_qr_code((string) $qrCodeResult))->toBe('mailto:mail@example.tld?subject=Subject&body=Body');
})->with('drivers');

it('can scan a generated QR code with complex WiFi payload', function (bool $imagick, bool $gd): void {
    Environment::mockExtension('imagick', $imagick);
    Environment::mockExtension('gd', $gd);

    $qrCodeResult = QrCode::format('png')->WiFi(
        encryption: 'WPA',
        ssid: 'MyNetwork',
        password: 'SuperSecret',
        hidden: true,
    );

    expect(read_qr_code((string) $qrCodeResult))->toBe('WIFI:S:MyNetwork;T:WPA;P:SuperSecret;H:true;;');
})->with('drivers');

it('can scan a generated png QR code with merged image', function (bool $imagick, bool $gd): void {
    Environment::mockExtension('imagick', $imagick);
    Environment::mockExtension('gd', $gd);

    $qrCodeResult = QrCode::format('png')->merge(__DIR__.'/../Support/Fixtures/images/linkxtr.png', .2)->generate('https://example.com/merged');

    expect(read_qr_code((string) $qrCodeResult))->toBe('https://example.com/merged');
})->with('drivers');

it('can scan a generated svg QR code with merged image', function ($imagick, bool $gd): void {
    if (! $imagick) {
        $this->markTestSkipped('svg convert to png to scan requires imagick.');
    }

    Environment::mockExtension('imagick', $imagick);
    Environment::mockExtension('gd', $gd);

    $qrCodeResult = QrCode::format('svg')->merge(__DIR__.'/../Support/Fixtures/images/linkxtr.png', .2)->generate('https://example.com/merged');

    expect(read_qr_code((string) $qrCodeResult))->toBe('https://example.com/merged');
})->with('drivers');

it('can scan a generated webp QR code with merged image', function ($imagick, bool $gd): void {
    if (! $imagick) {
        $this->markTestSkipped('webp merge requires imagick.');
    }

    Environment::mockExtension('imagick', $imagick);
    Environment::mockExtension('gd', $gd);

    $qrCodeResult = QrCode::format('webp')->merge(__DIR__.'/../Support/Fixtures/images/linkxtr.png', .2)->generate('https://example.com/merged');

    expect(read_qr_code((string) $qrCodeResult))->toBe('https://example.com/merged');
})->with('drivers');

it('can scan a generated standard eps QR code', function ($imagick, bool $gd): void {
    if (! $imagick) {
        $this->markTestSkipped('eps convert to png to scan requires imagick.');
    }

    if (! function_exists('exec')) {
        $this->markTestSkipped('exec() is disabled; cannot verify Ghostscript availability.');
    }

    $gsCommand = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'gswin64c -v' : 'gs --version';
    exec($gsCommand, $output, $returnVar);
    if ($returnVar !== 0) {
        $this->markTestSkipped('ghostscript is required by imagick to rasterize eps.');
    }

    Environment::mockExtension('imagick', $imagick);
    Environment::mockExtension('gd', $gd);

    $qrCodeResult = QrCode::format('eps')->generate('https://example.com/eps');

    expect(read_qr_code((string) $qrCodeResult))->toBe('https://example.com/eps');
})->with('drivers');

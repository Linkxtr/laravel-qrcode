<?php

use Linkxtr\QrCode\Facades\QrCode;

require_once __DIR__.'/Overrides.php';

beforeEach(function () {
    QrCode::setFacadeApplication(app());
    global $mockImagickLoaded, $mockGdLoaded;
    $mockImagickLoaded = true;
    $mockGdLoaded = true;
});

dataset('drivers', [
    'imagick_enabled' => [true, false],
    'gd_enabled' => [false, true],
    'both_enabled' => [true, true],
]);

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

it('can scan a generated QR code with merged image', function ($imagick, $gd) {
    setDriver($imagick, $gd);
    $qrCode = QrCode::format('png')->merge(__DIR__.'/images/linkxtr.png', .2, true)->generate('https://example.com/merged');

    expect(read_qr_code($qrCode))->toBe('https://example.com/merged');
})->with('drivers');

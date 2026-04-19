<?php

declare(strict_types=1);

use BaconQrCode\Common\ErrorCorrectionLevel as BaconErrorCorrectionLevel;
use BaconQrCode\Renderer\RendererStyle\GradientType as BaconGradientType;
use Linkxtr\QrCode\Enums\ColorModel;
use Linkxtr\QrCode\Enums\Concerns\EnumHelper;
use Linkxtr\QrCode\Enums\ErrorCorrectionLevel;
use Linkxtr\QrCode\Enums\EyeStyle;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Enums\GradientType;
use Linkxtr\QrCode\Enums\Style;

covers([
    ColorModel::class,
    Format::class,
    Style::class,
    EyeStyle::class,
    ErrorCorrectionLevel::class,
    GradientType::class,
    EnumHelper::class,
]);

test('the enum helper trait strictly extracts all scalar string values to kill array_map mutants', function () {
    expect(ColorModel::toArray())->toBe(['rgb', 'cmyk', 'gray']);
    expect(Format::toArray())->toBe(['png', 'svg', 'eps', 'webp']);
    expect(Style::toArray())->toBe(['square', 'dot', 'round']);
    expect(EyeStyle::toArray())->toBe(['square', 'circle', 'pointy']);
});

test('error correction level maps perfectly to the underlying bacon enum to kill match arm mutants', function () {
    expect(ErrorCorrectionLevel::L->toBaconErrorCorrectionLevel())->toBe(BaconErrorCorrectionLevel::L());
    expect(ErrorCorrectionLevel::M->toBaconErrorCorrectionLevel())->toBe(BaconErrorCorrectionLevel::M());
    expect(ErrorCorrectionLevel::Q->toBaconErrorCorrectionLevel())->toBe(BaconErrorCorrectionLevel::Q());
    expect(ErrorCorrectionLevel::H->toBaconErrorCorrectionLevel())->toBe(BaconErrorCorrectionLevel::H());
});

test('gradient type maps perfectly to the underlying bacon enum to kill match arm mutants', function () {
    expect(GradientType::VERTICAL->toBaconGradientType())->toBe(BaconGradientType::VERTICAL());
    expect(GradientType::HORIZONTAL->toBaconGradientType())->toBe(BaconGradientType::HORIZONTAL());
    expect(GradientType::DIAGONAL->toBaconGradientType())->toBe(BaconGradientType::DIAGONAL());
    expect(GradientType::INVERSE_DIAGONAL->toBaconGradientType())->toBe(BaconGradientType::INVERSE_DIAGONAL());
    expect(GradientType::RADIAL->toBaconGradientType())->toBe(BaconGradientType::RADIAL());
});

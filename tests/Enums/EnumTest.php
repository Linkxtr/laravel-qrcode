<?php

declare(strict_types=1);

use BaconQrCode\Common\ErrorCorrectionLevel as BaconErrorCorrectionLevel;
use BaconQrCode\Renderer\RendererStyle\Gradient;
use BaconQrCode\Renderer\RendererStyle\GradientType as BaconGradientType;
use Linkxtr\QrCode\Enums\ErrorCorrectionLevel;
use Linkxtr\QrCode\Enums\EyeStyle;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Enums\GradientType;
use Linkxtr\QrCode\Enums\Style;
use Linkxtr\QrCode\Generator;

it('accepts Format enum', function () {
    $generator = new Generator;
    $generator->format(Format::PNG);

    expect(invade($generator)->config->getFormat())->toBe(Format::PNG);
});

it('accepts Style enum', function () {
    $generator = new Generator;
    $generator->style(Style::DOT);

    expect(invade($generator)->config->getStyle())->toBe(Style::DOT);
});

it('accepts EyeStyle enum', function () {
    $generator = new Generator;
    $generator->eye(EyeStyle::CIRCLE);

    expect(invade($generator)->config->getEyeStyle())->toBe(EyeStyle::CIRCLE);
});

it('accepts ErrorCorrectionLevel enum', function () {
    $generator = new Generator;
    $generator->errorCorrection(ErrorCorrectionLevel::H);

    expect(invade($generator)->config->getErrorCorrectionLevel())->toEqual(ErrorCorrectionLevel::H);
});

it('accepts ErrorCorrectionLevel string', function () {
    $generator = new Generator;
    $generator->errorCorrection('M');

    expect(invade($generator)->config->getErrorCorrectionLevel())->toBe(ErrorCorrectionLevel::M);
});

it('throws exception for invalid ErrorCorrectionLevel', function () {
    $generator = new Generator;
    $generator->errorCorrection('Invalid');
})->throws(InvalidArgumentException::class);

it('converts ErrorCorrectionLevel enum to BaconErrorCorrectionLevel', function () {
    expect(ErrorCorrectionLevel::L->toBaconErrorCorrectionLevel())->toBe(BaconErrorCorrectionLevel::L());
    expect(ErrorCorrectionLevel::M->toBaconErrorCorrectionLevel())->toBe(BaconErrorCorrectionLevel::M());
    expect(ErrorCorrectionLevel::Q->toBaconErrorCorrectionLevel())->toBe(BaconErrorCorrectionLevel::Q());
    expect(ErrorCorrectionLevel::H->toBaconErrorCorrectionLevel())->toBe(BaconErrorCorrectionLevel::H());
});

it('accepts GradientType enum', function () {
    $generator = new Generator;
    $generator->gradient(0, 0, 0, 255, 255, 255, GradientType::DIAGONAL);

    expect(invade($generator)->config->getGradient())->toBeInstanceOf(Gradient::class);
});

it('converts GradientType enum to BaconGradientType', function () {
    expect(GradientType::VERTICAL->toBaconGradientType())->toBe(BaconGradientType::VERTICAL());
    expect(GradientType::HORIZONTAL->toBaconGradientType())->toBe(BaconGradientType::HORIZONTAL());
    expect(GradientType::DIAGONAL->toBaconGradientType())->toBe(BaconGradientType::DIAGONAL());
    expect(GradientType::INVERSE_DIAGONAL->toBaconGradientType())->toBe(BaconGradientType::INVERSE_DIAGONAL());
    expect(GradientType::RADIAL->toBaconGradientType())->toBe(BaconGradientType::RADIAL());
});

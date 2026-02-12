<?php

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

    $reflection = new ReflectionClass($generator);
    $property = $reflection->getProperty('format');

    expect($property->getValue($generator))->toBe(Format::PNG);
});

it('accepts Style enum', function () {
    $generator = new Generator;
    $generator->style(Style::DOT);

    $reflection = new ReflectionClass($generator);
    $property = $reflection->getProperty('style');

    expect($property->getValue($generator))->toBe(Style::DOT);
});

it('accepts EyeStyle enum', function () {
    $generator = new Generator;
    $generator->eye(EyeStyle::CIRCLE);

    $reflection = new ReflectionClass($generator);
    $property = $reflection->getProperty('eyeStyle');

    expect($property->getValue($generator))->toBe(EyeStyle::CIRCLE);
});

it('accepts ErrorCorrectionLevel enum', function () {
    $generator = new Generator;
    $generator->errorCorrection(ErrorCorrectionLevel::H);

    $reflection = new ReflectionClass($generator);
    $property = $reflection->getProperty('errorCorrection');

    expect($property->getValue($generator))->toEqual(ErrorCorrectionLevel::H);
});

it('accepts ErrorCorrectionLevel string', function () {
    $generator = new Generator;
    $generator->errorCorrection('M');

    $reflection = new ReflectionClass($generator);
    $property = $reflection->getProperty('errorCorrection');

    expect($property->getValue($generator))->toEqual(ErrorCorrectionLevel::M);
});

it('throws exception for invalid ErrorCorrectionLevel', function () {
    $generator = new Generator;
    $generator->errorCorrection('Invalid');
})->throws(InvalidArgumentException::class);

it('converts ErrorCorrectionLevel enum to BaconErrorCorrectionLevel', function () {
    expect(ErrorCorrectionLevel::L->toBaconErrorCorrectionLevel())->toEqual(BaconErrorCorrectionLevel::L());
    expect(ErrorCorrectionLevel::M->toBaconErrorCorrectionLevel())->toEqual(BaconErrorCorrectionLevel::M());
    expect(ErrorCorrectionLevel::Q->toBaconErrorCorrectionLevel())->toEqual(BaconErrorCorrectionLevel::Q());
    expect(ErrorCorrectionLevel::H->toBaconErrorCorrectionLevel())->toEqual(BaconErrorCorrectionLevel::H());
});

it('accepts GradientType enum', function () {
    $generator = new Generator;
    $generator->gradient(0, 0, 0, 255, 255, 255, GradientType::DIAGONAL);

    $reflection = new ReflectionClass($generator);
    $property = $reflection->getProperty('gradient');

    $gradient = $property->getValue($generator);

    expect($gradient)->toBeInstanceOf(Gradient::class);
});

it('converts GradientType enum to BaconGradientType', function () {
    expect(GradientType::VERTICAL->toBaconGradientType())->toEqual(BaconGradientType::VERTICAL());
    expect(GradientType::HORIZONTAL->toBaconGradientType())->toEqual(BaconGradientType::HORIZONTAL());
    expect(GradientType::DIAGONAL->toBaconGradientType())->toEqual(BaconGradientType::DIAGONAL());
    expect(GradientType::INVERSE_DIAGONAL->toBaconGradientType())->toEqual(BaconGradientType::INVERSE_DIAGONAL());
    expect(GradientType::RADIAL->toBaconGradientType())->toEqual(BaconGradientType::RADIAL());
});

<?php

declare(strict_types=1);

use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\Cmyk;
use BaconQrCode\Renderer\Color\Gray;
use BaconQrCode\Renderer\Color\Rgb as BaconRgb;
use BaconQrCode\Renderer\RendererStyle\EyeFill;
use Linkxtr\QrCode\DTOs\Config;
use Linkxtr\QrCode\Enums\ColorModel;
use Linkxtr\QrCode\Support\Bacon\FillResolver;
use Linkxtr\QrCode\ValueObjects\Colors\Rgb;

covers(FillResolver::class);

it('builds the fill correctly with and without gradient', function (): void {
    $config = new Config;
    $fill = FillResolver::resolve($config);

    expect($fill->hasGradientFill())->toBeFalse();

    $config->setupGradient(Rgb::fromArray([255, 0, 0]), Rgb::fromArray([0, 255, 0]), 'diagonal');
    $fill = FillResolver::resolve($config);

    expect($fill->hasGradientFill())->toBeTrue();
});

it('builds the correct eye fills', function (): void {
    $config = new Config;
    $config->setupEyeColor(0, Rgb::fromArray([10, 20, 30]));
    $config->setupEyeColor(1, Rgb::fromArray([40, 50, 60]));
    $config->setupEyeColor(2, Rgb::fromArray([70, 80, 90]));

    $fill = FillResolver::resolve($config);

    expect($fill->getTopLeftEyeFill())->toBeInstanceOf(EyeFill::class)
        ->and($fill->getTopLeftEyeFill()->getExternalColor())->toBeInstanceOf(BaconRgb::class)
        ->and($fill->getTopLeftEyeFill()->getExternalColor()->getRed())->toBe(10)
        ->and($fill->getTopLeftEyeFill()->getExternalColor()->getGreen())->toBe(20)
        ->and($fill->getTopLeftEyeFill()->getExternalColor()->getBlue())->toBe(30)
        ->and($fill->getTopRightEyeFill())->toBeInstanceOf(EyeFill::class)
        ->and($fill->getTopRightEyeFill()->getExternalColor())->toBeInstanceOf(BaconRgb::class)
        ->and($fill->getTopRightEyeFill()->getExternalColor()->getRed())->toBe(40)
        ->and($fill->getTopRightEyeFill()->getExternalColor()->getGreen())->toBe(50)
        ->and($fill->getTopRightEyeFill()->getExternalColor()->getBlue())->toBe(60)
        ->and($fill->getBottomLeftEyeFill())->toBeInstanceOf(EyeFill::class)
        ->and($fill->getBottomLeftEyeFill()->getExternalColor())->toBeInstanceOf(BaconRgb::class)
        ->and($fill->getBottomLeftEyeFill()->getExternalColor()->getRed())->toBe(70)
        ->and($fill->getBottomLeftEyeFill()->getExternalColor()->getGreen())->toBe(80)
        ->and($fill->getBottomLeftEyeFill()->getExternalColor()->getBlue())->toBe(90);
});

it('builds the correct color models', function (): void {
    $config = new Config;

    $config->setupColor(255, 0, 0, 99);

    $fill = new FillResolver($config);
    expect(invade($fill)->buildColor($config->getColorValue()))->toBeInstanceOf(Alpha::class);

    $config->setupColor(255, 0, 0);
    $fill = new FillResolver($config);
    expect(invade($fill)->buildColor($config->getColorValue()))->toBeInstanceOf(BaconRgb::class);

    $config->setColorModel(ColorModel::CMYK);
    $config->setupColor(10, 20, 30, 40);

    $fill = new FillResolver($config);
    expect(invade($fill)->buildColor($config->getColorValue()))->toBeInstanceOf(Cmyk::class)
        ->and(invade($fill)->buildColor($config->getColorValue())->getBlack())->toBe(40);

    $config->setupColor(10, 20, 30);
    $fill = new FillResolver($config);
    expect(invade($fill)->buildColor($config->getColorValue()))->toBeInstanceOf(Cmyk::class)
        ->and(invade($fill)->buildColor($config->getColorValue())->getBlack())->toBe(100);

    $config->setGrayscale(50);
    $fill = new FillResolver($config);
    expect(invade($fill)->buildColor($config->getColorValue()))->toBeInstanceOf(Gray::class);
});

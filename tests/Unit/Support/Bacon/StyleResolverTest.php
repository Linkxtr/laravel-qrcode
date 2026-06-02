<?php

declare(strict_types=1);

use BaconQrCode\Renderer\Eye\CompositeEye;
use BaconQrCode\Renderer\Eye\ModuleEye;
use BaconQrCode\Renderer\Eye\PointyEye;
use BaconQrCode\Renderer\Eye\SimpleCircleEye;
use BaconQrCode\Renderer\Eye\SquareEye;
use BaconQrCode\Renderer\Module\DotsModule;
use BaconQrCode\Renderer\Module\RoundnessModule;
use BaconQrCode\Renderer\Module\SquareModule;
use Linkxtr\QrCode\DTOs\Config;
use Linkxtr\QrCode\Enums\EyeStyle;
use Linkxtr\QrCode\Enums\Style;
use Linkxtr\QrCode\Support\Bacon\StyleResolver;

covers(StyleResolver::class);

it('resolves the correct module styles', function (): void {
    $config = new Config;
    $styleResolver = new StyleResolver($config);

    $config->setupStyle(Style::DOT);
    expect(invade($styleResolver)->resolveModule())->toBeInstanceOf(DotsModule::class);

    $config->setupStyle(Style::ROUND);
    expect(invade($styleResolver)->resolveModule())->toBeInstanceOf(RoundnessModule::class);

    $config->setupStyle(Style::SQUARE);
    expect(invade($styleResolver)->resolveModule())->toBeInstanceOf(SquareModule::class);
});

it('resolves single eye styles', function (): void {
    $config = new Config;
    $styleResolver = invade(new StyleResolver($config));

    expect($styleResolver->resolveEye($styleResolver->resolveModule()))->toBeInstanceOf(ModuleEye::class);

    $config->setEyeStyle(EyeStyle::SQUARE);
    $styleResolver = invade(new StyleResolver($config));
    expect($styleResolver->resolveEye($styleResolver->resolveModule()))->toBeInstanceOf(SquareEye::class);

    $config->setEyeStyle(EyeStyle::CIRCLE);
    $styleResolver = invade(new StyleResolver($config));
    expect($styleResolver->resolveEye($styleResolver->resolveModule()))->toBeInstanceOf(SimpleCircleEye::class);

    $config->setEyeStyle(EyeStyle::POINTY);
    $styleResolver = invade(new StyleResolver($config));
    expect($styleResolver->resolveEye($styleResolver->resolveModule()))->toBeInstanceOf(PointyEye::class);

    $config->setInternalEyeStyle(EyeStyle::SQUARE);
    $styleResolver = invade(new StyleResolver($config));
    expect($styleResolver->resolveEye($styleResolver->resolveModule()))->toBeInstanceOf(CompositeEye::class);
});

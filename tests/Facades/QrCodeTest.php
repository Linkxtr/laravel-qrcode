<?php

declare(strict_types=1);

use Linkxtr\QrCode\Facades\QrCode;
use Linkxtr\QrCode\Generator;
use Linkxtr\QrCode\QrCodeServiceProvider;

covers(QrCode::class);
covers(QrCodeServiceProvider::class);

it('confirms the facade class exists', function () {
    expect(class_exists(QrCode::class))->toBeTrue();
});

it('resolves the generator instance from the facade', function () {
    QrCode::setFacadeApplication(app());
    expect(QrCode::getFacadeRoot())->toBeInstanceOf(Generator::class);
});

it('can call methods via the facade', function () {
    QrCode::setFacadeApplication(app());
    $size = 250;
    QrCode::size($size);

    expect(QrCode::getFacadeRoot()->getRendererStyle()->getSize())->toBe($size);
});

it('resolves alias correctly', function () {
    $provider = new QrCodeServiceProvider(app());
    $provider->register();

    expect(app()->bound('qrcode'))->toBeTrue();
    expect(resolve('qrcode'))->toBeInstanceOf(Generator::class);
});

it('provides generator class', function () {
    $provider = new QrCodeServiceProvider(app());
    expect($provider->provides())->toContain(Generator::class);
});

<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Linkxtr\QrCode\Components\QrCodeComponent;
use Linkxtr\QrCode\Console\Commands\GenerateQrCodeCommand;
use Linkxtr\QrCode\Generator;
use Linkxtr\QrCode\QrCodeServiceProvider;

covers(QrCodeServiceProvider::class);

test('it merges the default configuration', function () {
    expect(config('qrcode'))->toBeArray()
        ->and(config('qrcode'))->not->toBeEmpty();
});

test('it binds the generator to the container as a singleton', function () {
    $generator1 = app('qrcode');
    $generator2 = app(Generator::class);

    expect($generator1)->toBeInstanceOf(Generator::class)
        ->and($generator2)->toBeInstanceOf(Generator::class)
        ->and($generator1)->toBe($generator2);
});

test('it registers the blade component', function () {
    $aliases = Blade::getClassComponentAliases();

    expect($aliases)->toHaveKey('qrcode')
        ->and($aliases['qrcode'])->toBe(QrCodeComponent::class);
});

test('it registers publishable assets when running in console', function () {
    $publishGroups = ServiceProvider::publishableGroups();
    expect($publishGroups)->toContain('qrcode-config');

    $paths = ServiceProvider::pathsToPublish(
        QrCodeServiceProvider::class,
        'qrcode-config'
    );

    expect($paths)->toBeArray()
        ->and($paths)->not->toBeEmpty();

    $actualSourcePathString = array_key_first($paths);
    $actualDestinationPath = $paths[$actualSourcePathString];

    $expectedSourcePath = realpath(__DIR__.'/../../config/qrcode.php');

    expect(realpath($actualSourcePathString))->toBe($expectedSourcePath)
        ->and($actualDestinationPath)->toBe(config_path('qrcode.php'));
});

test('it registers the generate qr code command', function () {
    $commands = Artisan::all();

    expect($commands)->toHaveKey('qr:generate');

    expect(get_class($commands['qr:generate']))->toBe(GenerateQrCodeCommand::class);
});

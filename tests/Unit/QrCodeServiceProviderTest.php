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

test('it merges the default configuration', function (): void {
    expect(config('qrcode'))->toBeArray()
        ->and(config('qrcode'))->not->toBeEmpty();
});

test('it binds the generator to the container as a singleton', function (): void {
    $generator1 = app('qrcode');
    $generator2 = app(Generator::class);

    expect($generator1)->toBeInstanceOf(Generator::class)
        ->and($generator2)->toBeInstanceOf(Generator::class)
        ->and($generator1)->toBe($generator2);
});

test('it registers the blade component with both tag syntaxes', function (): void {
    $aliases = Blade::getClassComponentAliases();

    expect($aliases)->toHaveKey('qrcode')
        ->and($aliases['qrcode'])->toBe(QrCodeComponent::class)
        ->and($aliases)->toHaveKey('qr-code')
        ->and($aliases['qr-code'])->toBe(QrCodeComponent::class);
});

test('it registers publishable assets when running in console', function (): void {
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

test('it registers the generate qr code command', function (): void {
    $commands = Artisan::all();

    expect($commands)->toHaveKey('qr:generate');

    expect($commands['qr:generate']::class)->toBe(GenerateQrCodeCommand::class);
});

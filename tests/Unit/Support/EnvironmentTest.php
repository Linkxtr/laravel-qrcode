<?php

declare(strict_types=1);

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Linkxtr\QrCode\Exceptions\InvalidEnvironmentMutationException;
use Linkxtr\QrCode\Support\Environment;

covers(Environment::class);

it('falls back to native extension_loaded when no mock is set', function (): void {
    expect(Environment::hasExtension('core'))->toBeTrue();
    expect(Environment::hasExtension('fake_missing_ext'))->toBeFalse();
});

it('can explicitly mock an extension state', function (): void {
    Environment::mockExtension('fake_missing_ext', true);
    expect(Environment::hasExtension('fake_missing_ext'))->toBeTrue();

    Environment::mockExtension('core', false);
    expect(Environment::hasExtension('core'))->toBeFalse();
});

it('can fluently disable an extension', function (): void {
    Environment::disableExtension('core');

    expect(Environment::hasExtension('core'))->toBeFalse();
});

it('can fluently enable an extension', function (): void {
    Environment::enableExtension('fake_missing_ext');

    expect(Environment::hasExtension('fake_missing_ext'))->toBeTrue();
});

it('can completely clear all mocked states', function (): void {
    Environment::enableExtension('fake_missing_ext');
    Environment::disableExtension('core');

    expect(Environment::hasExtension('fake_missing_ext'))->toBeTrue()
        ->and(Environment::hasExtension('core'))->toBeFalse();

    Environment::clearMocks();

    expect(Environment::hasExtension('fake_missing_ext'))->toBeFalse()
        ->and(Environment::hasExtension('core'))->toBeTrue();
});

it('forces GD fallback and ignores imagick when configured to do so', function (): void {
    Environment::enableExtension('imagick');

    expect(Environment::hasExtension('imagick'))->toBeTrue();

    config(['qrcode.force_gd' => true]);

    expect(Environment::hasExtension('imagick'))->toBeFalse();
});

it('throws an exception if mutation methods are called outside a testing environment', function (): void {
    App::shouldReceive('runningUnitTests')
        ->times(4)
        ->andReturn(false);

    expect(fn () => Environment::disableExtension('imagick'))
        ->toThrow(InvalidEnvironmentMutationException::class, 'strictly reserved for testing environments');

    expect(fn () => Environment::enableExtension('imagick'))
        ->toThrow(InvalidEnvironmentMutationException::class, 'strictly reserved for testing environments');

    expect(fn () => Environment::mockExtension('imagick', true))
        ->toThrow(InvalidEnvironmentMutationException::class, 'strictly reserved for testing environments');

    expect(fn () => Environment::mockIsWindows(true))
        ->toThrow(InvalidEnvironmentMutationException::class, 'strictly reserved for testing environments');
});

it('does not force GD fallback by default when config is missing', function (): void {
    Config::set('qrcode', []);
    Environment::enableExtension('imagick');

    expect(Environment::hasExtension('imagick'))->toBeTrue();
});

it('can check if the environment is windows', function (): void {
    expect(Environment::isWindows())->toBeBool();
});

it('can successfully mock the windows environment', function (): void {
    Environment::mockIsWindows(true);
    expect(Environment::isWindows())->toBeTrue();

    Environment::mockIsWindows(false);
    expect(Environment::isWindows())->toBeFalse();

    Environment::clearMocks();
});

it('clears windows mock when clearMocks is called', function (): void {
    $original = Environment::isWindows();

    Environment::mockIsWindows(! $original);
    expect(Environment::isWindows())->not->toBe($original);

    Environment::clearMocks();
    expect(Environment::isWindows())->toBe($original);
});

it('evaluates native OS windows check correctly', function (): void {
    $expected = DIRECTORY_SEPARATOR === '\\';
    expect(Environment::isWindows())->toBe($expected);
});

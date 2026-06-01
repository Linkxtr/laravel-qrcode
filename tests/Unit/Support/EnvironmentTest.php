<?php

declare(strict_types=1);

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

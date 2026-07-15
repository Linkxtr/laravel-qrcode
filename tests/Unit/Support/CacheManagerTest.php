<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Linkxtr\QrCode\DTOs\Config;
use Linkxtr\QrCode\Support\CacheManager;
use Linkxtr\QrCode\Support\QrCodeResult;

covers(CacheManager::class);

test('it completely bypasses the cache when caching is disabled to kill logic mutants', function (): void {
    Cache::shouldReceive('remember')->never();

    $config = Config::fromArray(['cache_enabled' => false]);
    $qrCodeResult = CacheManager::handle($config, 'my-payload');

    expect($qrCodeResult)->toBeInstanceOf(QrCodeResult::class);
});

test('it hits the cache and returns a valid result when caching is enabled', function (): void {
    $config = Config::fromArray(['cache_enabled' => true, 'cache_ttl' => 600]);

    Cache::shouldReceive('remember')
        ->once()
        ->withArgs(fn (string $key, int $ttl, Closure $callback): bool => str_starts_with($key, 'qrcode_') && $ttl === 600)
        ->andReturn('fake-cached-svg-string');

    $qrCodeResult = CacheManager::handle($config, 'my-payload');

    expect($qrCodeResult)->toBeInstanceOf(QrCodeResult::class);
});

test('it strictly executes the closure to generate the qrcode on a cache miss', function (): void {
    $config = Config::fromArray(['cache_enabled' => true]);

    Cache::shouldReceive('remember')
        ->once()
        ->andReturnUsing(function ($key, $ttl, $closure) {
            $generatedContent = $closure();

            expect($generatedContent)->toBeString()->not->toBeEmpty();

            return $generatedContent;
        });

    CacheManager::handle($config, 'my-payload');
});

test('it strictly formats the exact cache key to kill concatenation and hashing mutants', function (): void {
    $config = Config::fromArray(['cache_enabled' => true]);
    $payload = 'super-secret-payload';

    $manager = new CacheManager($config, $payload);

    $key = invade($manager)->getKey();

    $expectedKey = 'qrcode_'.md5($payload.serialize($config));

    expect($key)->toBe($expectedKey);
});

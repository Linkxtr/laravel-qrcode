<?php

declare(strict_types=1);

use Carbon\Carbon;
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
        ->withArgs(fn (string $key, int $ttl, Closure $callback): bool => str_starts_with($key, 'qrcode_v3_') && $ttl === 600)
        ->andReturn('fake-cached-svg-string');

    $qrCodeResult = CacheManager::handle($config, 'my-payload');

    expect($qrCodeResult)->toBeInstanceOf(QrCodeResult::class);
});

test('it uses the specified cache store when configured', function (): void {
    $config = Config::fromArray(['cache_enabled' => true, 'cache_store' => 'redis', 'cache_ttl' => 600]);

    $storeMock = Mockery::mock();
    $storeMock->shouldReceive('remember')
        ->once()
        ->withArgs(fn (string $key, int $ttl, Closure $callback): bool => str_starts_with($key, 'qrcode_v3_') && $ttl === 600)
        ->andReturn('fake-cached-svg-string');

    Cache::shouldReceive('store')
        ->once()
        ->with('redis')
        ->andReturn($storeMock);

    $qrCodeResult = CacheManager::handle($config, 'my-payload');

    expect($qrCodeResult)->toBeInstanceOf(QrCodeResult::class);
});

test('it defaults cache ttl to 86400 seconds', function (): void {
    $config = Config::fromArray(['cache_enabled' => true]);

    Cache::shouldReceive('remember')
        ->once()
        ->withArgs(fn (string $key, int $ttl, Closure $callback): bool => str_starts_with($key, 'qrcode_v3_') && $ttl === 86400)
        ->andReturn('fake-cached-svg-string');

    CacheManager::handle($config, 'my-payload');
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

    $expectedKey = 'qrcode_v3_'.md5($payload.serialize($config));

    expect($key)->toBe($expectedKey);
});

test('it produces identical cache keys regardless of dynamic DateTimeInterface TTL timestamps', function (): void {
    $config1 = new Config;
    $config1->setupCache(Carbon::parse('2026-10-06 10:00:00'));

    $config2 = new Config;
    $config2->setupCache(Carbon::parse('2026-10-06 10:00:01'));

    $manager1 = new CacheManager($config1, 'same-payload');
    $manager2 = new CacheManager($config2, 'same-payload');

    $key1 = invade($manager1)->getKey();
    $key2 = invade($manager2)->getKey();

    expect($key1)->toBe($key2);
});

test('it accepts the payload parameter by name without parameter mismatch errors', function (): void {
    $config = Config::fromArray(['cache_enabled' => false]);

    $qrCodeResult = CacheManager::handle(config: $config, payload: 'https://example.com');

    expect($qrCodeResult)->toBeInstanceOf(QrCodeResult::class);
});

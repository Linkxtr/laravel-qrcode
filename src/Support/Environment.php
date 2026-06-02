<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Support;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Linkxtr\QrCode\Exceptions\InvalidEnvironmentMutationException;

final class Environment
{
    /** @var array<string, bool> */
    private static array $mockedExtensions = [];

    private static ?bool $mockedIsWindows = null;

    public static function hasExtension(string $extension): bool
    {
        if ($extension === 'imagick' && self::shouldForceGdFallback()) {
            return false;
        }

        if (array_key_exists($extension, self::$mockedExtensions)) {
            return self::$mockedExtensions[$extension];
        }

        return extension_loaded($extension);
    }

    public static function mockExtension(string $extension, bool $isLoaded): void
    {
        self::ensureTestingEnvironment();
        self::$mockedExtensions[$extension] = $isLoaded;
    }

    public static function disableExtension(string $extension): void
    {
        self::ensureTestingEnvironment();
        self::$mockedExtensions[$extension] = false;
    }

    public static function enableExtension(string $extension): void
    {
        self::ensureTestingEnvironment();
        self::$mockedExtensions[$extension] = true;
    }

    public static function clearMocks(): void
    {
        self::$mockedExtensions = [];
        self::$mockedIsWindows = null;
    }

    public static function isWindows(): bool
    {
        if (self::$mockedIsWindows !== null) {
            return self::$mockedIsWindows;
        }

        return DIRECTORY_SEPARATOR === '\\';
    }

    public static function mockIsWindows(bool $isWindows): void
    {
        self::ensureTestingEnvironment();
        self::$mockedIsWindows = $isWindows;
    }

    private static function shouldForceGdFallback(): bool
    {
        return Config::get('qrcode.force_gd', false) === true;
    }

    private static function ensureTestingEnvironment(): void
    {
        if (! App::runningUnitTests()) {
            throw InvalidEnvironmentMutationException::restrictedToTests();
        }
    }
}

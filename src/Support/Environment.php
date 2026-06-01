<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Support;

final class Environment
{
    /** @var array<string, bool> */
    private static array $mockedExtensions = [];

    public static function hasExtension(string $extension): bool
    {
        if (array_key_exists($extension, self::$mockedExtensions)) {
            return self::$mockedExtensions[$extension];
        }

        return extension_loaded($extension);
    }

    public static function mockExtension(string $extension, bool $isLoaded): void
    {
        self::$mockedExtensions[$extension] = $isLoaded;
    }

    public static function disableExtension(string $extension): void
    {
        self::$mockedExtensions[$extension] = false;
    }

    public static function enableExtension(string $extension): void
    {
        self::$mockedExtensions[$extension] = true;
    }

    public static function clearMocks(): void
    {
        self::$mockedExtensions = [];
    }
}

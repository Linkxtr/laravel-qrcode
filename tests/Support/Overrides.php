<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DTOs {
    if (! function_exists(__NAMESPACE__.'\base_path')) {
        function base_path($path = ''): string
        {
            $baseDiff = '/..';

            return $path === '' ? __DIR__.$baseDiff : __DIR__.$baseDiff.'/'.ltrim((string) $path, '/');
        }
    }

    $GLOBALS['mockFileGetContents'] ??= null;

    if (! function_exists(__NAMESPACE__.'\file_get_contents')) {
        function file_get_contents(...$args): string|false
        {
            if (isset($GLOBALS['mockFileGetContents']) && $GLOBALS['mockFileGetContents'] === false) {
                return false;
            }

            return \file_get_contents(...$args);
        }
    }

    $GLOBALS['mockRealPath'] ??= null;

    if (! function_exists(__NAMESPACE__.'\realpath')) {
        function realpath(...$args): string|false
        {
            if (isset($GLOBALS['mockRealPath']) && $GLOBALS['mockRealPath'] !== null) {
                return $GLOBALS['mockRealPath'];
            }

            return \realpath(...$args);
        }
    }
}

namespace Linkxtr\QrCode\Support {
    $GLOBALS['mockFilePutContents'] ??= null;

    if (! function_exists(__NAMESPACE__.'\file_put_contents')) {
        function file_put_contents(...$args): int|false
        {
            if (isset($GLOBALS['mockFilePutContents']) && $GLOBALS['mockFilePutContents']) {
                return false;
            }

            return \file_put_contents(...$args);
        }
    }
}

namespace Linkxtr\QrCode\Mergers {
    use GdImage;

    $GLOBALS['mockImageColorAllocate'] ??= null;

    if (! function_exists(__NAMESPACE__.'\imagecolorallocate')) {
        function imagecolorallocate(...$args): int|false
        {
            if (isset($GLOBALS['mockImageColorAllocate']) && $GLOBALS['mockImageColorAllocate'] === false) {
                return false;
            }

            return \imagecolorallocate(...$args);
        }
    }

    $GLOBALS['mockImageCreateTrueColor'] ??= null;

    if (! function_exists(__NAMESPACE__.'\imagecreatetruecolor')) {
        function imagecreatetruecolor(...$args): GdImage|false
        {
            if (isset($GLOBALS['mockImageCreateTrueColor']) && $GLOBALS['mockImageCreateTrueColor'] === false) {
                return false;
            }

            return \imagecreatetruecolor(...$args);
        }
    }

    $GLOBALS['mockObGetClean'] ??= null;

    if (! function_exists(__NAMESPACE__.'\ob_get_clean')) {
        function ob_get_clean(): string|false
        {
            if (isset($GLOBALS['mockObGetClean']) && $GLOBALS['mockObGetClean'] === false) {
                return false;
            }

            return \ob_get_clean();
        }
    }

    $GLOBALS['mock_imagepng_empty'] ??= null;

    if (! function_exists(__NAMESPACE__.'\imagepng')) {
        function imagepng(...$args): bool
        {
            if (isset($GLOBALS['mock_imagepng_empty']) && $GLOBALS['mock_imagepng_empty'] === true) {
                return true;
            }

            return \imagepng(...$args);
        }
    }
}

namespace Linkxtr\QrCode\DataTypes\Concerns {
    $GLOBALS['mockPregReplaceNull'] ??= false;

    if (! function_exists(__NAMESPACE__.'\preg_replace')) {
        function preg_replace(...$args): string|array|null
        {
            if (! empty($GLOBALS['mockPregReplaceNull'])) {
                return null;
            }

            return \preg_replace(...$args);
        }
    }
}

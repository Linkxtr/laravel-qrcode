<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DTOs {
    if (! function_exists(__NAMESPACE__.'\base_path')) {
        function base_path($path = ''): string
        {
            $baseDiff = '/..';

            return $path === '' ? __DIR__.$baseDiff : __DIR__.$baseDiff.'/'.ltrim($path, '/');
        }
    }

    if (! isset($GLOBALS['mockFileGetContents'])) {
        $GLOBALS['mockFileGetContents'] = null;
    }

    if (! function_exists(__NAMESPACE__.'\file_get_contents')) {
        function file_get_contents(...$args): string|false
        {
            if (isset($GLOBALS['mockFileGetContents']) && $GLOBALS['mockFileGetContents'] === false) {
                return false;
            }

            return \file_get_contents(...$args);
        }
    }
}

namespace Linkxtr\QrCode\Renderers {
    $GLOBALS['mockImagickLoaded'] = true;
    $GLOBALS['mockGdLoaded'] = true;

    if (! function_exists(__NAMESPACE__.'\extension_loaded')) {
        function extension_loaded($extension): bool
        {
            if ($extension === 'imagick') {
                return $GLOBALS['mockImagickLoaded'] ?? true;
            }

            if ($extension === 'gd') {
                return $GLOBALS['mockGdLoaded'] ?? true;
            }

            return \extension_loaded($extension);
        }
    }
}

namespace Linkxtr\QrCode {
    if (! isset($GLOBALS['mockFilePutContents'])) {
        $GLOBALS['mockFilePutContents'] = null;
    }

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

    if (! isset($GLOBALS['mockImageColorAllocateAlpha'])) {
        $GLOBALS['mockImageColorAllocateAlpha'] = null;
    }

    if (! function_exists(__NAMESPACE__.'\imagecolorallocatealpha')) {
        function imagecolorallocatealpha(...$args): int|false
        {
            if (isset($GLOBALS['mockImageColorAllocateAlpha']) && $GLOBALS['mockImageColorAllocateAlpha'] === false) {
                return false;
            }

            return \imagecolorallocatealpha(...$args);
        }
    }

    if (! isset($GLOBALS['mockImageColorAllocate'])) {
        $GLOBALS['mockImageColorAllocate'] = null;
    }

    if (! function_exists(__NAMESPACE__.'\imagecolorallocate')) {
        function imagecolorallocate(...$args): int|false
        {
            if (isset($GLOBALS['mockImageColorAllocate']) && $GLOBALS['mockImageColorAllocate'] === false) {
                return false;
            }

            return \imagecolorallocate(...$args);
        }
    }

    if (! isset($GLOBALS['mockImageCreateTrueColor'])) {
        $GLOBALS['mockImageCreateTrueColor'] = null;
    }

    if (! function_exists(__NAMESPACE__.'\imagecreatetruecolor')) {
        function imagecreatetruecolor(...$args): GdImage|false
        {
            if (isset($GLOBALS['mockImageCreateTrueColor']) && $GLOBALS['mockImageCreateTrueColor'] === false) {
                return false;
            }

            return \imagecreatetruecolor(...$args);
        }
    }

    if (! isset($GLOBALS['mockObGetClean'])) {
        $GLOBALS['mockObGetClean'] = null;
    }

    if (! function_exists(__NAMESPACE__.'\ob_get_clean')) {
        function ob_get_clean(): string|false
        {
            if (isset($GLOBALS['mockObGetClean']) && $GLOBALS['mockObGetClean'] === false) {
                return false;
            }

            return \ob_get_clean();
        }
    }

    if (! isset($GLOBALS['mockImageFill'])) {
        $GLOBALS['mockImageFill'] = null;
    }

    if (! function_exists(__NAMESPACE__.'\imagefill')) {
        function imagefill(...$args): bool
        {
            if (isset($GLOBALS['mockImageFill']) && $GLOBALS['mockImageFill'] === false) {
                return false;
            }

            return \imagefill(...$args);
        }
    }

    if (! isset($GLOBALS['mockImageCopy'])) {
        $GLOBALS['mockImageCopy'] = null;
    }

    if (! function_exists(__NAMESPACE__.'\imagecopy')) {
        function imagecopy(...$args): bool
        {
            if (isset($GLOBALS['mockImageCopy']) && $GLOBALS['mockImageCopy'] === false) {
                return false;
            }

            return \imagecopy(...$args);
        }
    }

    if (! isset($GLOBALS['mockImageCopyResampled'])) {
        $GLOBALS['mockImageCopyResampled'] = null;
    }

    if (! function_exists(__NAMESPACE__.'\imagecopyresampled')) {
        function imagecopyresampled(...$args): bool
        {
            if (isset($GLOBALS['mockImageCopyResampled']) && $GLOBALS['mockImageCopyResampled'] === false) {
                return false;
            }

            return \imagecopyresampled(...$args);
        }
    }

    if (! isset($GLOBALS['mockImageSaveAlpha'])) {
        $GLOBALS['mockImageSaveAlpha'] = null;
    }

    if (! function_exists(__NAMESPACE__.'\imagesavealpha')) {
        function imagesavealpha(...$args): bool
        {
            if (isset($GLOBALS['mockImageSaveAlpha']) && $GLOBALS['mockImageSaveAlpha'] === false) {
                return false;
            }

            return \imagesavealpha(...$args);
        }
    }

    if (! isset($GLOBALS['mock_imagepng_empty'])) {
        $GLOBALS['mock_imagepng_empty'] = null;
    }

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

namespace Linkxtr\QrCode\Support {
    use GdImage;

    if (! isset($GLOBALS['mockImageCreateFromString'])) {
        $GLOBALS['mockImageCreateFromString'] = null;
    }

    if (! function_exists(__NAMESPACE__.'\imagecreatefromstring')) {
        function imagecreatefromstring($data): GdImage|false
        {
            if (isset($GLOBALS['mockImageCreateFromString']) && $GLOBALS['mockImageCreateFromString'] === false) {
                return false;
            }

            return \imagecreatefromstring($data);
        }
    }
}

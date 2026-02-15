<?php

declare(strict_types=1);

namespace Linkxtr\QrCode {
    if (! function_exists('Linkxtr\QrCode\base_path')) {
        function base_path($path = '')
        {
            $baseDiff = '/..';

            return $path === '' ? __DIR__.$baseDiff : __DIR__.$baseDiff.'/'.ltrim($path, '/');
        }
    }

    $GLOBALS['mockFilePutContents'] = false;

    if (! function_exists('Linkxtr\QrCode\file_put_contents')) {
        function file_put_contents($filename, $data, $flags = 0, $context = null)
        {
            if (isset($GLOBALS['mockFilePutContents']) && $GLOBALS['mockFilePutContents']) {
                return false;
            }

            return \file_put_contents($filename, $data, $flags, $context);
        }
    }

    $GLOBALS['mockImagickLoaded'] = true;
    $GLOBALS['mockGdLoaded'] = true;

    if (! function_exists('Linkxtr\QrCode\extension_loaded')) {
        function extension_loaded($extension)
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

    $GLOBALS['mockFileGetContents'] = null;

    if (! function_exists('Linkxtr\QrCode\file_get_contents')) {
        function file_get_contents($filename, $use_include_path = false, $context = null, $offset = 0, $length = null)
        {
            if (isset($GLOBALS['mockFileGetContents']) && $GLOBALS['mockFileGetContents'] === false) {
                return false;
            }

            if (func_num_args() >= 5) {
                return \file_get_contents($filename, $use_include_path, $context, $offset, $length);
            }

            if (func_num_args() >= 4) {
                return \file_get_contents($filename, $use_include_path, $context, $offset);
            }

            if (func_num_args() >= 3) {
                return \file_get_contents($filename, $use_include_path, $context);
            }

            return \file_get_contents($filename, $use_include_path);
        }
    }
}

namespace Linkxtr\QrCode\Mergers {
    if (! isset($GLOBALS['mockImageColorAllocateAlpha'])) {
        $GLOBALS['mockImageColorAllocateAlpha'] = null;
    }

    if (! function_exists('Linkxtr\QrCode\Mergers\imagecolorallocatealpha')) {
        function imagecolorallocatealpha($image, $red, $green, $blue, $alpha)
        {
            if (isset($GLOBALS['mockImageColorAllocateAlpha']) && $GLOBALS['mockImageColorAllocateAlpha'] === false) {
                return false;
            }

            return \imagecolorallocatealpha($image, $red, $green, $blue, $alpha);
        }
    }

    if (! isset($GLOBALS['mockImageColorAllocate'])) {
        $GLOBALS['mockImageColorAllocate'] = null;
    }

    if (! function_exists('Linkxtr\QrCode\Mergers\imagecolorallocate')) {
        function imagecolorallocate($image, $red, $green, $blue)
        {
            if (isset($GLOBALS['mockImageColorAllocate']) && $GLOBALS['mockImageColorAllocate'] === false) {
                return false;
            }

            return \imagecolorallocate($image, $red, $green, $blue);
        }
    }

    if (! isset($GLOBALS['mockImageCreateTrueColor'])) {
        $GLOBALS['mockImageCreateTrueColor'] = null;
    }

    if (! function_exists('Linkxtr\QrCode\Mergers\imagecreatetruecolor')) {
        function imagecreatetruecolor($width, $height)
        {
            if (isset($GLOBALS['mockImageCreateTrueColor']) && $GLOBALS['mockImageCreateTrueColor'] === false) {
                return false;
            }

            return \imagecreatetruecolor($width, $height);
        }
    }

    if (! isset($GLOBALS['mockObGetClean'])) {
        $GLOBALS['mockObGetClean'] = null;
    }

    if (! function_exists('Linkxtr\QrCode\Mergers\ob_get_clean')) {
        function ob_get_clean()
        {
            if (isset($GLOBALS['mockObGetClean']) && $GLOBALS['mockObGetClean'] === false) {
                return false;
            }

            return \ob_get_clean();
        }
    }
}

namespace Linkxtr\QrCode\DataTypes {
    final class InvalidDataType
    {
        public function __construct() {}

        public function __toString(): string
        {
            return '';
        }

        public function create(array $arguments): void {}
    }
}

namespace Linkxtr\QrCode\Support {
    if (! isset($GLOBALS['mockImagesx'])) {
        $GLOBALS['mockImagesx'] = null;
    }

    if (! function_exists('Linkxtr\QrCode\Support\imagesx')) {
        function imagesx($image)
        {
            if (isset($GLOBALS['mockImagesx'])) {
                if ($GLOBALS['mockImagesx'] === false) {
                    return false;
                }
                if (is_callable($GLOBALS['mockImagesx'])) {
                    return ($GLOBALS['mockImagesx'])($image);
                }
                if (is_int($GLOBALS['mockImagesx'])) {
                    return $GLOBALS['mockImagesx'];
                }
            }

            return \imagesx($image);
        }
    }

    if (! isset($GLOBALS['mockImageCreateFromString'])) {
        $GLOBALS['mockImageCreateFromString'] = null;
    }

    if (! function_exists('Linkxtr\QrCode\Support\imagecreatefromstring')) {
        function imagecreatefromstring($data)
        {
            if (isset($GLOBALS['mockImageCreateFromString']) && $GLOBALS['mockImageCreateFromString'] === false) {
                return false;
            }

            return \imagecreatefromstring($data);
        }
    }

    if (! isset($GLOBALS['mockImagesy'])) {
        $GLOBALS['mockImagesy'] = null;
    }

    if (! function_exists('Linkxtr\QrCode\Support\imagesy')) {
        function imagesy($image)
        {
            if (isset($GLOBALS['mockImagesy'])) {
                if ($GLOBALS['mockImagesy'] === false) {
                    return false;
                }
                if (is_callable($GLOBALS['mockImagesy'])) {
                    return ($GLOBALS['mockImagesy'])($image);
                }
                if (is_int($GLOBALS['mockImagesy'])) {
                    return $GLOBALS['mockImagesy'];
                }
            }

            return \imagesy($image);
        }
    }
}

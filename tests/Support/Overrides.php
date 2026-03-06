<?php

declare(strict_types=1);

namespace Linkxtr\QrCode {
    if (! function_exists('Linkxtr\QrCode\base_path')) {
        function base_path($path = ''): string
        {
            $baseDiff = '/..';

            return $path === '' ? __DIR__.$baseDiff : __DIR__.$baseDiff.'/'.ltrim($path, '/');
        }
    }

    $GLOBALS['mockFilePutContents'] = false;

    if (! function_exists('Linkxtr\QrCode\file_put_contents')) {
        function file_put_contents($filename, $data, $flags = 0, $context = null): int|false
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

    $GLOBALS['mockFileGetContents'] = null;

    if (! function_exists('Linkxtr\QrCode\file_get_contents')) {
        function file_get_contents($filename, $use_include_path = false, $context = null, $offset = 0, $length = null): string|false
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
    use GdImage;

    if (! isset($GLOBALS['mockImageColorAllocateAlpha'])) {
        $GLOBALS['mockImageColorAllocateAlpha'] = null;
    }

    if (! function_exists('Linkxtr\QrCode\Mergers\imagecolorallocatealpha')) {
        /**
         * @param  GdImage  $image
         */
        function imagecolorallocatealpha($image, $red, $green, $blue, $alpha): int|false
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
        /**
         * @param  GdImage  $image
         */
        function imagecolorallocate($image, $red, $green, $blue): int|false
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
        function imagecreatetruecolor($width, $height): GdImage|false
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

    if (! function_exists('Linkxtr\QrCode\Mergers\imagefill')) {
        /**
         * @param  GdImage  $image
         */
        function imagefill($image, $x, $y, $color): bool
        {
            if (isset($GLOBALS['mockImageFill']) && $GLOBALS['mockImageFill'] === false) {
                return false;
            }

            return \imagefill($image, $x, $y, $color);
        }
    }

    if (! isset($GLOBALS['mockImageCopy'])) {
        $GLOBALS['mockImageCopy'] = null;
    }

    if (! function_exists('Linkxtr\QrCode\Mergers\imagecopy')) {
        /**
         * @param  GdImage  $dst_im
         * @param  GdImage  $src_im
         */
        function imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h): bool
        {
            if (isset($GLOBALS['mockImageCopy']) && $GLOBALS['mockImageCopy'] === false) {
                return false;
            }

            return \imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
        }
    }

    if (! isset($GLOBALS['mockImageCopyResampled'])) {
        $GLOBALS['mockImageCopyResampled'] = null;
    }

    if (! function_exists('Linkxtr\QrCode\Mergers\imagecopyresampled')) {
        /**
         * @param  GdImage  $dst_image
         * @param  GdImage  $src_image
         */
        function imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h): bool
        {
            if (isset($GLOBALS['mockImageCopyResampled']) && $GLOBALS['mockImageCopyResampled'] === false) {
                return false;
            }

            return \imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
        }
    }

    if (! isset($GLOBALS['mockImageSaveAlpha'])) {
        $GLOBALS['mockImageSaveAlpha'] = null;
    }

    if (! function_exists('Linkxtr\QrCode\Mergers\imagesavealpha')) {
        /**
         * @param  GdImage  $image
         */
        function imagesavealpha($image, $enable): bool
        {
            if (isset($GLOBALS['mockImageSaveAlpha']) && $GLOBALS['mockImageSaveAlpha'] === false) {
                return false;
            }

            return \imagesavealpha($image, $enable);
        }
    }

    if (! isset($GLOBALS['mockImagesx'])) {
        $GLOBALS['mockImagesx'] = null;
    }

    if (! function_exists('Linkxtr\QrCode\Mergers\imagesx')) {
        /**
         * @param  GdImage  $image
         */
        function imagesx($image): int|false
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

    if (! isset($GLOBALS['mockImagesy'])) {
        $GLOBALS['mockImagesy'] = null;
    }

    if (! function_exists('Linkxtr\QrCode\Mergers\imagesy')) {
        /**
         * @param  GdImage  $image
         */
        function imagesy($image): int|false
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
    use GdImage;

    if (! isset($GLOBALS['mockImagesx'])) {
        $GLOBALS['mockImagesx'] = null;
    }

    if (! function_exists('Linkxtr\QrCode\Support\imagesx')) {
        /**
         * @param  GdImage  $image
         * @return int<0, max>|false
         */
        function imagesx($image): int|false
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
        function imagecreatefromstring($data): GdImage|false
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
        /**
         * @param  GdImage  $image
         * @return int<0, max>|false
         */
        function imagesy($image): int|false
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

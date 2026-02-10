<?php

namespace Linkxtr\QrCode;

if (! function_exists('Linkxtr\QrCode\base_path')) {
    function base_path($path = '')
    {
        return __DIR__.'/'.$path;
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

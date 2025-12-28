<?php

namespace Linkxtr\QrCode;

if (! function_exists('Linkxtr\QrCode\base_path')) {
    function base_path($path = '')
    {
        return __DIR__.'/'.$path;
    }
}

$mockFilePutContents = false;

if (! function_exists('Linkxtr\QrCode\file_put_contents')) {
    function file_put_contents($filename, $data, $flags = 0, $context = null)
    {
        global $mockFilePutContents;

        if ($mockFilePutContents) {
            return false;
        }

        return \file_put_contents($filename, $data, $flags, $context);
    }
}

<?php

namespace Linkxtr\QrCode;

if (! function_exists('Linkxtr\QrCode\base_path')) {
    function base_path($path = '')
    {
        return __DIR__.'/'.$path;
    }
}

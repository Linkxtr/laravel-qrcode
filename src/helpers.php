<?php

use Linkxtr\QrCode\Generator;
use Illuminate\Support\HtmlString;
use Linkxtr\QrCode\Facades\QrCode as QrCodeFacade;

if (! function_exists('qrcode')) {
    function qrcode(?string $text = null): Generator|HtmlString
    {
        if ($text === null) {
            return app(Generator::class);
        }

        return QrCodeFacade::generate($text);
    }
}

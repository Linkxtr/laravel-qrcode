<?php

use Linkxtr\QrCode\Facades\QrCode as QrCodeFacade;
use Linkxtr\QrCode\Generator;

if (! function_exists('qrcode')) {
    function qrcode(?string $text = null): mixed
    {
        if ($text === null) {
            return app(Generator::class);
        }

        return QrCodeFacade::generate($text);
    }
}

<?php

use Linkxtr\QrCode\Facades\QrCode as QrCodeFacade;

if (! function_exists('qrcode')) {
    /**
     * Generate a QR code.
     */
    function qrcode(string $text = ''): string
    {
        return QrCodeFacade::generate($text);
    }
}

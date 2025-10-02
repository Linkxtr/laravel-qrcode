<?php

use Linkxtr\QrCode\Facades\QrCode as QrCodeFacade;
use Linkxtr\QrCode\QrCode;

if (! function_exists('qrcode')) {
    /**
     * Generate a QR code or get a new QrCode instance.
     *
     * @return QrCode|string
     */
    function qrcode(?string $text = null)
    {
        if ($text === null) {
            /** @var QrCode */
            return app('qrcode');
        }

        return QrCodeFacade::generate($text);
    }
}

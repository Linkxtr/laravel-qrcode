<?php

declare(strict_types=1);

use Linkxtr\QrCode\Facades\QrCode as QrCodeFacade;
use Linkxtr\QrCode\Generator;
use Linkxtr\QrCode\Support\QrCodeResult;

if (! function_exists('qrcode')) {
    /**
     * Get the QR Code generator instance or generate a QR code directly.
     *
     * @return ($text is null ? Generator : QrCodeResult)
     */
    function qrcode(?string $text = null): Generator|QrCodeResult
    {
        if ($text === null) {
            return app(Generator::class);
        }

        return QrCodeFacade::generate($text);
    }
}

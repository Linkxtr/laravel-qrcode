<?php

declare(strict_types=1);

use Illuminate\Support\HtmlString;
use Linkxtr\QrCode\Facades\QrCode as QrCodeFacade;
use Linkxtr\QrCode\Generator;

if (! function_exists('qrcode')) {
    /**
     * Get the QR Code generator instance or generate a QR code directly.
     *
     * @return ($text is null ? Generator : HtmlString)
     */
    function qrcode(?string $text = null): Generator|HtmlString
    {
        if ($text === null) {
            return app(Generator::class);
        }

        return QrCodeFacade::generate($text);
    }
}

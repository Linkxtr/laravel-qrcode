<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Format
    |--------------------------------------------------------------------------
    |
    | This option controls the default format that will be used when
    | generating QR codes.
    |
    | Supported: "png", "eps", "svg", "webp"
    | Note: As of v2.4.x, the default 'format' is now "svg" (previously "png").
    |
    */
    'format' => env('QR_CODE_FORMAT', 'svg'),

    /*
    |--------------------------------------------------------------------------
    | Default Size
    |--------------------------------------------------------------------------
    |
    | This option controls the default size of the QR code in pixels.
    | Note: As of v2.4.x, the default 'size' is now 400 (previously 200).
    |
    */
    'size' => (int) (env('QR_CODE_SIZE') ?? 400),

    /*
    |--------------------------------------------------------------------------
    | Default Margin
    |--------------------------------------------------------------------------
    |
    | This option controls the default margin around the QR code.
    |
    */
    'margin' => (int) (env('QR_CODE_MARGIN') ?? 4),

    /*
    |--------------------------------------------------------------------------
    | Default Color
    |--------------------------------------------------------------------------
    |
    | This option controls the default foreground color of the QR code.
    | Format: comma-separated string. Examples:
    | RGB (csv string): "R,G,B" (each 0-255) e.g. "0,0,0"
    | RGBA (csv string): "R,G,B,A" (each 0-255 and A 0-100) e.g. "0,0,0,100"
    | RGB (array): [R, G, B] (each 0-255) e.g. [0, 0, 0]
    | RGBA (array): [R, G, B, A] (each 0-255 and A 0-100) e.g. [0, 0, 0, 100]
    | Hex: "#RRGGBB" (each 0-F) e.g. "#000000"
    | Note: While PHP GD uses 0-127 for transparency, this package automatically scales the 0-100 alpha value.
    |
    */
    'color' => env('QR_CODE_COLOR', '0,0,0'),

    /*
    |--------------------------------------------------------------------------
    | Default Background Color
    |--------------------------------------------------------------------------
    |
    | This option controls the default background color of the QR code.
    | Format: comma-separated string. Examples:
    | RGB (csv string): "R,G,B" (each 0-255) e.g. "0,0,0"
    | RGBA (csv string): "R,G,B,A" (each 0-255 and A 0-100) e.g. "0,0,0,100"
    | RGB (array): [R, G, B] (each 0-255) e.g. [0, 0, 0]
    | RGBA (array): [R, G, B, A] (each 0-255 and A 0-100) e.g. [0, 0, 0, 100]
    | Hex: "#RRGGBB" (each 0-F) e.g. "#000000"
    | Note: While PHP GD uses 0-127 for transparency, this package automatically scales the 0-100 alpha value.
    |
    */
    'background_color' => env('QR_CODE_BACKGROUND_COLOR', '255,255,255'),

    /*
    |--------------------------------------------------------------------------
    | Error Correction Level
    |--------------------------------------------------------------------------
    |
    | This option controls the error correction level of the QR code.
    |
    | Supported: 'L', 'M', 'Q', 'H'
    | Note: As of v2.4.x, the default 'error_correction' is now 'M' (previously 'H').
    |
    */
    'error_correction' => env('QR_CODE_ERROR_CORRECTION', 'M'),

    /*
    |--------------------------------------------------------------------------
    | Encoding
    |--------------------------------------------------------------------------
    |
    | This option controls the character encoding of the QR code.
    |
    */
    'encoding' => env('QR_CODE_ENCODING', 'UTF-8'),

    /*
    |--------------------------------------------------------------------------
    | Force GD Backend
    |--------------------------------------------------------------------------
    |
    | If your server has Imagick installed but restricts its usage (e.g., via
    | strict security policies), you can force the package to bypass Imagick
    | and fall back to the GD extension for rendering raster images.
    |
    */
    'force_gd' => env('QRCODE_FORCE_GD', false),
];

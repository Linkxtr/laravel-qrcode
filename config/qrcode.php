<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Format
    |--------------------------------------------------------------------------
    |
    | This option controls the default format that will be used when
    | generating QR codes.
    |
    | Supported: "png", "eps", "svg"
    |
    */
    'format' => env('QR_CODE_FORMAT', 'png'),

    /*
    |--------------------------------------------------------------------------
    | Default Size
    |--------------------------------------------------------------------------
    |
    | This option controls the default size of the QR code in pixels.
    |
    */
    'size' => env('QR_CODE_SIZE', 200),

    /*
    |--------------------------------------------------------------------------
    | Default Margin
    |--------------------------------------------------------------------------
    |
    | This option controls the default margin around the QR code.
    |
    */
    'margin' => env('QR_CODE_MARGIN', 4),

    /*
    |--------------------------------------------------------------------------
    | Error Correction Level
    |--------------------------------------------------------------------------
    |
    | This option controls the error correction level of the QR code.
    |
    | Supported: 'L', 'M', 'Q', 'H'
    |
    */
    'error_correction' => env('QR_CODE_ERROR_CORRECTION', 'H'),

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
    | Merge Options
    |--------------------------------------------------------------------------
    |
    | These options control the default behavior when merging an image
    | with the QR code.
    |
    */
    'merge' => [
        'percentage' => env('QR_CODE_MERGE_PERCENTAGE', 0.2),
        'absolute' => env('QR_CODE_MERGE_ABSOLUTE', false),
    ],
];

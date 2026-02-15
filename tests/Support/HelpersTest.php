<?php

declare(strict_types=1);

use Illuminate\Support\HtmlString;
use Linkxtr\QrCode\Facades\QrCode;
use Linkxtr\QrCode\Generator;

it('returns generator instance when no arguments provided', function () {
    expect(qrcode())->toBeInstanceOf(Generator::class);
});

it('generates qrcode string when text argument provided', function () {
    QrCode::setFacadeApplication(app());
    $result = qrcode('test');
    expect($result)->toBeInstanceOf(HtmlString::class);
});

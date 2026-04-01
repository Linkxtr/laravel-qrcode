<?php

declare(strict_types=1);

use Illuminate\Support\HtmlString;
use Linkxtr\QrCode\Facades\QrCode;

it('can register and call custom macros that return a string payload', function () {
    QrCode::macro('spotify', function (string $uri) {
        return 'spotify:track:'.$uri;
    });

    $result = QrCode::spotify('4uLU6hMCjMI75M1A2tKUQC');

    expect($result)->toBeInstanceOf(HtmlString::class);

    // Generating an SVG with default settings should output the data
    expect((string) $result)->toContain('<svg', '</svg>');
})->after(function () {
    QrCode::flushMacros();
});

it('can register and call custom macros that return pre-styled generation', function () {
    QrCode::macro('assetTag', function (string $serialNumber) {
        $payload = json_encode(['type' => 'hardware', 'sn' => $serialNumber]);

        return $this->size(300)->margin(2)->generate($payload);
    });

    $result = QrCode::assetTag('SN-99812-X');

    expect($result)->toBeInstanceOf(HtmlString::class);

    expect((string) $result)->toContain('<svg', '</svg>');
})->after(function () {
    QrCode::flushMacros();
});

it('throws an exception for unsupported type returns from macro', function () {
    QrCode::macro('returnArray', function () {
        return ['an' => 'array'];
    });

    QrCode::returnArray();
})->throws(UnexpectedValueException::class)->after(function () {
    QrCode::flushMacros();
});

it('still delegates to data types if macro is not registered', function () {
    $result = QrCode::Email('test@example.com');

    expect($result)->toBeInstanceOf(HtmlString::class);
    expect((string) $result)->toContain('<svg');
});

it('throws bad method exception for completely non-existent methods', function () {
    QrCode::nonExistentMacro('test');
})->throws(BadMethodCallException::class);

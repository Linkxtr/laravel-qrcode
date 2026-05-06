<?php

declare(strict_types=1);

use Linkxtr\QrCode\Support\DataTypeResolver;

it('throws an exception for an unregistered method', function () {
    DataTypeResolver::resolve('unknownMethod', []);
})->throws(
    BadMethodCallException::class,
    'Method "unknownMethod" does not exist on the QrCode Generator. It is not a registered macro or a valid Data Type.'
);

it('resolves valid data types and executes them case-insensitively', function (string $method) {
    $result = DataTypeResolver::resolve($method, ['test@example.com']);

    expect($result)->toBe('mailto:test@example.com');
})->with([
    'email',
    'Email',
    'EMAIL',
    'eMaIl',
]);

it('correctly maps classes and passes complex arguments to the data type', function () {
    $result = DataTypeResolver::resolve('wifi', [
        [
            'ssid' => 'MyNetwork',
            'encryption' => 'WPA',
            'password' => 'secret123',
        ],
    ]);

    expect($result)->toBe('WIFI:S:MyNetwork;T:WPA;P:secret123;;');
});

it('can resolve all mapped data types without instantiating unknown classes', function () {
    $geo = DataTypeResolver::resolve('geo', [37.7749, -122.4194]);
    expect($geo)->toBe('geo:37.7749,-122.4194');

    $sms = DataTypeResolver::resolve('sms', ['+1234567890', 'Hello']);
    expect($sms)->toBe('sms:+1234567890?body=Hello');

    $ethereum = DataTypeResolver::resolve('ethereum', ['0x1234567890abcdef']);
    expect($ethereum)->toBe('ethereum:0x1234567890abcdef');
});

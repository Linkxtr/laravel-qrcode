<?php

declare(strict_types=1);

use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\Exceptions\DataTypes\GenericInvalidDataTypeArgumentException;
use Linkxtr\QrCode\Exceptions\UnknownMethodException;
use Linkxtr\QrCode\Support\DataTypeResolver;

covers(DataTypeResolver::class);

it('throws an exception for an unregistered method', function (): void {
    DataTypeResolver::resolve('unknownMethod', []);
})->throws(
    UnknownMethodException::class,
    'Method "unknownMethod" does not exist on the QrCode Generator.'
);

it('resolves valid data types and executes them case-insensitively', function (string $method): void {
    $result = DataTypeResolver::resolve($method, ['test@example.com']);

    expect($result)->toBe('mailto:test@example.com');
})->with([
    'email',
    'Email',
    'EMAIL',
    'eMaIl',
]);

it('correctly maps classes and passes complex arguments to the data type', function (): void {
    $result = DataTypeResolver::resolve('wifi', [
        [
            'ssid' => 'MyNetwork',
            'encryption' => 'WPA',
            'password' => 'secret123',
        ],
    ]);

    expect($result)->toBe('WIFI:S:MyNetwork;T:WPA;P:secret123;;');
});

it('can resolve all mapped data types without instantiating unknown classes', function (): void {
    $geo = DataTypeResolver::resolve('geo', [37.7749, -122.4194]);
    expect($geo)->toBe('geo:37.7749,-122.4194');

    $sms = DataTypeResolver::resolve('sms', ['+1234567890', 'Hello']);
    expect($sms)->toBe('sms:+1234567890?body=Hello');

    $ethereum = DataTypeResolver::resolve('ethereum', ['0x1234567890abcdef']);
    expect($ethereum)->toBe('ethereum:0x1234567890abcdef');
});

it('throws an exception if a resolved class does not implement DataTypeInterface', function (): void {
    final class BadDataType {}

    $reflection = new ReflectionClass(DataTypeResolver::class);
    $reflectionProperty = $reflection->getProperty('map');

    $reflectionProperty->setValue(null, ['badtype' => BadDataType::class]);

    expect(fn (): string => DataTypeResolver::resolve('badtype', []))->toThrow(UnknownMethodException::class, 'Data type class "BadDataType" must implement Linkxtr\QrCode\Contracts\DataTypeInterface.');

    $reflectionProperty->setValue(null, null);
});

it('dynamically scans the directory and builds the data type map correctly', function (): void {
    $reflection = new ReflectionClass(DataTypeResolver::class);
    $reflectionProperty = $reflection->getProperty('map');
    $reflectionProperty->setValue(null, null);

    $result = DataTypeResolver::resolve('email', ['test@example.com']);
    expect($result)->toContain('mailto:test@example.com');
});

it('throws an exception if the data type class has an invalid constructor', function (): void {
    final readonly class DummyDataType implements DataTypeInterface
    {
        public function __construct(private string $arg) {}

        public function __toString(): string
        {
            return 'dummy, '.$this->arg;
        }
    }

    $reflection = new ReflectionClass(DataTypeResolver::class);
    $reflectionProperty = $reflection->getProperty('map');
    $originalMap = $reflectionProperty->getValue();

    $reflectionProperty->setValue(null, ['dummy' => DummyDataType::class]);

    expect(fn (): string => DataTypeResolver::resolve('dummy', []))->toThrow(
        GenericInvalidDataTypeArgumentException::class
    );

    expect(fn (): string => DataTypeResolver::resolve('dummy', [null]))->toThrow(
        GenericInvalidDataTypeArgumentException::class
    );

    $reflectionProperty->setValue(null, $originalMap);
});

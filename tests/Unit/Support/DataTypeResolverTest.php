<?php

declare(strict_types=1);

namespace Tests\Unit\Support {

    use BadMethodCallException;
    use Linkxtr\QrCode\Support\DataTypeResolver;

    covers(DataTypeResolver::class);

    test('it correctly formats the class namespace', function () {
        expect(DataTypeResolver::formatClass('Email'))
            ->toBe('Linkxtr\QrCode\DataTypes\Email');
    });

    test('it resolves a valid data type and returns its string payload', function () {
        $result = DataTypeResolver::resolve('ValidDummyType', ['hello', 'world']);

        expect($result)->toBe('dummy:hello,world');
    });

    test('it throws exception if data type class does not exist', function () {
        expect(fn () => DataTypeResolver::resolve('GhostType', []))
            ->toThrow(
                BadMethodCallException::class,
                'Method "GhostType" does not exist on the QrCode Generator. It is not a registered macro or a valid Data Type.'
            );
    });

    test('it enforces strict case sensitivity for method calls', function () {
        expect(fn () => DataTypeResolver::resolve('validDummyType', []))
            ->toThrow(BadMethodCallException::class);
    });

    test('it throws exception if class exists but does not implement interface', function () {
        expect(fn () => DataTypeResolver::resolve('InvalidDummyType', []))
            ->toThrow(BadMethodCallException::class);
    });
}

namespace Linkxtr\QrCode\DataTypes {

    use Linkxtr\QrCode\Contracts\DataTypeInterface;

    final class ValidDummyType implements DataTypeInterface
    {
        private array $args = [];

        public function __toString(): string
        {
            return 'dummy:'.implode(',', $this->args);
        }

        public function create(array $payload): void
        {
            $this->args = $payload;
        }
    }

    // without interface
    final class InvalidDummyType {}
}

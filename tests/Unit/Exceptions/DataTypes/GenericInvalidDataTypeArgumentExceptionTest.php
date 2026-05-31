<?php

declare(strict_types=1);

use Linkxtr\QrCode\Exceptions\DataTypes\GenericInvalidDataTypeArgumentException;

covers(GenericInvalidDataTypeArgumentException::class);

test('missingArguments sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = GenericInvalidDataTypeArgumentException::missingArguments('test message');

    expect($invalidDataTypeArgumentException)->toBeInstanceOf(GenericInvalidDataTypeArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())->toBe('MISSING_ARGUMENTS')
        ->and($invalidDataTypeArgumentException->getMessage())->toBe('test message');
});

test('invalidArgument sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = GenericInvalidDataTypeArgumentException::invalidArgument('test message');

    expect($invalidDataTypeArgumentException)->toBeInstanceOf(GenericInvalidDataTypeArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())->toBe('INVALID_ARGUMENT')
        ->and($invalidDataTypeArgumentException->getMessage())->toBe('test message');
});

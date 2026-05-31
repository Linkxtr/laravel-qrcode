<?php

declare(strict_types=1);

use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\Exceptions\UnknownMethodException;

covers(UnknownMethodException::class);

test('methodNotFound sets correct error code and message', function (): void {
    $unknownMethodException = UnknownMethodException::methodNotFound('invalid_method');

    expect($unknownMethodException)->toBeInstanceOf(UnknownMethodException::class)
        ->and($unknownMethodException->getErrorCode())->toBe('UNKNOWN_METHOD')
        ->and($unknownMethodException->getMessage())->toBe('Method "invalid_method" does not exist on the QrCode Generator.');
});

test('dataTypeNotImplemented sets correct error code and message', function (): void {
    $unknownMethodException = UnknownMethodException::dataTypeNotImplemented('InvalidDataType');

    expect($unknownMethodException)->toBeInstanceOf(UnknownMethodException::class)
        ->and($unknownMethodException->getErrorCode())->toBe('UNKNOWN_DATA_TYPE')
        ->and($unknownMethodException->getHelperMessage())->toBe('Ensure the data type class implements '.DataTypeInterface::class.'.')
        ->and($unknownMethodException->getMessage())->toBe('Data type class "InvalidDataType" must implement '.DataTypeInterface::class.'.');
});

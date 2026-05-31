<?php

declare(strict_types=1);

use Linkxtr\QrCode\Exceptions\UninitializedDataTypeException;

covers(UninitializedDataTypeException::class);

test('forType sets correct error code and message', function (): void {
    $uninitializedDataTypeException = UninitializedDataTypeException::forType('invalid_type');

    expect($uninitializedDataTypeException)->toBeInstanceOf(UninitializedDataTypeException::class)
        ->and($uninitializedDataTypeException->getErrorCode())->toBe('UNINITIALIZED_DATA_TYPE')
        ->and($uninitializedDataTypeException->getMessage())->toBe('invalid_type must be initialized via create() before rendering.');
});

<?php

declare(strict_types=1);

use Linkxtr\QrCode\Exceptions\UnknownMethodException;

covers(UnknownMethodException::class);

test('methodNotFound sets correct error code and message', function (): void {
    $unknownMethodException = UnknownMethodException::methodNotFound('invalid_method');

    expect($unknownMethodException)->toBeInstanceOf(UnknownMethodException::class)
        ->and($unknownMethodException->getErrorCode())->toBe('UNKNOWN_METHOD')
        ->and($unknownMethodException->getMessage())->toBe('Method "invalid_method" does not exist on the QrCode Generator.');
});

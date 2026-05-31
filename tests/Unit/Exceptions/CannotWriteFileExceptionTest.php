<?php

declare(strict_types=1);

use Linkxtr\QrCode\Exceptions\CannotWriteFileException;

covers(CannotWriteFileException::class);

test('cannotWriteFile creates exception with correct error code and helper message', function (): void {
    $cannotWriteFileException = CannotWriteFileException::toPath(__DIR__);

    expect($cannotWriteFileException)
        ->toBeInstanceOf(CannotWriteFileException::class)
        ->and($cannotWriteFileException->getMessage())
        ->toBe('Failed to write QR code to file: '.__DIR__)
        ->and($cannotWriteFileException->getErrorCode())
        ->toBe('CANNOT_WRITE_FILE')
        ->and($cannotWriteFileException->getHelperMessage())
        ->toBe('Check if the destination directory exists and if the PHP process has the necessary write permissions to that path.');
});

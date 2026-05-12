<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Exceptions;

use Linkxtr\QrCode\Exceptions\Concerns\HasHelperMessage;
use RuntimeException;

final class CannotWriteFileException extends RuntimeException implements QrCodeExceptionInterface
{
    use HasHelperMessage;

    public static function toPath(string $filename): self
    {
        $exception = new self(sprintf('Failed to write QR code to file: %s', $filename));
        $exception->errorCode = 'CANNOT_WRITE_FILE';
        $exception->helperMessage = 'Check if the destination directory exists and if the PHP process has the necessary write permissions to that path.';

        return $exception;
    }
}

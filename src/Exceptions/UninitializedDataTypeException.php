<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Exceptions;

use Linkxtr\QrCode\Exceptions\Concerns\HasHelperMessage;
use LogicException;

final class UninitializedDataTypeException extends LogicException implements QrCodeExceptionInterface
{
    use HasHelperMessage;

    public static function forType(string $type): self
    {
        $exception = new self(sprintf('%s must be initialized via create() before rendering.', $type));
        $exception->errorCode = 'UNINITIALIZED_DATA_TYPE';
        $exception->helperMessage = 'You are trying to cast a DataType to string before initializing it. Ensure you pass arguments via create().';

        return $exception;
    }
}

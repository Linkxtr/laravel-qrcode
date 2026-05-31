<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Exceptions;

use BadMethodCallException;
use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\Contracts\QrCodeExceptionInterface;
use Linkxtr\QrCode\Exceptions\Concerns\HasHelperMessage;

final class UnknownMethodException extends BadMethodCallException implements QrCodeExceptionInterface
{
    use HasHelperMessage;

    public static function methodNotFound(string $method): self
    {
        $exception = new self(sprintf('Method "%s" does not exist on the QrCode Generator.', $method));
        $exception->errorCode = 'UNKNOWN_METHOD';
        $exception->helperMessage = 'Ensure the method is either a valid built-in Data Type (e.g., WiFi, Email) or a registered custom Macro.';

        return $exception;
    }

    public static function dataTypeNotImplemented(string $className): self
    {
        $exception = new self('Data type class "'.$className.'" must implement '.DataTypeInterface::class.'.');
        $exception->errorCode = 'UNKNOWN_DATA_TYPE';
        $exception->helperMessage = 'Ensure the data type class implements '.DataTypeInterface::class.'.';

        return $exception;
    }
}

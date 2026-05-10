<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Exceptions;

use InvalidArgumentException;
use Linkxtr\QrCode\Exceptions\Concerns\HasHelperMessage;

abstract class InvalidDataTypeArgumentException extends InvalidArgumentException implements QrCodeExceptionInterface
{
    use HasHelperMessage;

    final public function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function missingArguments(string $message): self
    {
        $static = new static($message);
        $static->errorCode = 'MISSING_ARGUMENTS';
        $static->helperMessage = $message;

        return $static;
    }

    public static function invalidArgument(string $message): self
    {
        $static = new static($message);
        $static->errorCode = 'INVALID_ARGUMENT';
        $static->helperMessage = $message;

        return $static;
    }
}

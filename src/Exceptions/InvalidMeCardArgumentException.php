<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Exceptions;

final class InvalidMeCardArgumentException extends InvalidDataTypeArgumentException
{
    public static function invalidNameType(string $type): self
    {
        if ($type === 'string') {
            $type = 'empty string';
        }

        $exception = new self(sprintf('MeCard name must be a non-empty string. Provided type: %s', $type));
        $exception->errorCode = 'INVALID_MECARD_NAME';
        $exception->helperMessage = 'Ensure the name is a non-empty string.';

        return $exception;
    }
}

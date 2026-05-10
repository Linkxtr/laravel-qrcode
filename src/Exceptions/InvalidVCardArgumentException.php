<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Exceptions;

final class InvalidVCardArgumentException extends InvalidDataTypeArgumentException
{
    public static function invalidNameType(string $type): self
    {
        if ($type === 'string') {
            $type = 'empty string';
        }

        $exception = new self(sprintf('VCard name must be a non-empty string. Provided type: %s', $type));
        $exception->errorCode = 'INVALID_VCARD_NAME';
        $exception->helperMessage = 'Ensure the VCard name is a non-empty string.';

        return $exception;
    }
}

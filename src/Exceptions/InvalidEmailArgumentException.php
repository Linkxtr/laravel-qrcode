<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Exceptions;

final class InvalidEmailArgumentException extends InvalidDataTypeArgumentException
{
    public static function invalidSubjectType(string $type): self
    {
        $exception = new self(sprintf('Email subject must be a string. Provided type: %s', $type));
        $exception->errorCode = 'INVALID_EMAIL_SUBJECT';
        $exception->helperMessage = 'Ensure the subject is a string.';

        return $exception;
    }

    public static function invalidBodyType(string $type): self
    {
        $exception = new self(sprintf('Email body must be a string. Provided type: %s', $type));
        $exception->errorCode = 'INVALID_EMAIL_BODY';
        $exception->helperMessage = 'Ensure the body is a string.';

        return $exception;
    }

    public static function invalidAddress(): self
    {
        $exception = new self('Invalid email address provided to Email.');
        $exception->errorCode = 'INVALID_EMAIL_ADDRESS';
        $exception->helperMessage = 'Ensure the address is a valid email address format.';

        return $exception;
    }
}

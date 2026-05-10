<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Exceptions;

final class InvalidTelegramArgumentException extends InvalidDataTypeArgumentException
{
    public static function invalidUsernameType(string $type): self
    {
        $exception = new self(sprintf('Telegram username must be a string. Provided type: %s', $type));
        $exception->errorCode = 'INVALID_USERNAME_TYPE';
        $exception->helperMessage = 'Ensure the Telegram username is a string.';

        return $exception;
    }

    public static function invalidUsername(): self
    {
        $exception = new self('Telegram username cannot be empty.');
        $exception->errorCode = 'INVALID_USERNAME';
        $exception->helperMessage = 'Ensure the Telegram username is not empty after removing leading `@` symbol and trimming whitespace.';

        return $exception;
    }
}

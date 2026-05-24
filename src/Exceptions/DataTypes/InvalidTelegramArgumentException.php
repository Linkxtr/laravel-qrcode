<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Exceptions\DataTypes;

use Linkxtr\QrCode\Exceptions\InvalidDataTypeArgumentException;

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
        $exception = new self('Invalid Telegram username format.');
        $exception->errorCode = 'INVALID_USERNAME';
        $exception->helperMessage = 'Ensure the Telegram username is 5–32 characters long, contains only alphanumeric characters and underscores, and starts with a letter.';

        return $exception;
    }
}

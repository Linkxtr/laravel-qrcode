<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\Exceptions\InvalidTelegramArgumentException;
use Linkxtr\QrCode\Exceptions\UninitializedDataTypeException;

final class Telegram implements DataTypeInterface
{
    private const PREFIX = 'https://t.me/';

    private string $username;

    public function __toString(): string
    {
        if (! isset($this->username)) {
            throw UninitializedDataTypeException::forType('Telegram');
        }

        return self::PREFIX.$this->username;
    }

    /**
     * @param  list<mixed>  $arguments
     */
    public function create(array $arguments): void
    {
        if (! isset($arguments[0])) {
            throw InvalidTelegramArgumentException::missingArguments('Telegram username is required.');
        }

        if (! is_string($arguments[0])) {
            throw InvalidTelegramArgumentException::invalidUsernameType(gettype($arguments[0]));
        }

        $username = ltrim(trim($arguments[0]), '@');

        if ($username === '') {
            throw InvalidTelegramArgumentException::invalidUsername();
        }

        $this->username = $username;
    }
}

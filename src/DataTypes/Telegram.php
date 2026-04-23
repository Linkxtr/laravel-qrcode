<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\DataTypeInterface;

final class Telegram implements DataTypeInterface
{
    private const PREFIX = 'https://t.me/';

    private string $username;

    public function __toString(): string
    {
        return self::PREFIX.$this->username;
    }

    /**
     * @param  list<mixed>  $arguments
     */
    public function create(array $arguments): void
    {
        if (! isset($arguments[0])) {
            throw new InvalidArgumentException('Telegram username is required.');
        }

        if (! is_string($arguments[0])) {
            throw new InvalidArgumentException('Telegram username must be a string.');
        }

        $username = ltrim(trim($arguments[0]), '@');

        if ($username === '') {
            throw new InvalidArgumentException('Telegram username cannot be empty.');
        }

        $this->username = $username;
    }
}

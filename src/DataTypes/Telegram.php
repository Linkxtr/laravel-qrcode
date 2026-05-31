<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidTelegramArgumentException;

final readonly class Telegram implements DataTypeInterface
{
    private const PREFIX = 'https://t.me/';

    private string $username;

    public function __construct(string $username)
    {
        $cleanedUsername = ltrim(trim($username), '@');

        if (! preg_match('/^[a-zA-Z]\w{4,31}$/', $cleanedUsername)) {
            throw InvalidTelegramArgumentException::invalidUsername();
        }

        $this->username = $cleanedUsername;
    }

    public function __toString(): string
    {
        return self::PREFIX.$this->username;
    }
}

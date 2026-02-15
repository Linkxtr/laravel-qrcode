<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\DataTypeInterface;

final class Telegram implements DataTypeInterface
{
    private ?string $username = null;

    public function __toString(): string
    {
        if (! $this->username) {
            throw new InvalidArgumentException('Telegram username is mandatory.');
        }

        return 'https://t.me/'.$this->username;
    }

    /**
     * @param  list<mixed>|array<string, mixed>  $arguments
     */
    public function create(array $arguments): void
    {
        $properties = $arguments;

        // Support positional arguments
        if (array_is_list($arguments)) {
            $properties = [];
            if (isset($arguments[0])) {
                $properties['username'] = $arguments[0];
            }
        }

        if (isset($properties['username']) && is_string($properties['username'])) {
            $this->username = $properties['username'];
        }
    }
}

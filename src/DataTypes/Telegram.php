<?php

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;

final class Telegram implements DataTypeInterface
{
    protected ?string $username = null;

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

    public function __toString(): string
    {
        if (! $this->username) {
            throw new InvalidArgumentException('Telegram username is mandatory.');
        }

        return 'https://t.me/'.$this->username;
    }
}

<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\DataTypeInterface;

final class WhatsApp implements DataTypeInterface
{
    private ?string $number = null;

    private ?string $message = null;

    public function __toString(): string
    {
        if (! $this->number) {
            throw new InvalidArgumentException('WhatsApp number is mandatory.');
        }

        $url = 'https://wa.me/'.$this->number;

        if ($this->message) {
            $url .= '?text='.urlencode($this->message);
        }

        return $url;
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
                $properties['number'] = $arguments[0];
            }
            if (isset($arguments[1])) {
                $properties['message'] = $arguments[1];
            }
        }

        if (isset($properties['number']) && is_string($properties['number'])) {
            $this->number = $properties['number'];
        }

        if (isset($properties['message']) && is_string($properties['message'])) {
            $this->message = $properties['message'];
        }
    }
}

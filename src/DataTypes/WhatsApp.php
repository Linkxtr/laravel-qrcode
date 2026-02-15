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
            throw new InvalidArgumentException('WhatsApp must be initialized via create() before rendering.');
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
        if (array_is_list($arguments)) {
            $arguments = [
                'number' => $arguments[0] ?? null,
                'message' => $arguments[1] ?? null,
            ];
        }

        if (! isset($arguments['number'])) {
            throw new InvalidArgumentException('WhatsApp number is mandatory.');
        }

        foreach (['number', 'message'] as $key) {
            if (isset($arguments[$key])) {
                if (! is_string($arguments[$key])) {
                    throw new InvalidArgumentException("WhatsApp {$key} must be a string.");
                }

                $this->{$key} = $arguments[$key];
            }
        }
    }
}

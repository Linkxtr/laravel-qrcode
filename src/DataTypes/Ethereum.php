<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\DataTypeInterface;

final class Ethereum implements DataTypeInterface
{
    private const PREFIX = 'ethereum:';

    private string $address;

    private ?float $amount = null;

    public function __toString(): string
    {
        if ($this->amount !== null) {
            return self::PREFIX.$this->address.'?'.http_build_query(['amount' => $this->amount], encoding_type: PHP_QUERY_RFC3986);
        }

        return self::PREFIX.$this->address;
    }

    /**
     * @param  list<mixed>  $arguments
     */
    public function create(array $arguments): void
    {
        if (! isset($arguments[0])) {
            throw new InvalidArgumentException('Ethereum address is required.');
        }

        if (! is_string($arguments[0])) {
            throw new InvalidArgumentException('Ethereum address must be a string.');
        }

        $this->address = $arguments[0];

        if (isset($arguments[1])) {
            if (! is_numeric($arguments[1])) {
                throw new InvalidArgumentException('Ethereum amount must be a numeric value.');
            }

            if ($arguments[1] < 0) {
                throw new InvalidArgumentException('Ethereum amount must be non-negative.');
            }

            $this->amount = (float) $arguments[1];
        }
    }
}

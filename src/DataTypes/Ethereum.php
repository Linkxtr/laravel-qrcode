<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidEthereumArgumentException;
use Linkxtr\QrCode\Exceptions\UninitializedDataTypeException;

final class Ethereum implements DataTypeInterface
{
    private const PREFIX = 'ethereum:';

    private string $address;

    private ?string $amount = null;

    public function __toString(): string
    {
        if (! isset($this->address)) {
            throw UninitializedDataTypeException::forType('Ethereum');
        }

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
            throw InvalidEthereumArgumentException::missingArguments('Ethereum address is required.');
        }

        if (! is_string($arguments[0]) || trim($arguments[0]) === '') {
            throw InvalidEthereumArgumentException::invalidAddress(gettype($arguments[0]));
        }

        $address = trim($arguments[0]);
        $amount = null;

        if (isset($arguments[1])) {
            if (is_bool($arguments[1]) || ! is_scalar($arguments[1])) {
                throw InvalidEthereumArgumentException::invalidAmountType(gettype($arguments[1]));
            }

            $amount = (string) $arguments[1];

            if (! preg_match('/^(0|[1-9]\d*)(\.\d+)?$/', $amount)) {
                throw InvalidEthereumArgumentException::invalidAmount($amount);
            }
        }

        $this->address = $address;
        $this->amount = $amount;
    }
}

<?php

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;

class BTC implements DataTypeInterface
{
    protected string $prefix = 'bitcoin:';

    protected string $address;

    protected float $amount;

    protected ?string $label = null;

    protected ?string $message = null;

    protected ?string $returnAddress = null;

    /**
     * @param  list<mixed>  $arguments
     */
    public function create(array $arguments): void
    {
        $this->setProperties($arguments);
    }

    public function __toString(): string
    {
        return $this->buildBitCoinString();
    }

    /**
     * @param  list<mixed>  $arguments
     */
    protected function setProperties(array $arguments): void
    {
        if (count($arguments) < 2) {
            throw new InvalidArgumentException('Bitcoin address and amount are required.');
        }

        if (! is_string($arguments[0])) {
            throw new InvalidArgumentException('Bitcoin address must be a string.');
        }

        if (! is_float($arguments[1])) {
            throw new InvalidArgumentException('Bitcoin amount must be a float.');
        }

        $this->address = $arguments[0];
        $this->amount = $arguments[1];

        if (isset($arguments[2]) && is_array($arguments[2])) {
            $this->setOptions($arguments[2]);
        }
    }

    /**
     * @param  array<mixed>  $options
     */
    protected function setOptions(array $options): void
    {
        if (isset($options['label']) && is_string($options['label'])) {
            $this->label = $options['label'];
        }

        if (isset($options['message']) && is_string($options['message'])) {
            $this->message = $options['message'];
        }

        if (isset($options['returnAddress']) && is_string($options['returnAddress'])) {
            $this->returnAddress = $options['returnAddress'];
        }
    }

    protected function buildBitCoinString(): string
    {
        $params = [
            'amount' => $this->amount,
            'label' => $this->label,
            'message' => $this->message,
            'r' => $this->returnAddress,
        ];

        return $this->prefix.$this->address.'?'.http_build_query($params);
    }
}

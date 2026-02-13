<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\DataTypeInterface;

final class BTC implements DataTypeInterface
{
    private string $prefix = 'bitcoin:';

    private string $address;

    private float $amount;

    private ?string $label = null;

    private ?string $message = null;

    private ?string $returnAddress = null;

    public function __toString(): string
    {
        return $this->buildBitCoinString();
    }

    /**
     * @param  list<mixed>  $arguments
     */
    public function create(array $arguments): void
    {
        $this->setProperties($arguments);
    }

    /**
     * @param  list<mixed>  $arguments
     */
    private function setProperties(array $arguments): void
    {
        if (count($arguments) < 2) {
            throw new InvalidArgumentException('Bitcoin address and amount are required.');
        }

        if (! is_string($arguments[0])) {
            throw new InvalidArgumentException('Bitcoin address must be a string.');
        }

        if (! is_numeric($arguments[1])) {
            throw new InvalidArgumentException('Bitcoin amount must be a numeric value.');
        }

        if ((float) $arguments[1] < 0) {
            throw new InvalidArgumentException('Bitcoin amount must be non-negative.');
        }

        $this->address = $arguments[0];
        $this->amount = (float) $arguments[1];

        if (isset($arguments[2]) && is_array($arguments[2])) {
            $this->setOptions($arguments[2]);
        }
    }

    /**
     * @param  array<mixed>  $options
     */
    private function setOptions(array $options): void
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

    private function buildBitCoinString(): string
    {
        $params = [
            'amount' => $this->amount,
            'label' => $this->label,
            'message' => $this->message,
            'r' => $this->returnAddress,
        ];

        $params = array_filter($params, fn (null|string|float $value): bool => $value !== null);

        return $this->prefix.$this->address.'?'.http_build_query($params);
    }
}

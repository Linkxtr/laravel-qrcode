<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\DataTypeInterface;
use LogicException;

final class BTC implements DataTypeInterface
{
    private const PREFIX = 'bitcoin:';

    private string $address;

    private ?string $amount = null;

    private ?string $label = null;

    private ?string $message = null;

    private ?string $returnAddress = null;

    public function __toString(): string
    {
        if (! isset($this->address)) {
            throw new LogicException('BTC must be initialized via create() before rendering.');
        }

        $params = [
            'amount' => $this->amount,
            'label' => $this->label,
            'message' => $this->message,
            'r' => $this->returnAddress,
        ];

        return self::PREFIX.$this->address.'?'.http_build_query($params, encoding_type: PHP_QUERY_RFC3986);
    }

    /**
     * @param  list<mixed>  $arguments
     */
    public function create(array $arguments): void
    {
        if (count($arguments) < 2) {
            throw new InvalidArgumentException('Bitcoin address and amount are required.');
        }

        if (! is_string($arguments[0]) || trim($arguments[0]) === '') {
            throw new InvalidArgumentException('Bitcoin address must be a non-empty string.');
        }

        if (! is_scalar($arguments[1])) {
            throw new InvalidArgumentException('Bitcoin amount must be a scalar value.');
        }

        if (is_bool($arguments[1])) {
            throw new InvalidArgumentException('Bitcoin amount cannot be a boolean.');
        }

        $amountStr = (string) $arguments[1];

        if (preg_match('/^\d+(\.\d+)?$/', $amountStr) !== 1) {
            throw new InvalidArgumentException('Bitcoin amount must be a positive decimal string. Small amounts must be passed as strings to avoid scientific notation loss.');
        }

        $this->address = trim($arguments[0]);
        $this->amount = $amountStr;

        $this->label = null;
        $this->message = null;
        $this->returnAddress = null;

        if (! isset($arguments[2]) || ! is_array($arguments[2])) {
            return;
        }

        $options = $arguments[2];

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
}

<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\Exceptions\InvalidBTCArgumentException;
use Linkxtr\QrCode\Exceptions\UninitializedDataTypeException;

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
            throw UninitializedDataTypeException::forType('BTC');
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
        unset($this->address);
        $this->amount = null;
        $this->label = null;
        $this->message = null;
        $this->returnAddress = null;

        if (count($arguments) < 2) {
            throw InvalidBTCArgumentException::missingArguments('Bitcoin address and amount are required.');
        }

        if (! is_string($arguments[0]) || trim($arguments[0]) === '') {
            throw InvalidBTCArgumentException::invalidAddress(gettype($arguments[0]));
        }

        if (! is_scalar($arguments[1]) || is_bool($arguments[1])) {
            throw InvalidBTCArgumentException::invalidAmountType(gettype($arguments[1]));
        }

        $amountStr = (string) $arguments[1];

        if (preg_match('/^\d+(\.\d+)?$/', $amountStr) !== 1) {
            throw InvalidBTCArgumentException::invalidAmount($amountStr);
        }

        $this->address = trim($arguments[0]);
        $this->amount = $amountStr;

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

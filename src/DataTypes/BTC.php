<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidBTCArgumentException;

final readonly class BTC implements DataTypeInterface
{
    private const PREFIX = 'bitcoin:';

    private string $amount;

    public function __construct(
        private string $address,
        float|string $amount,
        private ?string $label = null,
        private ?string $message = null,
        private ?string $returnAddress = null
    ) {
        $addressTrimmed = trim($this->address);
        if ($addressTrimmed === '') {
            throw InvalidBTCArgumentException::invalidAddress('string');
        }

        $amountStr = (string) $amount;
        if (preg_match('/^\d+(\.\d+)?$/', $amountStr) !== 1) {
            throw InvalidBTCArgumentException::invalidAmount($amountStr);
        }

        $this->amount = $amountStr;
    }

    public function __toString(): string
    {
        $address = trim($this->address);
        $params = [];

        $params['amount'] = $this->amount;

        if ($this->label !== null) {
            $params['label'] = $this->label;
        }

        if ($this->message !== null) {
            $params['message'] = $this->message;
        }

        if ($this->returnAddress !== null) {
            $params['r'] = $this->returnAddress;
        }

        return self::PREFIX.$address.'?'.http_build_query($params, encoding_type: PHP_QUERY_RFC3986);
    }
}

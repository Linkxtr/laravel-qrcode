<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidEthereumArgumentException;

final readonly class Ethereum implements DataTypeInterface
{
    private const PREFIX = 'ethereum:';

    private string $amount;

    public function __construct(
        private string $address,
        float|string|null $amount = null
    ) {
        $addressTrimmed = trim($this->address);
        if ($addressTrimmed === '') {
            throw InvalidEthereumArgumentException::invalidAddress('string');
        }

        if ($amount === null) {
            $amountStr = '';
        } else {
            $amountStr = (string) $amount;
            if (! preg_match('/^(0|[1-9]\d*)(\.\d+)?$/', $amountStr)) {
                throw InvalidEthereumArgumentException::invalidAmount($amountStr);
            }
        }

        $this->amount = $amountStr;
    }

    public function __toString(): string
    {
        $address = trim($this->address);

        if ($this->amount !== '') {
            return self::PREFIX.$address.'?'.http_build_query(['amount' => $this->amount], encoding_type: PHP_QUERY_RFC3986);
        }

        return self::PREFIX.$address;
    }
}

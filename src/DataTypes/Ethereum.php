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
            $amountStr = $this->normalizeAmount($amount);
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

    private function normalizeAmount(float|string $amount): string
    {
        if (is_float($amount)) {
            $amountStr = (string) $amount;
            if (preg_match('/^[+-]?\d+(?:\.\d+)?e[+-]?\d+$/i', $amountStr)) {
                return $this->convertScientificToDecimal($amountStr);
            }

            return $amountStr;
        }

        return $amount;
    }

    private function convertScientificToDecimal(string $str): string
    {
        $sign = '';
        if (str_starts_with($str, '-')) {
            $sign = '-';
            $str = substr($str, 1);
        }

        [$mantissa, $exponent] = explode('e', strtolower($str));
        $exp = (int) $exponent;
        $parts = explode('.', $mantissa);
        $intPart = $parts[0];
        $fracPart = $parts[1] ?? '';

        if ($exp > 0) {
            $result = $intPart.$fracPart.str_repeat('0', max(0, $exp - strlen($fracPart)));
        } else {
            $result = '0.'.str_repeat('0', abs($exp) - strlen($intPart)).$intPart.$fracPart;
        }

        if (str_contains($result, '.')) {
            $result = rtrim(rtrim($result, '0'), '.');
        }

        return $sign.($result === '' ? '0' : $result);
    }
}

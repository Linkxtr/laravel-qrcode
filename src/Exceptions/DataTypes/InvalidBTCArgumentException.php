<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Exceptions\DataTypes;

use Linkxtr\QrCode\Exceptions\InvalidDataTypeArgumentException;

final class InvalidBTCArgumentException extends InvalidDataTypeArgumentException
{
    public static function invalidAddress(string $type): self
    {
        if ($type === 'string') {
            $type = 'empty string';
        }

        $exception = new self(sprintf('Bitcoin address must be a non-empty string. Provided type: %s', $type));
        $exception->errorCode = 'INVALID_BITCOIN_ADDRESS';
        $exception->helperMessage = 'Ensure the address is a non-empty string. It should follow valid Bitcoin address formats (e.g., starts with 1, 3, or bc1).';

        return $exception;
    }

    public static function invalidAmount(string $amount): self
    {
        $exception = new self(sprintf('Bitcoin amount must be a positive decimal string. Provided value: %s', $amount));
        $exception->errorCode = 'INVALID_BITCOIN_AMOUNT';
        $exception->helperMessage = 'Bitcoin amounts must be valid, non-negative numeric strings. Small amounts must be passed as strings to avoid scientific notation loss.';

        return $exception;
    }

    public static function invalidAmountType(string $type): self
    {
        $exception = new self(sprintf('Bitcoin amount must be a positive decimal string. Provided type: %s', $type));
        $exception->errorCode = 'INVALID_BITCOIN_AMOUNT';
        $exception->helperMessage = 'Bitcoin amounts must be valid, non-negative numeric strings. Small amounts must be passed as strings to avoid scientific notation loss.';

        return $exception;
    }
}

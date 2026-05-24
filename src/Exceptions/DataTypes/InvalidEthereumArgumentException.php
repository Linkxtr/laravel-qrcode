<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Exceptions\DataTypes;

use Linkxtr\QrCode\Exceptions\InvalidDataTypeArgumentException;

final class InvalidEthereumArgumentException extends InvalidDataTypeArgumentException
{
    public static function invalidAddress(string $type): self
    {
        if ($type === 'string') {
            $type = 'empty string';
        }

        $exception = new self(sprintf('Ethereum address must be a non-empty string. Provided type: %s', $type));
        $exception->errorCode = 'INVALID_ETHEREUM_ADDRESS';
        $exception->helperMessage = 'Ensure the address is a non-empty string. It should follow valid Ethereum address formats (e.g., starts with 0x followed by 40 hexadecimal characters).';

        return $exception;
    }

    public static function invalidAmount(string $amount): self
    {
        $exception = new self(sprintf('Ethereum amount must be a valid, non-negative numeric string without scientific notation. Provided value: %s', $amount));
        $exception->errorCode = 'INVALID_ETHEREUM_AMOUNT';
        $exception->helperMessage = 'Ethereum amounts must be valid, non-negative numeric strings. Small amounts must be passed as strings to avoid scientific notation loss.';

        return $exception;
    }

    public static function invalidAmountType(string $type): self
    {
        $exception = new self(sprintf('Ethereum amount must be a positive decimal string. Provided type: %s', $type));
        $exception->errorCode = 'INVALID_ETHEREUM_AMOUNT';
        $exception->helperMessage = 'Ethereum amounts must be valid, non-negative numeric strings. Small amounts must be passed as strings to avoid scientific notation loss.';

        return $exception;
    }
}

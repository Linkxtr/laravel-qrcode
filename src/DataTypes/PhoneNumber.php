<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\DataTypes\Concerns\ValidatesPhoneNumbers;

final readonly class PhoneNumber implements DataTypeInterface
{
    use ValidatesPhoneNumbers;

    private const PREFIX = 'tel:';

    private string $phoneNumber;

    public function __construct(string|int|float $phoneNumber)
    {
        $this->phoneNumber = $this->validatePhoneNumber((string) $phoneNumber);
    }

    public function __toString(): string
    {
        return self::PREFIX.$this->phoneNumber;
    }
}

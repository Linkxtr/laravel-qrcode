<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\DataTypes\Concerns\ValidatesPhoneNumbers;

final class PhoneNumber implements DataTypeInterface
{
    use ValidatesPhoneNumbers;

    private const PREFIX = 'tel:';

    private string $phoneNumber;

    public function __toString(): string
    {
        return self::PREFIX.$this->phoneNumber;
    }

    /**
     * @param  list<mixed>  $arguments
     */
    public function create(array $arguments): void
    {
        if (! isset($arguments[0])) {
            throw new InvalidArgumentException('Phone number is required.');
        }

        if (! is_string($arguments[0]) && ! is_numeric($arguments[0])) {
            throw new InvalidArgumentException('Phone number must be a string or numeric value.');
        }

        $this->phoneNumber = $this->validatePhoneNumber((string) $arguments[0]);
    }
}

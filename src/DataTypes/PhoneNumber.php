<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\DataTypes\Concerns\ValidatesPhoneNumbers;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidPhoneNumberArgumentException;
use Linkxtr\QrCode\Exceptions\UninitializedDataTypeException;

final class PhoneNumber implements DataTypeInterface
{
    use ValidatesPhoneNumbers;

    private const PREFIX = 'tel:';

    private string $phoneNumber;

    public function __toString(): string
    {
        if (! isset($this->phoneNumber)) {
            throw UninitializedDataTypeException::forType('Phone number');
        }

        return self::PREFIX.$this->phoneNumber;
    }

    /**
     * @param  list<mixed>  $arguments
     */
    public function create(array $arguments): void
    {
        if (! isset($arguments[0])) {
            throw InvalidPhoneNumberArgumentException::missingArguments('Phone number is required.');
        }

        if (! is_string($arguments[0]) && ! is_numeric($arguments[0])) {
            throw InvalidPhoneNumberArgumentException::invalidPhoneNumberType(gettype($arguments[0]));
        }

        $this->phoneNumber = $this->validatePhoneNumber((string) $arguments[0]);
    }
}

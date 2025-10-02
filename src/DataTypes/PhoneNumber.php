<?php

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;
use Linkxtr\QrCode\DataTypes\DataTypeInterface;

class PhoneNumber implements DataTypeInterface
{
    protected string $prefix = 'tel:';

    protected ?string $phoneNumber = null;

    public function create(array $arguments): void
    {
        $this->setProperties($arguments);
    }

    protected function setProperties(array $arguments)
    {
        if (!isset($arguments[0])) {
            throw new InvalidArgumentException('Phone number is required.');
        }

        if (!is_string($arguments[0])) {
            throw new InvalidArgumentException('Phone number must be a string.');
        }

        $this->validatePhoneNumber($arguments[0]);
        $this->phoneNumber = $arguments[0];
    }

    public function __toString(): string
    {
        return $this->buildPhoneNumberString();
    }

    protected function validatePhoneNumber(string $phoneNumber): void
    {
        $cleaned = preg_replace('/[^\d+]/', '', $phoneNumber);
        
        if (!preg_match('/^\+?[0-9]{1,15}$/', $cleaned)) {
            throw new InvalidArgumentException('Invalid phone number format. Must be 1-15 digits, optionally starting with +');
        }
    }

    protected function buildPhoneNumberString(): string
    {
        return $this->prefix . $this->phoneNumber;
    }
}

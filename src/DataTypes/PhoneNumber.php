<?php

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;
use Linkxtr\QrCode\DataTypes\Concerns\ValidatesPhoneNumbers;

class PhoneNumber implements DataTypeInterface
{
    use ValidatesPhoneNumbers;

    protected string $prefix = 'tel:';

    protected ?string $phoneNumber = null;

    /**
     * @param  list<mixed>  $arguments
     */
    public function create(array $arguments): void
    {
        $this->setProperties($arguments);
    }

    /**
     * @param  list<mixed>  $arguments
     */
    protected function setProperties(array $arguments): void
    {
        if (! isset($arguments[0])) {
            throw new InvalidArgumentException('Phone number is required.');
        }

        if (! is_string($arguments[0])) {
            throw new InvalidArgumentException('Phone number must be a string.');
        }

        $this->validatePhoneNumber($arguments[0]);
        $this->phoneNumber = $arguments[0];
    }

    public function __toString(): string
    {
        return $this->buildPhoneNumberString();
    }

    protected function buildPhoneNumberString(): string
    {
        return $this->prefix.$this->phoneNumber;
    }
}

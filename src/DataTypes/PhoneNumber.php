<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\DataTypes\Concerns\ValidatesPhoneNumbers;
use LogicException;

final class PhoneNumber implements DataTypeInterface
{
    use ValidatesPhoneNumbers;

    protected string $prefix = 'tel:';

    protected ?string $phoneNumber = null;

    public function __toString(): string
    {
        return $this->buildPhoneNumberString();
    }

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

    protected function buildPhoneNumberString(): string
    {
        if ($this->phoneNumber === null) {
            throw new LogicException('Phone number is required. Call create() before using this object.');
        }

        return $this->prefix.$this->phoneNumber;
    }
}

<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\DataTypes\Concerns\ValidatesPhoneNumbers;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidSMSArgumentException;
use Linkxtr\QrCode\Exceptions\UninitializedDataTypeException;

final class SMS implements DataTypeInterface
{
    use ValidatesPhoneNumbers;

    private const PREFIX = 'sms:';

    private string $phoneNumber;

    private ?string $message = null;

    public function __toString(): string
    {
        if (! isset($this->phoneNumber)) {
            throw UninitializedDataTypeException::forType('SMS');
        }

        $uri = self::PREFIX.$this->phoneNumber;

        if ($this->message !== null) {
            return $uri.'?'.http_build_query(['body' => $this->message], encoding_type: PHP_QUERY_RFC3986);
        }

        return $uri;
    }

    /**
     * @param  list<mixed>  $arguments
     */
    public function create(array $arguments): void
    {
        if (! isset($arguments[0])) {
            throw InvalidSMSArgumentException::missingArguments('SMS phone number is required.');
        }

        if (! is_string($arguments[0]) && ! is_int($arguments[0])) {
            throw InvalidSMSArgumentException::invalidPhoneNumberType(gettype($arguments[0]));
        }

        $this->phoneNumber = $this->validatePhoneNumber((string) $arguments[0]);
        $this->message = null;

        if (array_key_exists(1, $arguments)) {
            if (! is_string($arguments[1])) {
                throw InvalidSMSArgumentException::invalidMessageType(gettype($arguments[1]));
            }

            if ($arguments[1] !== '') {
                $this->message = $arguments[1];
            }
        }
    }
}

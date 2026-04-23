<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\DataTypes\Concerns\ValidatesPhoneNumbers;

final class SMS implements DataTypeInterface
{
    use ValidatesPhoneNumbers;

    private const PREFIX = 'sms:';

    private string $phoneNumber;

    private ?string $message = null;

    public function __toString(): string
    {
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
            throw new InvalidArgumentException('SMS phone number is required.');
        }

        if (! is_string($arguments[0]) && ! is_numeric($arguments[0])) {
            throw new InvalidArgumentException('SMS phone number must be a string or numeric value.');
        }

        $this->phoneNumber = $this->validatePhoneNumber((string) $arguments[0]);
        $this->message = null;

        if (isset($arguments[1])) {
            if (! is_string($arguments[1])) {
                throw new InvalidArgumentException('SMS message must be a string.');
            }

            if ($arguments[1] !== '') {
                $this->message = $arguments[1];
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\DataTypes\Concerns\ValidatesPhoneNumbers;
use Linkxtr\QrCode\Exceptions\InvalidWhatsAppArgumentException;
use Linkxtr\QrCode\Exceptions\UninitializedDataTypeException;

final class WhatsApp implements DataTypeInterface
{
    use ValidatesPhoneNumbers;

    private const PREFIX = 'https://wa.me/';

    private string $phoneNumber;

    private ?string $message = null;

    public function __toString(): string
    {
        if (! isset($this->phoneNumber)) {
            throw UninitializedDataTypeException::forType('WhatsApp');
        }

        $uri = self::PREFIX.$this->phoneNumber;

        if ($this->message !== null) {
            return $uri.'?'.http_build_query(['text' => $this->message], encoding_type: PHP_QUERY_RFC3986);
        }

        return $uri;
    }

    /**
     * @param  list<mixed>  $arguments
     */
    public function create(array $arguments): void
    {
        if (! isset($arguments[0])) {
            throw InvalidWhatsAppArgumentException::missingArguments('WhatsApp phone number is required.');
        }

        if (! is_scalar($arguments[0])) {
            throw InvalidWhatsAppArgumentException::invalidPhoneNumberType(gettype($arguments[0]));
        }

        $rawNumber = trim((string) $arguments[0]);

        if ($rawNumber === '') {
            throw InvalidWhatsAppArgumentException::emptyPhoneNumber();
        }

        $this->phoneNumber = ltrim($this->validatePhoneNumber($rawNumber), '+');

        if (isset($arguments[1])) {
            if (! is_string($arguments[1])) {
                throw InvalidWhatsAppArgumentException::invalidMessageType(gettype($arguments[1]));
            }

            if ($arguments[1] !== '') {
                $this->message = $arguments[1];
            }
        }
    }
}

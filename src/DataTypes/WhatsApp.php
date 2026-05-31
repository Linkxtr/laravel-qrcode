<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\DataTypes\Concerns\ValidatesPhoneNumbers;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidWhatsAppArgumentException;

final readonly class WhatsApp implements DataTypeInterface
{
    use ValidatesPhoneNumbers;

    private const PREFIX = 'https://wa.me/';

    private string $phoneNumber;

    public function __construct(
        string|int|float $phoneNumber,
        private ?string $message = null
    ) {
        $rawNumber = trim((string) $phoneNumber);

        if ($rawNumber === '') {
            throw InvalidWhatsAppArgumentException::emptyPhoneNumber();
        }

        $this->phoneNumber = ltrim($this->validatePhoneNumber($rawNumber), '+');
    }

    public function __toString(): string
    {
        $uri = self::PREFIX.$this->phoneNumber;

        if ($this->message !== null && $this->message !== '') {
            return $uri.'?'.http_build_query(['text' => $this->message], encoding_type: PHP_QUERY_RFC3986);
        }

        return $uri;
    }
}

<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\DataTypes\Concerns\ValidatesPhoneNumbers;

final readonly class SMS implements DataTypeInterface
{
    use ValidatesPhoneNumbers;

    private const PREFIX = 'sms:';

    private string $phoneNumber;

    public function __construct(
        string|int|float $phoneNumber,
        private ?string $message = null
    ) {
        $this->phoneNumber = $this->validatePhoneNumber((string) $phoneNumber);
    }

    public function __toString(): string
    {
        $uri = self::PREFIX.$this->phoneNumber;

        if ($this->message !== null && $this->message !== '') {
            return $uri.'?'.http_build_query(['body' => $this->message], encoding_type: PHP_QUERY_RFC3986);
        }

        return $uri;
    }
}

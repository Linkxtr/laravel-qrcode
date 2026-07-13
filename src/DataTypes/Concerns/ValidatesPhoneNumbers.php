<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes\Concerns;

use Linkxtr\QrCode\Exceptions\DataTypes\InvalidPhoneNumberArgumentException;

trait ValidatesPhoneNumbers
{
    protected function validatePhoneNumber(string $phoneNumber): string
    {
        if (! preg_match('/^\+?[\d\s().-]+$/', $phoneNumber)) {
            throw InvalidPhoneNumberArgumentException::invalidPhoneNumberFormat();
        }

        $cleaned = (string) preg_replace('/[^\d+]/', '', $phoneNumber);

        if (! preg_match('/^\+?\d{1,15}$/', $cleaned)) {
            throw InvalidPhoneNumberArgumentException::invalidPhoneNumberLength();
        }

        return $cleaned;
    }
}

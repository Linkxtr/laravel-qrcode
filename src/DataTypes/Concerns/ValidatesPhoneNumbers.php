<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes\Concerns;

use InvalidArgumentException;

trait ValidatesPhoneNumbers
{
    protected function validatePhoneNumber(string $phoneNumber): string
    {
        if (! preg_match('/^\+?[\d\s().-]+$/', $phoneNumber)) {
            throw new InvalidArgumentException('Phone number contains invalid characters. Only digits, spaces, hyphens, parentheses, dots, and a leading plus are allowed.');
        }

        $cleaned = (string) preg_replace('/[^\d+]/', '', $phoneNumber); // @pest-mutate-ignore

        if (! preg_match('/^\+?\d{1,15}$/', $cleaned)) {
            throw new InvalidArgumentException('Invalid phone number length. Must be 1-15 digits, optionally starting with +');
        }

        return $cleaned;
    }
}

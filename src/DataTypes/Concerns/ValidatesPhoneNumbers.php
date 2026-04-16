<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes\Concerns;

use InvalidArgumentException;

trait ValidatesPhoneNumbers
{
    protected function validatePhoneNumber(string $phoneNumber): string
    {
        $cleaned = (string) preg_replace('/[^\d+]/', '', $phoneNumber);

        if (! preg_match('/^\+?\d{1,15}$/', $cleaned)) {
            throw new InvalidArgumentException('Invalid phone number format. Must be 1-15 digits, optionally starting with +');
        }

        return $cleaned;
    }
}

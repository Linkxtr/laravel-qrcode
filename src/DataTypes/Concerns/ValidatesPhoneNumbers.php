<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes\Concerns;

use InvalidArgumentException;

trait ValidatesPhoneNumbers
{
    protected function validatePhoneNumber(string $phoneNumber): void
    {
        $cleaned = preg_replace('/[^\d+]/', '', $phoneNumber);

        if (empty($cleaned) || ! preg_match('/^\+?[0-9]{1,15}$/', $cleaned)) {
            throw new InvalidArgumentException('Invalid phone number format. Must be 1-15 digits, optionally starting with +');
        }
    }
}

<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Exceptions;

final class InvalidPhoneNumberArgumentException extends InvalidDataTypeArgumentException
{
    public static function invalidPhoneNumberType(string $type): self
    {
        if ($type === 'string') {
            $type = 'empty string';
        }

        $exception = new self(sprintf('Phone number must be a string or numeric value. Provided type: %s', $type));
        $exception->errorCode = 'INVALID_PHONE_NUMBER_TYPE';
        $exception->helperMessage = 'Ensure the phone number is a non-empty string or numeric value.';

        return $exception;
    }

    public static function invalidPhoneNumberFormat(): self
    {
        $exception = new self('Phone number contains invalid characters. Only digits, spaces, hyphens, parentheses, dots, and a leading plus are allowed.');
        $exception->errorCode = 'INVALID_PHONE_NUMBER_FORMAT';
        $exception->helperMessage = 'Ensure the phone number contains only valid characters (digits, spaces, hyphens, parentheses, dots, and a leading plus).';

        return $exception;
    }

    public static function invalidPhoneNumberLength(): self
    {
        $exception = new self('Invalid phone number length. Must be 1-15 digits, optionally starting with +');
        $exception->errorCode = 'INVALID_PHONE_NUMBER_LENGTH';
        $exception->helperMessage = 'Ensure the phone number has 1-15 digits and optionally starts with a plus sign.';

        return $exception;
    }
}

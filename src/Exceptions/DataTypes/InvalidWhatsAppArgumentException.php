<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Exceptions\DataTypes;

use Linkxtr\QrCode\Exceptions\InvalidDataTypeArgumentException;

final class InvalidWhatsAppArgumentException extends InvalidDataTypeArgumentException
{
    public static function invalidPhoneNumberType(string $type): self
    {
        if ($type === 'string') {
            $type = 'empty string';
        }

        $exception = new self(sprintf('WhatsApp phone number must be a string or numeric value. Provided type: %s', $type));
        $exception->errorCode = 'INVALID_PHONE_NUMBER_TYPE';
        $exception->helperMessage = 'Ensure the phone number is a non-empty string or numeric value.';

        return $exception;
    }

    public static function invalidMessageType(string $type): self
    {
        $exception = new self(sprintf('WhatsApp message must be a string. Provided type: %s', $type));
        $exception->errorCode = 'INVALID_MESSAGE_TYPE';
        $exception->helperMessage = 'Ensure the message is a non-empty string.';

        return $exception;
    }

    public static function emptyPhoneNumber(): self
    {
        $exception = new self('WhatsApp phone number cannot be empty.');
        $exception->errorCode = 'EMPTY_PHONE_NUMBER';
        $exception->helperMessage = 'Ensure the phone number is a non-empty string or numeric value.';

        return $exception;
    }
}

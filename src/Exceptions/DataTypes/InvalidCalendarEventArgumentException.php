<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Exceptions\DataTypes;

use Linkxtr\QrCode\Exceptions\InvalidDataTypeArgumentException;

final class InvalidCalendarEventArgumentException extends InvalidDataTypeArgumentException
{
    public static function invalidSummary(string $type): self
    {
        if ($type === 'string') {
            $type = 'empty string';
        }

        $exception = new self(sprintf('The summary must be a non-empty string. Type provided: %s', $type));
        $exception->errorCode = 'INVALID_SUMMARY';
        $exception->helperMessage = 'The summary must be a non-empty string.';

        return $exception;
    }

    public static function endDateMustBeAfterStartDate(): self
    {
        $exception = new self('The end date must be after the start date.');
        $exception->errorCode = 'END_DATE_MUST_BE_AFTER_START_DATE';
        $exception->helperMessage = 'Ensure the end date is chronologically after the start date.';

        return $exception;
    }

    public static function invalidDate(string $type): self
    {
        $exception = new self(sprintf('The date must be a string, numeric, or DateTimeInterface. Type provided: %s', $type));
        $exception->errorCode = 'INVALID_DATE';
        $exception->helperMessage = 'The date must be a string, numeric, or DateTimeInterface.';

        return $exception;
    }
}

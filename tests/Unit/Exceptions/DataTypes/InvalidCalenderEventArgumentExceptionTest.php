<?php

declare(strict_types=1);

use Linkxtr\QrCode\Exceptions\DataTypes\InvalidCalendarEventArgumentException;

covers(InvalidCalendarEventArgumentException::class);

test('invalidSummary sets correct error code and message', function (): void {
    $invalidCalendarEventArgumentException = InvalidCalendarEventArgumentException::invalidSummary('string');

    expect($invalidCalendarEventArgumentException)->toBeInstanceOf(InvalidCalendarEventArgumentException::class)
        ->and($invalidCalendarEventArgumentException->getMessage())->toBe('The summary must be a non-empty string. Type provided: empty string')
        ->and($invalidCalendarEventArgumentException->getErrorCode())->toBe('INVALID_SUMMARY')
        ->and($invalidCalendarEventArgumentException->getHelperMessage())->toBe('The summary must be a non-empty string.');

    $invalidCalendarEventArgumentException = InvalidCalendarEventArgumentException::invalidSummary('invalid_type');

    expect($invalidCalendarEventArgumentException)->toBeInstanceOf(InvalidCalendarEventArgumentException::class)
        ->and($invalidCalendarEventArgumentException->getMessage())->toBe('The summary must be a non-empty string. Type provided: invalid_type');
});

test('endDateMustBeAfterStartDate sets correct error code and message', function (): void {
    $invalidCalendarEventArgumentException = InvalidCalendarEventArgumentException::endDateMustBeAfterStartDate();

    expect($invalidCalendarEventArgumentException)->toBeInstanceOf(InvalidCalendarEventArgumentException::class)
        ->and($invalidCalendarEventArgumentException->getErrorCode())->toBe('END_DATE_MUST_BE_AFTER_START_DATE')
        ->and($invalidCalendarEventArgumentException->getHelperMessage())->toBe('Ensure the end date is chronologically after the start date.');
});

test('invalidDate sets correct error code and message', function (): void {
    $invalidCalendarEventArgumentException = InvalidCalendarEventArgumentException::invalidDate('test');

    expect($invalidCalendarEventArgumentException)->toBeInstanceOf(InvalidCalendarEventArgumentException::class)
        ->and($invalidCalendarEventArgumentException->getErrorCode())->toBe('INVALID_DATE')
        ->and($invalidCalendarEventArgumentException->getHelperMessage())->toBe('The date must be a string, numeric, or DateTimeInterface.');
});

test('missingArguments sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = InvalidCalendarEventArgumentException::missingArguments('test');

    expect($invalidDataTypeArgumentException)->toBeInstanceOf(InvalidCalendarEventArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())->toBe('MISSING_ARGUMENTS')
        ->and($invalidDataTypeArgumentException->getHelperMessage())->toBe('test');
});

test('invalidArgument sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = InvalidCalendarEventArgumentException::invalidArgument('test');

    expect($invalidDataTypeArgumentException)->toBeInstanceOf(InvalidCalendarEventArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())->toBe('INVALID_ARGUMENT')
        ->and($invalidDataTypeArgumentException->getHelperMessage())->toBe('test');
});

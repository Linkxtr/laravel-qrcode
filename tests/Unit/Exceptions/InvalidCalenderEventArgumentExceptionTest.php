<?php

declare(strict_types=1);

use Linkxtr\QrCode\Exceptions\DataTypes\InvalidCalenderEventArgumentException;

covers(InvalidCalenderEventArgumentException::class);

test('invalidSummary sets correct error code and message', function (): void {
    $invalidCalenderEventArgumentException = InvalidCalenderEventArgumentException::invalidSummary('string');

    expect($invalidCalenderEventArgumentException)->toBeInstanceOf(InvalidCalenderEventArgumentException::class)
        ->and($invalidCalenderEventArgumentException->getMessage())->toBe('The summary must be a non-empty string. Type provided: empty string')
        ->and($invalidCalenderEventArgumentException->getErrorCode())->toBe('INVALID_SUMMARY')
        ->and($invalidCalenderEventArgumentException->getHelperMessage())->toBe('The summary must be a non-empty string.');

    $invalidCalenderEventArgumentException = InvalidCalenderEventArgumentException::invalidSummary('invalid_type');

    expect($invalidCalenderEventArgumentException)->toBeInstanceOf(InvalidCalenderEventArgumentException::class)
        ->and($invalidCalenderEventArgumentException->getMessage())->toBe('The summary must be a non-empty string. Type provided: invalid_type');
});

test('endDateMustBeAfterStartDate sets correct error code and message', function (): void {
    $invalidCalenderEventArgumentException = InvalidCalenderEventArgumentException::endDateMustBeAfterStartDate();

    expect($invalidCalenderEventArgumentException)->toBeInstanceOf(InvalidCalenderEventArgumentException::class)
        ->and($invalidCalenderEventArgumentException->getErrorCode())->toBe('END_DATE_MUST_BE_AFTER_START_DATE')
        ->and($invalidCalenderEventArgumentException->getHelperMessage())->toBe('Ensure the end date is chronologically after the start date.');
});

test('invalidDate sets correct error code and message', function (): void {
    $invalidCalenderEventArgumentException = InvalidCalenderEventArgumentException::invalidDate('test');

    expect($invalidCalenderEventArgumentException)->toBeInstanceOf(InvalidCalenderEventArgumentException::class)
        ->and($invalidCalenderEventArgumentException->getErrorCode())->toBe('INVALID_DATE')
        ->and($invalidCalenderEventArgumentException->getHelperMessage())->toBe('The date must be a string, numeric, or DateTimeInterface.');
});

test('missingArguments sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = InvalidCalenderEventArgumentException::missingArguments('test');

    expect($invalidDataTypeArgumentException)->toBeInstanceOf(InvalidCalenderEventArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())->toBe('MISSING_ARGUMENTS')
        ->and($invalidDataTypeArgumentException->getHelperMessage())->toBe('test');
});

test('invalidArgument sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = InvalidCalenderEventArgumentException::invalidArgument('test');

    expect($invalidDataTypeArgumentException)->toBeInstanceOf(InvalidCalenderEventArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())->toBe('INVALID_ARGUMENT')
        ->and($invalidDataTypeArgumentException->getHelperMessage())->toBe('test');
});

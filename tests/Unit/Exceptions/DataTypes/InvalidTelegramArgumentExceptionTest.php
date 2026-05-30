<?php

declare(strict_types=1);

use Linkxtr\QrCode\Exceptions\DataTypes\InvalidTelegramArgumentException;

covers(InvalidTelegramArgumentException::class);

test('invalidUsernameType sets correct error code and message', function (): void {
    $invalidTelegramArgumentException = InvalidTelegramArgumentException::invalidUsernameType('string');

    expect($invalidTelegramArgumentException)->toBeInstanceOf(InvalidTelegramArgumentException::class)
        ->and($invalidTelegramArgumentException->getErrorCode())->toBe('INVALID_USERNAME_TYPE')
        ->and($invalidTelegramArgumentException->getHelperMessage())->toBe('Ensure the Telegram username is a string.');
});

test('invalidUsername sets correct error code and message', function (): void {
    $invalidTelegramArgumentException = InvalidTelegramArgumentException::invalidUsername();

    expect($invalidTelegramArgumentException)->toBeInstanceOf(InvalidTelegramArgumentException::class)
        ->and($invalidTelegramArgumentException->getErrorCode())->toBe('INVALID_USERNAME')
        ->and($invalidTelegramArgumentException->getHelperMessage())->toBe('Ensure the Telegram username is 5–32 characters long, contains only alphanumeric characters and underscores, and starts with a letter.');
});

test('missingArguments sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = InvalidTelegramArgumentException::missingArguments('test');

    expect($invalidDataTypeArgumentException)->toBeInstanceOf(InvalidTelegramArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())->toBe('MISSING_ARGUMENTS')
        ->and($invalidDataTypeArgumentException->getHelperMessage())->toBe('test');
});

test('invalidArgument sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = InvalidTelegramArgumentException::invalidArgument('test');

    expect($invalidDataTypeArgumentException)->toBeInstanceOf(InvalidTelegramArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())->toBe('INVALID_ARGUMENT')
        ->and($invalidDataTypeArgumentException->getHelperMessage())->toBe('test');
});

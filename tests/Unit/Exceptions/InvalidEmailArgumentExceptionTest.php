<?php

declare(strict_types=1);

use Linkxtr\QrCode\Exceptions\InvalidEmailArgumentException;

covers(InvalidEmailArgumentException::class);

test('invalidSubjectType sets correct error code and message', function (): void {
    $invalidEmailArgumentException = InvalidEmailArgumentException::invalidSubjectType('test');

    expect($invalidEmailArgumentException)->toBeInstanceOf(InvalidEmailArgumentException::class)
        ->and($invalidEmailArgumentException->getErrorCode())->toBe('INVALID_EMAIL_SUBJECT')
        ->and($invalidEmailArgumentException->getHelperMessage())->toBe('Ensure the subject is a string.');
});

test('invalidBodyType sets correct error code and message', function (): void {
    $invalidEmailArgumentException = InvalidEmailArgumentException::invalidBodyType('test');

    expect($invalidEmailArgumentException)->toBeInstanceOf(InvalidEmailArgumentException::class)
        ->and($invalidEmailArgumentException->getErrorCode())->toBe('INVALID_EMAIL_BODY')
        ->and($invalidEmailArgumentException->getHelperMessage())->toBe('Ensure the body is a string.');
});

test('invalidAddress sets correct error code and message', function (): void {
    $invalidEmailArgumentException = InvalidEmailArgumentException::invalidAddress();

    expect($invalidEmailArgumentException)->toBeInstanceOf(InvalidEmailArgumentException::class)
        ->and($invalidEmailArgumentException->getErrorCode())->toBe('INVALID_EMAIL_ADDRESS')
        ->and($invalidEmailArgumentException->getHelperMessage())->toBe('Ensure the address is a valid email address format.');
});

test('missingArguments sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = InvalidEmailArgumentException::missingArguments('test');

    expect($invalidDataTypeArgumentException)->toBeInstanceOf(InvalidEmailArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())->toBe('MISSING_ARGUMENTS')
        ->and($invalidDataTypeArgumentException->getHelperMessage())->toBe('test');
});

test('invalidArgument sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = InvalidEmailArgumentException::invalidArgument('test');

    expect($invalidDataTypeArgumentException)->toBeInstanceOf(InvalidEmailArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())->toBe('INVALID_ARGUMENT')
        ->and($invalidDataTypeArgumentException->getHelperMessage())->toBe('test');
});

<?php

declare(strict_types=1);

use Linkxtr\QrCode\Exceptions\DataTypes\InvalidMeCardArgumentException;

covers(InvalidMeCardArgumentException::class);

test('invalidNameType sets correct error code and message', function (): void {
    $invalidMeCardArgumentException = InvalidMeCardArgumentException::invalidNameType('string');

    expect($invalidMeCardArgumentException)->toBeInstanceOf(InvalidMeCardArgumentException::class)
        ->and($invalidMeCardArgumentException->getMessage())->toBe('MeCard name must be a non-empty string. Provided type: empty string')
        ->and($invalidMeCardArgumentException->getErrorCode())->toBe('INVALID_MECARD_NAME')
        ->and($invalidMeCardArgumentException->getHelperMessage())->toBe('Ensure the name is a non-empty string.');

    $invalidMeCardArgumentException = InvalidMeCardArgumentException::invalidNameType('invalid_type');

    expect($invalidMeCardArgumentException)->toBeInstanceOf(InvalidMeCardArgumentException::class)
        ->and($invalidMeCardArgumentException->getMessage())->toBe('MeCard name must be a non-empty string. Provided type: invalid_type');
});

test('missingArguments sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = InvalidMeCardArgumentException::missingArguments('test');

    expect($invalidDataTypeArgumentException)->toBeInstanceOf(InvalidMeCardArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())->toBe('MISSING_ARGUMENTS')
        ->and($invalidDataTypeArgumentException->getHelperMessage())->toBe('test');
});

test('invalidArgument sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = InvalidMeCardArgumentException::invalidArgument('test');

    expect($invalidDataTypeArgumentException)->toBeInstanceOf(InvalidMeCardArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())->toBe('INVALID_ARGUMENT')
        ->and($invalidDataTypeArgumentException->getHelperMessage())->toBe('test');
});

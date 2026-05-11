<?php

declare(strict_types=1);

use Linkxtr\QrCode\Exceptions\InvalidDataTypeArgumentException;

covers(InvalidDataTypeArgumentException::class);

test('can instantiate InvalidDataTypeArgumentException via missingArguments', function (): void {
    $exception = new class('test') extends InvalidDataTypeArgumentException {};
    $invalidDataTypeArgumentException = $exception::missingArguments('Missing args');
    expect($invalidDataTypeArgumentException)->toBeInstanceOf(InvalidDataTypeArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())->toBe('MISSING_ARGUMENTS')
        ->and($invalidDataTypeArgumentException->getHelperMessage())->toBe('Missing args')
        ->and($invalidDataTypeArgumentException->getMessage())->toBe('Missing args');
});

test('can instantiate InvalidDataTypeArgumentException via invalidArgument', function (): void {
    $exception = new class('test') extends InvalidDataTypeArgumentException {};
    $invalidDataTypeArgumentException = $exception::invalidArgument('Invalid arg');
    expect($invalidDataTypeArgumentException)->toBeInstanceOf(InvalidDataTypeArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())->toBe('INVALID_ARGUMENT')
        ->and($invalidDataTypeArgumentException->getHelperMessage())->toBe('Invalid arg')
        ->and($invalidDataTypeArgumentException->getMessage())->toBe('Invalid arg');
});

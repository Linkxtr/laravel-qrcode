<?php

declare(strict_types=1);

use Linkxtr\QrCode\Exceptions\InvalidEnvironmentMutationException;

covers(InvalidEnvironmentMutationException::class);

test('restrictedToTests returns an exception with the correct message', function (): void {
    $invalidEnvironmentMutationException = InvalidEnvironmentMutationException::restrictedToTests();

    expect($invalidEnvironmentMutationException)
        ->toBeInstanceOf(InvalidEnvironmentMutationException::class)
        ->and($invalidEnvironmentMutationException->getMessage())->toBe('Environment extension mocking is strictly reserved for testing environments. Do not call these methods in production.');
});

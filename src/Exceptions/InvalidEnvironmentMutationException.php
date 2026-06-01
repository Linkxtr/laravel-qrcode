<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Exceptions;

use Linkxtr\QrCode\Contracts\QrCodeExceptionInterface;
use Linkxtr\QrCode\Exceptions\Concerns\HasHelperMessage;
use RuntimeException;

final class InvalidEnvironmentMutationException extends RuntimeException implements QrCodeExceptionInterface
{
    use HasHelperMessage;

    public static function restrictedToTests(): self
    {
        return new self('Environment extension mocking is strictly reserved for testing environments. Do not call these methods in production.');
    }
}

<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Contracts;

use Throwable;

/**
 * Interface QrCodeExceptionInterface
 * This interface is used to tag all exceptions thrown by the QrCode package.
 * It allows consumers to easily catch all package-specific exceptions.
 */
interface QrCodeExceptionInterface extends Throwable
{
    /**
     * Get a developer-friendly helper message to assist in resolving the issue.
     */
    public function getHelperMessage(): string;

    /**
     * Get a unique string-based error code for programmatic handling.
     */
    public function getErrorCode(): string;
}

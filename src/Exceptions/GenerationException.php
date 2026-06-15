<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Exceptions;

use Exception;
use Linkxtr\QrCode\Contracts\QrCodeExceptionInterface;
use Linkxtr\QrCode\Exceptions\Concerns\HasHelperMessage;

final class GenerationException extends Exception implements QrCodeExceptionInterface
{
    use HasHelperMessage;

    public static function invalidSvgString(): self
    {
        $exception = new self('Generated QR code SVG is corrupted or invalid.');
        $exception->errorCode = 'INVALID_SVG_STRING';
        $exception->helperMessage = 'Invalid SVG string provided.';

        return $exception;
    }
}

<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Exceptions;

use Linkxtr\QrCode\Exceptions\Concerns\HasHelperMessage;
use UnexpectedValueException;

final class InvalidMacroReturnTypeException extends UnexpectedValueException implements QrCodeExceptionInterface
{
    use HasHelperMessage;

    public static function invalidType(string $method, string $actualType): self
    {
        $exception = new self(sprintf(
            'Macro "%s" must return a string, Stringable, or HtmlString. %s returned.',
            $method,
            $actualType
        ));

        $exception->errorCode = 'INVALID_MACRO_RETURN_TYPE';
        $exception->helperMessage = 'Ensure your registered macro in the AppServiceProvider returns a plain string payload or a fully generated HtmlString instance from $this->generate().';

        return $exception;
    }
}

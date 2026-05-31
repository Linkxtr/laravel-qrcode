<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Exceptions\Concerns;

trait HasHelperMessage
{
    protected string $helperMessage = '';

    protected string $errorCode = 'UNKNOWN_ERROR';

    public function getHelperMessage(): string
    {
        return $this->helperMessage;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}

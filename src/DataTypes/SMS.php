<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\DataTypes\Concerns\ValidatesPhoneNumbers;

final class SMS implements DataTypeInterface
{
    use ValidatesPhoneNumbers;

    protected string $prefix = 'SMSTO:';

    protected string $separator = ':';

    protected ?string $smsAddress = null;

    protected ?string $message = null;

    public function __toString(): string
    {
        return $this->buildSMSString();
    }

    /**
     * @param  list<mixed>  $arguments
     */
    public function create(array $arguments): void
    {
        $this->setProperties($arguments);
    }

    /**
     * @param  list<mixed>  $arguments
     */
    protected function setProperties(array $arguments): void
    {
        if (! isset($arguments[0]) && ! isset($arguments[1])) {
            throw new InvalidArgumentException('Either SMS address or message is required.');
        }

        if (isset($arguments[0])) {
            if (! is_string($arguments[0])) {
                throw new InvalidArgumentException('SMS address must be a string.');
            }

            if ($arguments[0] === '') {
                throw new InvalidArgumentException('SMS address cannot be empty.');
            }

            $this->validatePhoneNumber($arguments[0]);
            $this->smsAddress = $arguments[0];
        }

        if (isset($arguments[1]) && is_string($arguments[1])) {
            $this->message = $arguments[1];
        }
    }

    protected function buildSMSString(): string
    {
        $sms = $this->prefix.($this->smsAddress ?? '');

        if (isset($this->message)) {
            $sms .= $this->separator.rawurlencode($this->message);
        }

        return $sms;
    }
}

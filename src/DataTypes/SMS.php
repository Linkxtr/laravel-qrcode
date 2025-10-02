<?php

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;
use Linkxtr\QrCode\DataTypes\DataTypeInterface;

class SMS implements DataTypeInterface
{
    protected string $prefix = 'sms:';

    protected string $separator = '&body=';

    protected ?string $smsAddress = null;

    protected ?string $message = null;

    public function create(array $arguments): void
    {
        $this->setProperties($arguments);
    }

    protected function setProperties(array $arguments)
    {
        if (empty($arguments[0]) && empty($arguments[1])) {
            throw new InvalidArgumentException('Either SMS address or message is required.');
        }

        if (isset($arguments[0])) {
            if (!is_string($arguments[0]) && !is_null($arguments[0])) {
                throw new InvalidArgumentException('SMS address must be a string.');
            }
            
            if ($arguments[0] !== null) {
                $this->validatePhoneNumber($arguments[0]);
                $this->smsAddress = $arguments[0];
            }
        }

        if (isset($arguments[1])) {
            $this->message = $arguments[1];
        }
    }

    protected function validatePhoneNumber(string $phoneNumber): void
    {       
        $cleaned = preg_replace('/[^\d+]/', '', $phoneNumber);
        
        if (!preg_match('/^\+?[0-9]{1,15}$/', $cleaned)) {
            throw new InvalidArgumentException('Invalid SMS address format. Must be 1-15 digits, optionally starting with +');
        }
    }

    public function __toString(): string
    {
        return $this->buildSMSString();
    }

    protected function buildSMSString(): string
    {
        $sms = $this->prefix . ($this->smsAddress ?? '');

        if (isset($this->message)) {
            $sms .= $this->separator . $this->message;
        }

        return $sms;
    }
}

<?php

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;
use Linkxtr\QrCode\DataTypes\DataTypeInterface;

class Email implements DataTypeInterface
{
    protected string $prefix = 'mailto:';

    protected ?string $address = null;

    protected ?string $subject = null;

    protected ?string $body = null;

    protected ?string $cc = null;

    protected ?string $bcc = null;

    public function create(array $arguments): void
    {
        $this->setProperties($arguments);       
    }
    
    protected function setProperties(array $arguments)
    {
        if (isset($arguments[0])) {
            $this->address = $this->setAddress($arguments[0]);
        }

        if (isset($arguments[1])) {
            $this->subject = $arguments[1];
        }

        if (isset($arguments[2])) {
            $this->body = $arguments[2];
        }

        if (isset($arguments[3])) {
            $this->cc = $arguments[3];
        }

        if (isset($arguments[4])) {
            $this->bcc = $arguments[4];
        }
    }

    public function __toString(): string
    {
        return $this->buildEmailString();
    }

    protected function buildEmailString(): string
    {
        $query = http_build_query([
            'subject' => $this->subject,
            'body'    => $this->body,
            'cc'      => $this->cc,
            'bcc'     => $this->bcc,
        ]);

        if (empty($query)) {
            return $this->prefix.$this->address;
        }

        return $this->prefix.$this->address.'?'.$query;
    }

    protected function setAddress(string $address): string
    {
        if (!filter_var($address, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address provided to Email.');
        }

        return $address;
    }
}
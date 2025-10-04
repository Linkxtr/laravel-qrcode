<?php

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;

class Email implements DataTypeInterface
{
    protected string $prefix = 'mailto:';

    protected ?string $address = null;

    protected ?string $subject = null;

    protected ?string $body = null;

    protected ?string $cc = null;

    protected ?string $bcc = null;

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
        if (isset($arguments[0]) && is_string($arguments[0])) {
            $this->address = $this->setAddress($arguments[0]);
        }

        if (isset($arguments[1]) && is_string($arguments[1])) {
            $this->subject = $arguments[1];
        }

        if (isset($arguments[2]) && is_string($arguments[2])) {
            $this->body = $arguments[2];
        }

        if (isset($arguments[3]) && is_string($arguments[3])) {
            $this->cc = $this->setAddress($arguments[3]);
        }

        if (isset($arguments[4]) && is_string($arguments[4])) {
            $this->bcc = $this->setAddress($arguments[4]);
        }
    }

    public function __toString(): string
    {
        return $this->buildEmailString();
    }

    protected function buildEmailString(): string
    {
        $params = array_filter([
            'subject' => $this->subject,
            'body' => $this->body,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
        ]);

        if (empty($params)) {
            return $this->prefix.$this->address;
        }

        return $this->prefix.$this->address.'?'.http_build_query($params);
    }

    protected function setAddress(string $address): string
    {
        if (! filter_var($address, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address provided to Email.');
        }

        return $address;
    }
}

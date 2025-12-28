<?php

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;

class Email implements DataTypeInterface
{
    protected string $prefix = 'mailto:';

    protected ?string $address = null;

    protected string $subject;

    protected string $body;

    protected string $cc;

    protected string $bcc;

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
        if (isset($arguments[0])) {
            $this->address = $this->setAddress($arguments[0]);
        }

        if (isset($arguments[1]) && is_string($arguments[1])) {
            $this->subject = $arguments[1];
        }

        if (isset($arguments[2])) {
            if (! is_string($arguments[2])) {
                throw new InvalidArgumentException('Invalid body provided to Email.');
            }
            $this->body = $arguments[2];
        }

        if (isset($arguments[3])) {
            $this->cc = $this->setAddress($arguments[3]);
        }

        if (isset($arguments[4])) {
            $this->bcc = $this->setAddress($arguments[4]);
        }
    }

    public function __toString(): string
    {
        return $this->buildEmailString();
    }

    protected function buildEmailString(): string
    {
        $params = [];

        if (isset($this->subject) && $this->subject !== '') {
            $params['subject'] = $this->subject;
        }

        if (isset($this->body) && $this->body !== '') {
            $params['body'] = $this->body;
        }

        if (isset($this->cc) && $this->cc !== '') {
            $params['cc'] = $this->cc;
        }

        if (isset($this->bcc) && $this->bcc !== '') {
            $params['bcc'] = $this->bcc;
        }

        if (empty($params)) {
            return $this->prefix.$this->address;
        }

        return $this->prefix.$this->address.'?'.http_build_query($params);
    }

    protected function setAddress(mixed $address): string
    {
        if (! filter_var($address, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address provided to Email.');
        }

        /** @var string $address */
        return $address;
    }
}

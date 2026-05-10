<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\Exceptions\InvalidEmailArgumentException;
use Linkxtr\QrCode\Exceptions\UninitializedDataTypeException;

final class Email implements DataTypeInterface
{
    private const PREFIX = 'mailto:';

    private string $address;

    private ?string $subject = null;

    private ?string $body = null;

    private ?string $cc = null;

    private ?string $bcc = null;

    public function __toString(): string
    {
        if (! isset($this->address)) {
            throw UninitializedDataTypeException::forType('Email');
        }

        $params = [];

        if ($this->subject !== null) {
            $params['subject'] = $this->subject;
        }

        if ($this->body !== null) {
            $params['body'] = $this->body;
        }

        if ($this->cc !== null) {
            $params['cc'] = $this->cc;
        }

        if ($this->bcc !== null) {
            $params['bcc'] = $this->bcc;
        }

        if ($params === []) {
            return self::PREFIX.$this->address;
        }

        return self::PREFIX.$this->address.'?'.http_build_query($params, encoding_type: PHP_QUERY_RFC3986);
    }

    /**
     * @param  list<mixed>  $arguments
     */
    public function create(array $arguments): void
    {
        if (! isset($arguments[0])) {
            throw InvalidEmailArgumentException::missingArguments('Email address is required.');
        }

        $this->address = $this->validateAddress($arguments[0]);

        if (isset($arguments[1])) {
            if (! is_string($arguments[1])) {
                throw InvalidEmailArgumentException::invalidSubjectType(gettype($arguments[1]));
            }

            if ($arguments[1] !== '') {
                $this->subject = $arguments[1];
            }
        }

        if (isset($arguments[2])) {
            if (! is_string($arguments[2])) {
                throw InvalidEmailArgumentException::invalidBodyType(gettype($arguments[2]));
            }

            if ($arguments[2] !== '') {
                $this->body = $arguments[2];
            }
        }

        if (isset($arguments[3]) && $arguments[3] !== '') {
            $this->cc = $this->validateAddress($arguments[3]);
        }

        if (isset($arguments[4]) && $arguments[4] !== '') {
            $this->bcc = $this->validateAddress($arguments[4]);
        }
    }

    private function validateAddress(mixed $address): string
    {
        if (! filter_var($address, FILTER_VALIDATE_EMAIL)) {
            throw InvalidEmailArgumentException::invalidAddress();
        }

        /** @var string $address */
        return $address;
    }
}

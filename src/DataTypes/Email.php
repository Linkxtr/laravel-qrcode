<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidEmailArgumentException;

final readonly class Email implements DataTypeInterface
{
    private const PREFIX = 'mailto:';

    public function __construct(
        private string $address,
        private ?string $subject = null,
        private ?string $body = null,
        private ?string $cc = null,
        private ?string $bcc = null
    ) {
        if (filter_var($this->address, FILTER_VALIDATE_EMAIL) === false) {
            throw InvalidEmailArgumentException::invalidAddress();
        }

        if ($this->cc !== null && filter_var($this->cc, FILTER_VALIDATE_EMAIL) === false) {
            throw InvalidEmailArgumentException::invalidAddress();
        }

        if ($this->bcc !== null && filter_var($this->bcc, FILTER_VALIDATE_EMAIL) === false) {
            throw InvalidEmailArgumentException::invalidAddress();
        }
    }

    public function __toString(): string
    {
        $params = [];

        if ($this->subject !== null && $this->subject !== '') {
            $params['subject'] = $this->subject;
        }

        if ($this->body !== null && $this->body !== '') {
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
}

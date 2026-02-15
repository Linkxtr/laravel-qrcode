<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\DataTypeInterface;

final class VCard implements DataTypeInterface
{
    private string $name;

    private ?string $firstName = null;

    private ?string $lastName = null;

    private ?string $email = null;

    private ?string $phone = null;

    private ?string $company = null;

    private ?string $title = null;

    private ?string $url = null;

    public function __toString(): string
    {
        $vCard = "BEGIN:VCARD\r\nVERSION:3.0\r\n";

        $vCard .= "FN:{$this->escapeValue($this->name)}\r\n";
        $vCard .= "N:{$this->escapeValue($this->lastName ?? '')};{$this->escapeValue($this->firstName ?? '')};;;\r\n";

        if ($this->email) {
            $vCard .= "EMAIL:{$this->escapeValue($this->email)}\r\n";
        }

        if ($this->phone) {
            $vCard .= "TEL:{$this->escapeValue($this->phone)}\r\n";
        }

        if ($this->company) {
            $vCard .= "ORG:{$this->escapeValue($this->company)}\r\n";
        }

        if ($this->title) {
            $vCard .= "TITLE:{$this->escapeValue($this->title)}\r\n";
        }

        if ($this->url) {
            $vCard .= "URL:{$this->escapeValue($this->url)}\r\n";
        }

        return $vCard.'END:VCARD';
    }

    /**
     * @param  array<int, mixed>  $arguments
     */
    public function create(array $arguments): void
    {
        $properties = $arguments;

        if (isset($arguments[0]) && is_array($arguments[0])) {
            $properties = $arguments[0];
        }

        if (! isset($properties['name']) || ! is_string($properties['name'])) {
            throw new InvalidArgumentException('vCard FN (Formatted Name) is mandatory.');
        }

        $this->name = $properties['name'];

        if (isset($properties['first_name']) && is_string($properties['first_name'])) {
            $this->firstName = $properties['first_name'];
        }

        if (isset($properties['last_name']) && is_string($properties['last_name'])) {
            $this->lastName = $properties['last_name'];
        }

        if (isset($properties['email']) && is_string($properties['email'])) {
            if (! filter_var($properties['email'], FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Invalid email address provided to vCard.');
            }
            $this->email = $properties['email'];
        }

        if (isset($properties['phone']) && is_string($properties['phone'])) {
            $this->phone = $properties['phone'];
        }

        if (isset($properties['company']) && is_string($properties['company'])) {
            $this->company = $properties['company'];
        }

        if (isset($properties['title']) && is_string($properties['title'])) {
            $this->title = $properties['title'];
        }

        if (isset($properties['url']) && is_string($properties['url'])) {
            if (! filter_var($properties['url'], FILTER_VALIDATE_URL)) {
                throw new InvalidArgumentException('Invalid URL provided to vCard.');
            }

            $this->url = $properties['url'];
        }
    }

    private function escapeValue(string $value): string
    {
        return strtr($value, [
            '\\' => '\\\\',
            ',' => '\\,',
            ';' => '\\;',
            "\n" => '\\n',
            "\r" => '',
        ]);
    }
}

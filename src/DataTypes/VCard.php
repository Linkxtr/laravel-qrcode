<?php

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;

final class VCard implements DataTypeInterface
{
    protected ?string $name = null;

    protected ?string $email = null;

    protected ?string $phone = null;

    protected ?string $company = null;

    protected ?string $title = null;

    protected ?string $url = null;

    /**
     * @param  array<int, mixed>  $arguments
     */
    public function create(array $arguments): void
    {
        $properties = $arguments;

        if (isset($arguments[0]) && is_array($arguments[0])) {
            $properties = $arguments[0];
        }

        if (isset($properties['name']) && is_string($properties['name'])) {
            $this->name = $properties['name'];
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
            $this->url = $properties['url'];
        }
    }

    protected function escapeValue(string $value): string
    {
        return strtr($value, [
            '\\' => '\\\\',
            ',' => '\\,',
            ';' => '\\;',
            "\n" => '\\n',
            "\r" => '',
        ]);
    }

    public function __toString(): string
    {
        $vCard = "BEGIN:VCARD\r\nVERSION:3.0\r\n";

        if ($this->name) {
            $vCard .= "FN:{$this->escapeValue($this->name)}\r\n";
        }

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

        $vCard .= 'END:VCARD';

        return $vCard;
    }
}

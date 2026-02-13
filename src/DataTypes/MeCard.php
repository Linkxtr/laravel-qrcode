<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\DataTypeInterface;

final class MeCard implements DataTypeInterface
{
    private string $name;

    private ?string $reading = null;

    private ?string $phone = null;

    private ?string $email = null;

    private ?string $note = null;

    private ?string $birthday = null;

    private ?string $address = null;

    private ?string $url = null;

    public function __toString(): string
    {
        $meCard = 'MECARD:';

        $meCard .= 'N:'.$this->escapeValue($this->name).';';

        if ($this->reading) {
            $meCard .= 'SOUND:'.$this->escapeValue($this->reading).';';
        }

        if ($this->phone) {
            $meCard .= 'TEL:'.$this->escapeValue($this->phone).';';
        }

        if ($this->email) {
            $meCard .= 'EMAIL:'.$this->escapeValue($this->email).';';
        }

        if ($this->note) {
            $meCard .= 'NOTE:'.$this->escapeValue($this->note).';';
        }

        if ($this->birthday) {
            $meCard .= 'BDAY:'.$this->escapeValue($this->birthday).';';
        }

        if ($this->address) {
            $meCard .= 'ADR:'.$this->escapeValue($this->address).';';
        }

        if ($this->url) {
            $meCard .= 'URL:'.$this->escapeValue($this->url).';';
        }

        return $meCard . ';';
    }

    /**
     * @param  list<mixed>|array<string, mixed>  $arguments
     */
    public function create(array $arguments): void
    {
        $properties = $arguments;

        // Support positional arguments
        // Order: name, phone, email, ...? Usually name, phone, email are most common positional
        if (array_is_list($arguments)) {
            $properties = [];
            if (isset($arguments[0])) {
                $properties['name'] = $arguments[0];
            }
            if (isset($arguments[1])) {
                $properties['phone'] = $arguments[1];
            }
            if (isset($arguments[2])) {
                $properties['email'] = $arguments[2];
            }
        }

        if (! isset($properties['name']) || ! is_string($properties['name'])) {
            throw new InvalidArgumentException('MeCard Name is mandatory.');
        }

        $this->name = $properties['name'];

        if (isset($properties['reading']) && is_string($properties['reading'])) {
            $this->reading = $properties['reading'];
        }
        if (isset($properties['phone']) && is_string($properties['phone'])) {
            $this->phone = $properties['phone'];
        }
        if (isset($properties['email']) && is_string($properties['email'])) {
            $this->email = $properties['email'];
        }
        if (isset($properties['note']) && is_string($properties['note'])) {
            $this->note = $properties['note'];
        }
        if (isset($properties['birthday']) && is_string($properties['birthday'])) {
            $this->birthday = $properties['birthday'];
        }
        if (isset($properties['address']) && is_string($properties['address'])) {
            $this->address = $properties['address'];
        }
        if (isset($properties['url']) && is_string($properties['url'])) {
            $this->url = $properties['url'];
        }
    }

    private function escapeValue(string $value): string
    {
        return strtr($value, [
            '\\' => '\\\\',
            ';' => '\\;',
            ':' => '\\:',
            ',' => '\\,', // Sometimes needed for list values, but safer to escape
        ]);
    }
}

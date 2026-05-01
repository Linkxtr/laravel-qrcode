<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\DataTypeInterface;
use LogicException;

final class MeCard implements DataTypeInterface
{
    private string $name = '';

    private ?string $reading = null;

    private ?string $nickname = null;

    private ?string $phone = null;

    private ?string $phone2 = null;

    private ?string $phone3 = null;

    private ?string $videoPhone = null;

    private ?string $email = null;

    private ?string $note = null;

    private ?string $birthday = null;

    private ?string $address = null;

    private ?string $postOfficeBox = null;

    private ?string $url = null;

    public function __toString(): string
    {
        if ($this->name === '') {
            throw new LogicException('MeCard must be initialized via create() before rendering.');
        }

        $meCard = 'MECARD:N:'.$this->escapeValue($this->name).';';

        if ($this->reading !== null) {
            $meCard .= 'SOUND:'.$this->escapeValue($this->reading).';';
        }

        if ($this->nickname !== null) {
            $meCard .= 'NICKNAME:'.$this->escapeValue($this->nickname).';';
        }

        if ($this->phone !== null) {
            $meCard .= 'TEL:'.$this->escapeValue($this->phone).';';
        }

        if ($this->phone2 !== null) {
            $meCard .= 'TEL:'.$this->escapeValue($this->phone2).';';
        }

        if ($this->phone3 !== null) {
            $meCard .= 'TEL:'.$this->escapeValue($this->phone3).';';
        }

        if ($this->videoPhone !== null) {
            $meCard .= 'TEL-AV:'.$this->escapeValue($this->videoPhone).';';
        }

        if ($this->email !== null) {
            $meCard .= 'EMAIL:'.$this->escapeValue($this->email).';';
        }

        if ($this->note !== null) {
            $meCard .= 'NOTE:'.$this->escapeValue($this->note).';';
        }

        if ($this->birthday !== null) {
            $meCard .= 'BDAY:'.$this->escapeValue($this->birthday).';';
        }

        if ($this->address !== null) {
            $meCard .= 'ADR:'.$this->escapeValue($this->address).';';
        }

        if ($this->postOfficeBox !== null) {
            $meCard .= 'POBOX:'.$this->escapeValue($this->postOfficeBox).';';
        }

        if ($this->url !== null) {
            $meCard .= 'URL:'.$this->escapeUrlValue($this->url).';';
        }

        return $meCard.';';
    }

    /**
     * @param  list<mixed>|array<string, mixed>  $arguments
     */
    public function create(array $arguments): void
    {
        if (isset($arguments[0]) && is_array($arguments[0]) && count($arguments) === 1) {
            $arguments = $arguments[0];
        }

        $properties = $arguments;

        if (array_is_list($arguments)) {
            $properties = [];
            $map = ['name', 'phone', 'email', 'url', 'address'];

            foreach ($map as $index => $key) {
                if (isset($arguments[$index])) {
                    $properties[$key] = $arguments[$index];
                }
            }
        }

        if (! isset($properties['name']) || ! is_string($properties['name']) || $properties['name'] === '') {
            throw new InvalidArgumentException('MeCard Name is mandatory.');
        }

        $this->name = $properties['name'];

        $this->reading = null;
        $this->nickname = null;
        $this->phone = null;
        $this->phone2 = null;
        $this->phone3 = null;
        $this->videoPhone = null;
        $this->email = null;
        $this->note = null;
        $this->birthday = null;
        $this->address = null;
        $this->postOfficeBox = null;
        $this->url = null;

        $this->assignStringProperty($properties, 'reading');
        $this->assignStringProperty($properties, 'nickname');
        $this->assignStringProperty($properties, 'phone');
        $this->assignStringProperty($properties, 'phone2');
        $this->assignStringProperty($properties, 'phone3');
        $this->assignStringProperty($properties, 'videoPhone');
        $this->assignStringProperty($properties, 'email');
        $this->assignStringProperty($properties, 'note');
        $this->assignStringProperty($properties, 'birthday');
        $this->assignStringProperty($properties, 'address');
        $this->assignStringProperty($properties, 'postOfficeBox');
        $this->assignStringProperty($properties, 'url');
    }

    /**
     * Helper to safely map optional properties and ignore empty/invalid values.
     *
     * @param  array<int|string, mixed>  $properties
     */
    private function assignStringProperty(array $properties, string $key): void
    {
        if (isset($properties[$key]) && is_string($properties[$key]) && $properties[$key] !== '') {
            $this->{$key} = $properties[$key];
        }
    }

    private function escapeValue(string $value): string
    {
        return strtr($value, [
            '\\' => '\\\\',
            ';' => '\\;',
            ':' => '\\:',
            ',' => '\\,',
        ]);
    }

    private function escapeUrlValue(string $value): string
    {
        return strtr($value, [
            '\\' => '\\\\',
            ';' => '\\;',
        ]);
    }
}

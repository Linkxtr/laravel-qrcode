<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\DataTypeInterface;
use LogicException;

final class VCard implements DataTypeInterface
{
    private string $name = '';

    private ?string $firstName = null;

    private ?string $lastName = null;

    private ?string $email = null;

    private ?string $emailWork = null;

    private ?string $emailHome = null;

    private ?string $phone = null;

    private ?string $phoneWork = null;

    private ?string $phoneHome = null;

    private ?string $phoneCell = null;

    private ?string $company = null;

    private ?string $job = null;

    private ?string $role = null;

    private ?string $address = null;

    private ?string $url = null;

    private ?string $note = null;

    private ?string $birthday = null;

    public function __toString(): string
    {
        if ($this->name === '') {
            throw new LogicException('VCard must be initialized via create() before rendering.');
        }

        $lines = [
            'BEGIN:VCARD',
            'VERSION:3.0',
        ];

        $lines[] = 'FN:'.$this->escapeValue($this->name);

        if ($this->firstName !== null || $this->lastName !== null) {
            $lines[] = 'N:'.$this->escapeValue((string) $this->lastName).';'.$this->escapeValue((string) $this->firstName).';;;';
        }

        if ($this->company !== null) {
            $lines[] = 'ORG:'.$this->escapeValue($this->company);
        }

        if ($this->job !== null) {
            $lines[] = 'TITLE:'.$this->escapeValue($this->job);
        }

        if ($this->role !== null) {
            $lines[] = 'ROLE:'.$this->escapeValue($this->role);
        }

        if ($this->email !== null) {
            $lines[] = 'EMAIL;type=INTERNET:'.$this->escapeValue($this->email);
        }

        if ($this->emailWork !== null) {
            $lines[] = 'EMAIL;type=INTERNET,WORK:'.$this->escapeValue($this->emailWork);
        }

        if ($this->emailHome !== null) {
            $lines[] = 'EMAIL;type=INTERNET,HOME:'.$this->escapeValue($this->emailHome);
        }

        if ($this->phone !== null) {
            $lines[] = 'TEL:'.$this->escapeValue($this->phone);
        }

        if ($this->phoneWork !== null) {
            $lines[] = 'TEL;type=WORK:'.$this->escapeValue($this->phoneWork);
        }

        if ($this->phoneHome !== null) {
            $lines[] = 'TEL;type=HOME:'.$this->escapeValue($this->phoneHome);
        }

        if ($this->phoneCell !== null) {
            $lines[] = 'TEL;type=CELL:'.$this->escapeValue($this->phoneCell);
        }

        if ($this->address !== null) {
            $lines[] = 'ADR:;;'.$this->escapeValue($this->address).';;;;';
        }

        if ($this->url !== null) {
            $lines[] = 'URL:'.$this->escapeValue($this->url);
        }

        if ($this->note !== null) {
            $lines[] = 'NOTE:'.$this->escapeValue($this->note);
        }

        if ($this->birthday !== null) {
            $lines[] = 'BDAY:'.$this->escapeValue($this->birthday);
        }

        $lines[] = 'END:VCARD';
        $lines[] = '';

        return implode("\r\n", $lines);
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

        // Support positional arguments for the most common fields
        if (array_is_list($arguments)) {
            $properties = [];
            $map = ['name', 'phone', 'email', 'company', 'job'];

            foreach ($map as $index => $key) {
                if (isset($arguments[$index])) {
                    $properties[$key] = $arguments[$index];
                }
            }
        }

        if (! isset($properties['name']) || ! is_string($properties['name']) || $properties['name'] === '') {
            throw new InvalidArgumentException('VCard Name is mandatory.');
        }

        $optionalKeys = [
            'firstName', 'lastName',
            'email', 'emailWork', 'emailHome',
            'phone', 'phoneWork', 'phoneHome', 'phoneCell',
            'company', 'job', 'role',
            'address', 'url', 'note', 'birthday',
        ];

        $resolved = [];

        foreach ($optionalKeys as $key) {
            $resolved[$key] = (isset($properties[$key]) && is_string($properties[$key]) && $properties[$key] !== '')
                ? $properties[$key]
                : null;
        }

        $this->name = $properties['name'];

        foreach ($resolved as $key => $value) {
            $this->{$key} = $value;
        }
    }

    private function escapeValue(string $value): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", $value);

        return strtr($value, [
            '\\' => '\\\\',
            ';' => '\\;',
            ',' => '\\,',
            "\n" => '\\n',
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidVCardArgumentException;

final readonly class VCard implements DataTypeInterface
{
    public function __construct(
        private string $name,
        private ?string $phone = null,
        private ?string $email = null,
        private ?string $company = null,
        private ?string $job = null,
        private ?string $firstName = null,
        private ?string $lastName = null,
        private ?string $emailWork = null,
        private ?string $emailHome = null,
        private ?string $phoneWork = null,
        private ?string $phoneHome = null,
        private ?string $phoneCell = null,
        private ?string $role = null,
        private ?string $address = null,
        private ?string $url = null,
        private ?string $note = null,
        private ?string $birthday = null
    ) {
        if ($this->name === '') {
            throw InvalidVCardArgumentException::invalidNameType('string');
        }
    }

    public function __toString(): string
    {
        $lines = [
            'BEGIN:VCARD',
            'VERSION:3.0',
        ];

        $lines[] = 'FN:'.$this->escapeValue($this->name);

        if ($this->firstName !== null || $this->lastName !== null) {
            $lines[] = 'N:'.$this->escapeValue((string) $this->lastName).';'.$this->escapeValue((string) $this->firstName).';;;';
        }

        if ($this->company !== null && $this->company !== '') {
            $lines[] = 'ORG:'.$this->escapeValue($this->company);
        }

        if ($this->job !== null && $this->job !== '') {
            $lines[] = 'TITLE:'.$this->escapeValue($this->job);
        }

        if ($this->role !== null && $this->role !== '') {
            $lines[] = 'ROLE:'.$this->escapeValue($this->role);
        }

        if ($this->email !== null && $this->email !== '') {
            $lines[] = 'EMAIL;type=INTERNET:'.$this->escapeValue($this->email);
        }

        if ($this->emailWork !== null && $this->emailWork !== '') {
            $lines[] = 'EMAIL;type=INTERNET,WORK:'.$this->escapeValue($this->emailWork);
        }

        if ($this->emailHome !== null && $this->emailHome !== '') {
            $lines[] = 'EMAIL;type=INTERNET,HOME:'.$this->escapeValue($this->emailHome);
        }

        if ($this->phone !== null && $this->phone !== '') {
            $lines[] = 'TEL:'.$this->escapeValue($this->phone);
        }

        if ($this->phoneWork !== null && $this->phoneWork !== '') {
            $lines[] = 'TEL;type=WORK:'.$this->escapeValue($this->phoneWork);
        }

        if ($this->phoneHome !== null && $this->phoneHome !== '') {
            $lines[] = 'TEL;type=HOME:'.$this->escapeValue($this->phoneHome);
        }

        if ($this->phoneCell !== null && $this->phoneCell !== '') {
            $lines[] = 'TEL;type=CELL:'.$this->escapeValue($this->phoneCell);
        }

        if ($this->address !== null && $this->address !== '') {
            $lines[] = 'ADR:;;'.$this->escapeValue($this->address).';;;;';
        }

        if ($this->url !== null && $this->url !== '') {
            $lines[] = 'URL:'.$this->escapeValue($this->url);
        }

        if ($this->note !== null && $this->note !== '') {
            $lines[] = 'NOTE:'.$this->escapeValue($this->note);
        }

        if ($this->birthday !== null && $this->birthday !== '') {
            $lines[] = 'BDAY:'.$this->escapeValue($this->birthday);
        }

        $lines[] = 'END:VCARD';
        $lines[] = '';

        return implode("\r\n", $lines);
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

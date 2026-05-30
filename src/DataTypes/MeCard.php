<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidMeCardArgumentException;

final readonly class MeCard implements DataTypeInterface
{
    public function __construct(
        private string $name,
        private ?string $phone = null,
        private ?string $email = null,
        private ?string $url = null,
        private ?string $address = null,
        private ?string $reading = null,
        private ?string $nickname = null,
        private ?string $phone2 = null,
        private ?string $phone3 = null,
        private ?string $videoPhone = null,
        private ?string $note = null,
        private ?string $birthday = null,
        private ?string $postOfficeBox = null
    ) {
        if ($this->name === '') {
            throw InvalidMeCardArgumentException::invalidNameType('string');
        }
    }

    public function __toString(): string
    {
        $meCard = 'MECARD:N:'.$this->escapeNameValue($this->name).';';

        if ($this->reading !== null && $this->reading !== '') {
            $meCard .= 'SOUND:'.$this->escapeValue($this->reading).';';
        }

        if ($this->nickname !== null && $this->nickname !== '') {
            $meCard .= 'NICKNAME:'.$this->escapeValue($this->nickname).';';
        }

        if ($this->phone !== null && $this->phone !== '') {
            $meCard .= 'TEL:'.$this->escapeValue($this->phone).';';
        }

        if ($this->phone2 !== null && $this->phone2 !== '') {
            $meCard .= 'TEL:'.$this->escapeValue($this->phone2).';';
        }

        if ($this->phone3 !== null && $this->phone3 !== '') {
            $meCard .= 'TEL:'.$this->escapeValue($this->phone3).';';
        }

        if ($this->videoPhone !== null && $this->videoPhone !== '') {
            $meCard .= 'TEL-AV:'.$this->escapeValue($this->videoPhone).';';
        }

        if ($this->email !== null && $this->email !== '') {
            $meCard .= 'EMAIL:'.$this->escapeValue($this->email).';';
        }

        if ($this->note !== null && $this->note !== '') {
            $meCard .= 'NOTE:'.$this->escapeValue($this->note).';';
        }

        if ($this->birthday !== null && $this->birthday !== '') {
            $meCard .= 'BDAY:'.$this->escapeValue($this->birthday).';';
        }

        if ($this->address !== null && $this->address !== '') {
            $meCard .= 'ADR:'.$this->escapeValue($this->address).';';
        }

        if ($this->postOfficeBox !== null && $this->postOfficeBox !== '') {
            $meCard .= 'POBOX:'.$this->escapeValue($this->postOfficeBox).';';
        }

        if ($this->url !== null && $this->url !== '') {
            $meCard .= 'URL:'.$this->escapeUrlValue($this->url).';';
        }

        return $meCard.';';
    }

    private function escapeNameValue(string $value): string
    {
        return strtr($value, [
            '\\' => '\\\\',
            ';' => '\\;',
            ':' => '\\:',
        ]);
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

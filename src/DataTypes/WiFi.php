<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\DataTypeInterface;
use LogicException;

final class WiFi implements DataTypeInterface
{
    private string $ssid = '';

    private string $encryption = 'NOPASS';

    private ?string $password = null;

    private bool $hidden = false;

    public function __toString(): string
    {
        if ($this->ssid === '') {
            throw new LogicException('WiFi must be initialized via create() before rendering.');
        }

        $wifi = 'WIFI:S:'.$this->escapeValue($this->ssid).';';
        $wifi .= 'T:'.$this->encryption.';';

        if ($this->password !== null) {
            $wifi .= 'P:'.$this->escapeValue($this->password).';';
        }

        if ($this->hidden) {
            $wifi .= 'H:true;';
        }

        return $wifi.';';
    }

    /**
     * @param  list<mixed>|array<string, mixed>  $arguments
     */
    public function create(array $arguments): void
    {
        if (isset($arguments[0]) && is_array($arguments[0]) && count($arguments) === 1) {
            $arguments = $arguments[0];
        }

        $this->password = null;
        $this->hidden = false;

        $properties = $arguments;

        // Support positional arguments: SSID, Encryption, Password, Hidden
        if (array_is_list($arguments)) {
            $properties = [];
            $map = ['ssid', 'encryption', 'password', 'hidden'];

            foreach ($map as $index => $key) {
                if (isset($arguments[$index])) {
                    $properties[$key] = $arguments[$index];
                }
            }
        }

        if (! isset($properties['ssid']) || ! is_string($properties['ssid']) || $properties['ssid'] === '') {
            throw new InvalidArgumentException('WiFi SSID is mandatory.');
        }

        $this->ssid = $properties['ssid'];

        $hasPassword = isset($properties['password']) && is_string($properties['password']) && $properties['password'] !== '';

        if (isset($properties['encryption']) && is_string($properties['encryption']) && $properties['encryption'] !== '') {
            $encryption = strtoupper($properties['encryption']);
            if (! in_array($encryption, ['WEP', 'WPA', 'NOPASS'], true)) {
                throw new InvalidArgumentException('WiFi encryption must be WEP, WPA, or NOPASS.');
            }

            $this->encryption = $encryption;
        } else {
            $this->encryption = $hasPassword ? 'WPA' : 'NOPASS';
        }

        if ($this->encryption === 'NOPASS' && $hasPassword) {
            throw new InvalidArgumentException('WiFi password cannot be provided when encryption is NOPASS.');
        }

        if ($hasPassword) {
            $this->password = $properties['password'];
        }

        if (isset($properties['hidden'])) {
            if (! is_scalar($properties['hidden'])) {
                throw new InvalidArgumentException('WiFi hidden flag must be a boolean or a string representation of a boolean.');
            }

            $this->hidden = filter_var($properties['hidden'], FILTER_VALIDATE_BOOLEAN);
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
}

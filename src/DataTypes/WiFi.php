<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\Exceptions\InvalidWiFiArgumentException;
use Linkxtr\QrCode\Exceptions\UninitializedDataTypeException;

final class WiFi implements DataTypeInterface
{
    private string $ssid = '';

    private string $encryption = 'NOPASS';

    private ?string $password = null;

    private bool $hidden = false;

    public function __toString(): string
    {
        if ($this->ssid === '') {
            throw UninitializedDataTypeException::forType('WiFi');
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

        $ssid = null;
        $encryption = 'NOPASS';
        $password = null;
        $hidden = false;

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

        if (! isset($properties['ssid'])) {
            throw InvalidWiFiArgumentException::missingArguments('WiFi SSID is mandatory.');
        }

        if (! is_string($properties['ssid']) || $properties['ssid'] === '') {
            throw InvalidWiFiArgumentException::invalidSsidValue(gettype($properties['ssid']));
        }

        $ssid = $properties['ssid'];

        $hasPassword = isset($properties['password']) && is_string($properties['password']) && $properties['password'] !== '';

        if (isset($properties['encryption']) && is_string($properties['encryption']) && $properties['encryption'] !== '') {
            $encryption = strtoupper($properties['encryption']);

            if (in_array($encryption, ['WPA2', 'WPA3'], true)) {
                $encryption = 'WPA';
            }

            if (! in_array($encryption, ['WEP', 'WPA', 'NOPASS'], true)) {
                throw InvalidWiFiArgumentException::invalidEncryptionValue($encryption);
            }

            $resolvedEncryption = $encryption;
        } else {
            $resolvedEncryption = $hasPassword ? 'WPA' : 'NOPASS';
        }

        if ($resolvedEncryption === 'NOPASS' && $hasPassword) {
            throw InvalidWiFiArgumentException::passwordWithNopassEncryption();
        }

        if ($hasPassword) {
            $password = $properties['password'];
        }

        if (isset($properties['hidden'])) {
            if (! is_scalar($properties['hidden'])) {
                throw InvalidWiFiArgumentException::invalidHiddenType(gettype($properties['hidden']));
            }

            $hidden = filter_var($properties['hidden'], FILTER_VALIDATE_BOOLEAN);
        }

        $this->ssid = $ssid;
        $this->encryption = $resolvedEncryption;
        $this->password = $password;
        $this->hidden = $hidden;
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

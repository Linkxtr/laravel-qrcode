<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidWiFiArgumentException;

final readonly class WiFi implements DataTypeInterface
{
    private string $encryption;

    public function __construct(
        private string $ssid,
        ?string $encryption = null,
        private ?string $password = null,
        private bool $hidden = false
    ) {
        if ($this->ssid === '') {
            throw InvalidWiFiArgumentException::invalidSsidValue('string');
        }

        $hasPassword = $this->password !== null && $this->password !== '';
        $enc = $encryption !== null && $encryption !== '' ? strtoupper($encryption) : null;

        if ($enc !== null) {
            if (in_array($enc, ['WPA2', 'WPA3'], true)) {
                $enc = 'WPA';
            }

            if (! in_array($enc, ['WEP', 'WPA', 'NOPASS'], true)) {
                throw InvalidWiFiArgumentException::invalidEncryptionValue($enc);
            }

            $this->encryption = $enc;
        } else {
            $this->encryption = $hasPassword ? 'WPA' : 'NOPASS';
        }

        if ($this->encryption === 'NOPASS' && $hasPassword) {
            throw InvalidWiFiArgumentException::passwordWithNopassEncryption();
        }
    }

    public function __toString(): string
    {
        $wifi = 'WIFI:S:'.$this->escapeValue($this->ssid).';';
        $wifi .= 'T:'.$this->encryption.';';

        if ($this->password !== null && $this->password !== '') {
            $wifi .= 'P:'.$this->escapeValue($this->password).';';
        }

        if ($this->hidden) {
            $wifi .= 'H:true;';
        }

        return $wifi.';';
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

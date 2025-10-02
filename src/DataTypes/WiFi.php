<?php

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;

class WiFi implements DataTypeInterface
{
    protected string $prefix = 'WIFI:';

    protected ?string $ssid = null;

    protected ?string $password = null;

    protected bool $hidden = false;

    protected string $separator = ';';

    public function create(array $arguments): void
    {
        $this->setProperties($arguments);
    }

    protected function setProperties(array $arguments)
    {
        $arguments = $arguments[0];
        if (isset($arguments['ssid'])) {
            $this->ssid = $arguments['ssid'];
        }
        if (isset($arguments['password'])) {
            $this->password = $arguments['password'];
        }
        if (isset($arguments['hidden'])) {
            $this->hidden = $arguments['hidden'];
        }
    }

    public function __toString(): string
    {
        return $this->buildWiFiString();
    }

    protected function buildWiFiString(): string
    {
        $wifi = $this->prefix;

        if (isset($this->password)) {
            $wifi .= 'T:WPA'.$this->separator;
        }
        if (isset($this->ssid)) {
            $wifi .= 'S:'.$this->ssid.$this->separator;
        }
        if (isset($this->password)) {
            $wifi .= 'P:'.$this->password.$this->separator;
        }
        if ($this->hidden) {
            $wifi .= 'H:true'.$this->separator;
        }

        return $wifi;
    }
}

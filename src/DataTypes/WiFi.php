<?php

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;

class WiFi implements DataTypeInterface
{
    protected string $prefix = 'WIFI:';

    protected string $ssid;

    protected string $password;

    protected bool $hidden;

    protected string $separator = ';';

    /**
     * @param  list<mixed>  $arguments
     */
    public function create(array $arguments): void
    {
        $this->setProperties($arguments);
    }

    /**
     * @param  list<mixed>  $arguments
     */
    protected function setProperties(array $arguments): void
    {
        if (! isset($arguments[0]) || ! is_array($arguments[0])) {
            throw new InvalidArgumentException('Invalid WiFi arguments.');
        }

        $arguments = $arguments[0];

        if (! isset($arguments['ssid']) || ! is_string($arguments['ssid']) || $arguments['ssid'] === '') {
            throw new InvalidArgumentException('SSID is required and must be a string.');
        }

        $this->ssid = $arguments['ssid'];

        if (isset($arguments['password']) && is_string($arguments['password'])) {
            $this->password = $arguments['password'];
        }

        if (isset($arguments['hidden']) && is_bool($arguments['hidden'])) {
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

        $wifi .= 'S:'.$this->ssid.$this->separator;

        if (isset($this->password)) {
            $wifi .= 'P:'.$this->password.$this->separator;
        }
        if (isset($this->hidden) && $this->hidden === true) {
            $wifi .= 'H:true'.$this->separator;
        }

        return $wifi;
    }
}

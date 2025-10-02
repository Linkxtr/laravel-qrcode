<?php

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;
use Linkxtr\QrCode\DataTypes\DataTypeInterface;

class Geo implements DataTypeInterface
{
    protected string $prefix = 'geo:';

    protected ?string $latitude = null;

    protected ?string $longitude = null;

    protected ?string $name = null;

    public function create(array $arguments): void
    {
        $this->setProperties($arguments);
    }

    protected function setProperties(array $arguments)
    {
        if (!isset($arguments[0]) || !isset($arguments[1])) {
            throw new InvalidArgumentException('Both latitude and longitude are required.');
        }

        $this->validateCoordinate($arguments[0], 'latitude');
        $this->validateCoordinate($arguments[1], 'longitude');

        $this->latitude = $arguments[0];
        $this->longitude = $arguments[1];

        if (isset($arguments[2])) {
            $this->name = $arguments[2];
        }
    }

    public function __toString(): string
    {
        return $this->buildGeoString();
    }

    protected function validateCoordinate($value, string $type): void
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException("Invalid {$type} value: must be a number");
        }

        $value = (float) $value;
        
        if ($type === 'latitude' && ($value < -90 || $value > 90)) {
            throw new InvalidArgumentException('Latitude must be between -90 and 90 degrees');
        }
        
        if ($type === 'longitude' && ($value < -180 || $value > 180)) {
            throw new InvalidArgumentException('Longitude must be between -180 and 180 degrees');
        }
    }

    protected function buildGeoString(): string
    {
        $query = http_build_query([
            'name' => $this->name,
        ]);

        if (empty($query)) {
            return $this->prefix.$this->latitude.','.$this->longitude;
        }

        return $this->prefix.$this->latitude.','.$this->longitude.'?'.$query;
    }
}
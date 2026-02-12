<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\DataTypeInterface;

final class Geo implements DataTypeInterface
{
    private string $prefix = 'geo:';

    private float $latitude;

    private float $longitude;

    private string $name = '';

    public function __toString(): string
    {
        return $this->buildGeoString();
    }

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
    private function setProperties(array $arguments): void
    {
        if (! isset($arguments[0]) || ! isset($arguments[1])) {
            throw new InvalidArgumentException('Both latitude and longitude are required.');
        }

        $this->latitude = $this->validateCoordinate($arguments[0], 'latitude');
        $this->longitude = $this->validateCoordinate($arguments[1], 'longitude');

        if (! isset($arguments[2])) {
            return;
        }

        if (! is_string($arguments[2])) {
            throw new InvalidArgumentException('Invalid name value: must be a string');
        }

        $this->name = $arguments[2];
    }

    private function validateCoordinate(mixed $value, string $type): float
    {
        if (! is_numeric($value)) {
            throw new InvalidArgumentException("Invalid {$type} value: must be a number");
        }

        $value = (float) $value; // @pest-mutate-ignore

        if ($type === 'latitude' && ($value < -90 || $value > 90)) {
            throw new InvalidArgumentException('Latitude must be between -90 and 90 degrees');
        }

        if ($type === 'longitude' && ($value < -180 || $value > 180)) {
            throw new InvalidArgumentException('Longitude must be between -180 and 180 degrees');
        }

        return $value;
    }

    private function buildGeoString(): string
    {
        if (! isset($this->name) || empty($this->name)) {
            return $this->prefix.$this->latitude.','.$this->longitude;
        }

        $query = http_build_query([
            'name' => $this->name,
        ]);

        return $this->prefix.$this->latitude.','.$this->longitude.'?'.$query;
    }
}

<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\DataTypeInterface;

final class Geo implements DataTypeInterface
{
    private const PREFIX = 'geo:';

    private float $latitude;

    private float $longitude;

    private ?string $name = null;

    public function __toString(): string
    {
        $baseUri = self::PREFIX.$this->latitude.','.$this->longitude;

        if ($this->name !== null) {
            return $baseUri.'?'.http_build_query(['name' => $this->name], encoding_type: PHP_QUERY_RFC3986);
        }

        return $baseUri;
    }

    /**
     * @param  list<mixed>  $arguments
     */
    public function create(array $arguments): void
    {
        if (! isset($arguments[0], $arguments[1])) {
            throw new InvalidArgumentException('Latitude and longitude are required.');
        }

        if (! is_numeric($arguments[0]) || ! is_numeric($arguments[1])) {
            throw new InvalidArgumentException('Latitude and longitude must be numeric.');
        }

        $latitude = (float) $arguments[0];
        $longitude = (float) $arguments[1];

        if ($latitude < -90 || $latitude > 90) {
            throw new InvalidArgumentException('Latitude must be between -90 and 90.');
        }

        if ($longitude < -180 || $longitude > 180) {
            throw new InvalidArgumentException('Longitude must be between -180 and 180.');
        }

        $name = null;

        if (array_key_exists(2, $arguments) && $arguments[2] !== null) {
            if (! is_string($arguments[2])) {
                throw new InvalidArgumentException('Geo name must be a string.');
            }

            if ($arguments[2] !== '') {
                $name = $arguments[2];
            }
        }

        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->name = $name;
    }
}

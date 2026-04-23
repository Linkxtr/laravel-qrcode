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

        $this->latitude = (float) $arguments[0];
        $this->longitude = (float) $arguments[1];

        if ($this->latitude < -90 || $this->latitude > 90) {
            throw new InvalidArgumentException('Latitude must be between -90 and 90.');
        }

        if ($this->longitude < -180 || $this->longitude > 180) {
            throw new InvalidArgumentException('Longitude must be between -180 and 180.');
        }

        if (isset($arguments[2])) {
            if (! is_string($arguments[2])) {
                throw new InvalidArgumentException('Geo name must be a string.');
            }

            if ($arguments[2] !== '') {
                $this->name = $arguments[2];
            }
        }
    }
}

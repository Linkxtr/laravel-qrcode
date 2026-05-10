<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\Exceptions\InvalidGeoArgumentException;
use Linkxtr\QrCode\Exceptions\UninitializedDataTypeException;

final class Geo implements DataTypeInterface
{
    private const PREFIX = 'geo:';

    private float $latitude;

    private float $longitude;

    private ?string $name = null;

    public function __toString(): string
    {
        if (! isset($this->latitude, $this->longitude)) {
            throw UninitializedDataTypeException::forType('Geo');
        }

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
            throw InvalidGeoArgumentException::missingArguments('Latitude and longitude are required.');
        }

        if (! is_numeric($arguments[0]) || ! is_numeric($arguments[1])) {
            throw InvalidGeoArgumentException::invalidCoordinatesType(gettype($arguments[0]), gettype($arguments[1]));
        }

        $latitude = (float) $arguments[0];
        $longitude = (float) $arguments[1];

        if ($latitude < -90 || $latitude > 90) {
            throw InvalidGeoArgumentException::invalidLatitude();
        }

        if ($longitude < -180 || $longitude > 180) {
            throw InvalidGeoArgumentException::invalidLongitude();
        }

        $name = null;

        if (array_key_exists(2, $arguments) && $arguments[2] !== null) {
            if (! is_string($arguments[2])) {
                throw InvalidGeoArgumentException::invalidNameType(gettype($arguments[2]));
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

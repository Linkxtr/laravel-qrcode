<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidGeoArgumentException;

final readonly class Geo implements DataTypeInterface
{
    private const PREFIX = 'geo:';

    public function __construct(
        private float $latitude,
        private float $longitude,
        private ?string $name = null
    ) {
        if ($this->latitude < -90 || $this->latitude > 90) {
            throw InvalidGeoArgumentException::invalidLatitude();
        }

        if ($this->longitude < -180 || $this->longitude > 180) {
            throw InvalidGeoArgumentException::invalidLongitude();
        }
    }

    public function __toString(): string
    {
        $lat = $this->formatCoordinate($this->latitude);
        $lng = $this->formatCoordinate($this->longitude);

        $baseUri = self::PREFIX.$lat.','.$lng;

        if ($this->name !== null) {
            return $baseUri.'?'.http_build_query(['name' => $this->name], encoding_type: PHP_QUERY_RFC3986);
        }

        return $baseUri;
    }

    private function formatCoordinate(float $coordinate): string
    {
        $string = (string) $coordinate;

        if (! str_contains($string, 'E')) {
            return $string;
        }

        $formatted = rtrim(rtrim(sprintf('%.10F', $coordinate), '0'), '.');

        return $formatted === '-0' ? '0' : $formatted;
    }
}

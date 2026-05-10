<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Exceptions;

final class InvalidGeoArgumentException extends InvalidDataTypeArgumentException
{
    public static function invalidCoordinatesType(string $type1, string $type2): self
    {
        $exception = new self(sprintf('Latitude and longitude must be numeric. Provided types: %s, %s', $type1, $type2));
        $exception->errorCode = 'INVALID_GEO_COORDINATES_TYPE';
        $exception->helperMessage = 'Latitude and longitude must be numeric.';

        return $exception;
    }

    public static function invalidLatitude(): self
    {
        $exception = new self('Latitude must be between -90 and 90.');
        $exception->errorCode = 'INVALID_GEO_LATITUDE';
        $exception->helperMessage = 'Latitude must be between -90 and 90.';

        return $exception;
    }

    public static function invalidLongitude(): self
    {
        $exception = new self('Longitude must be between -180 and 180.');
        $exception->errorCode = 'INVALID_GEO_LONGITUDE';
        $exception->helperMessage = 'Longitude must be between -180 and 180.';

        return $exception;
    }

    public static function invalidNameType(string $type): self
    {
        $exception = new self(sprintf('Geo name must be a string. Provided type: %s', $type));
        $exception->errorCode = 'INVALID_GEO_NAME_TYPE';
        $exception->helperMessage = 'Geo name must be a string.';

        return $exception;
    }
}

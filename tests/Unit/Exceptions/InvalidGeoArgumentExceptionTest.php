<?php

declare(strict_types=1);

use Linkxtr\QrCode\Exceptions\InvalidGeoArgumentException;

covers(InvalidGeoArgumentException::class);

test('invalidCoordinatesType sets correct error code and message', function (): void {
    $invalidGeoArgumentException = InvalidGeoArgumentException::invalidCoordinatesType('test', 'test');

    expect($invalidGeoArgumentException)->toBeInstanceOf(InvalidGeoArgumentException::class)
        ->and($invalidGeoArgumentException->getErrorCode())->toBe('INVALID_GEO_COORDINATES_TYPE')
        ->and($invalidGeoArgumentException->getHelperMessage())->toBe('Latitude and longitude must be numeric.');
});

test('invalidLatitude sets correct error code and message', function (): void {
    $invalidGeoArgumentException = InvalidGeoArgumentException::invalidLatitude();

    expect($invalidGeoArgumentException)->toBeInstanceOf(InvalidGeoArgumentException::class)
        ->and($invalidGeoArgumentException->getErrorCode())->toBe('INVALID_GEO_LATITUDE')
        ->and($invalidGeoArgumentException->getHelperMessage())->toBe('Latitude must be between -90 and 90.');
});

test('invalidLongitude sets correct error code and message', function (): void {
    $invalidGeoArgumentException = InvalidGeoArgumentException::invalidLongitude();

    expect($invalidGeoArgumentException)->toBeInstanceOf(InvalidGeoArgumentException::class)
        ->and($invalidGeoArgumentException->getErrorCode())->toBe('INVALID_GEO_LONGITUDE')
        ->and($invalidGeoArgumentException->getHelperMessage())->toBe('Longitude must be between -180 and 180.');
});

test('invalidNameType sets correct error code and message', function (): void {
    $invalidGeoArgumentException = InvalidGeoArgumentException::invalidNameType('test');

    expect($invalidGeoArgumentException)->toBeInstanceOf(InvalidGeoArgumentException::class)
        ->and($invalidGeoArgumentException->getErrorCode())->toBe('INVALID_GEO_NAME_TYPE')
        ->and($invalidGeoArgumentException->getHelperMessage())->toBe('Geo name must be a string.');
});

test('missingArguments sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = InvalidGeoArgumentException::missingArguments('test');

    expect($invalidDataTypeArgumentException)->toBeInstanceOf(InvalidGeoArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())->toBe('MISSING_ARGUMENTS')
        ->and($invalidDataTypeArgumentException->getHelperMessage())->toBe('test');
});

test('invalidArgument sets correct error code and message', function (): void {
    $invalidDataTypeArgumentException = InvalidGeoArgumentException::invalidArgument('test');

    expect($invalidDataTypeArgumentException)->toBeInstanceOf(InvalidGeoArgumentException::class)
        ->and($invalidDataTypeArgumentException->getErrorCode())->toBe('INVALID_ARGUMENT')
        ->and($invalidDataTypeArgumentException->getHelperMessage())->toBe('test');
});

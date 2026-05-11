<?php

declare(strict_types=1);

use Linkxtr\QrCode\Exceptions\InvalidConfigurationException;

covers(InvalidConfigurationException::class);

test('unsupportedFormat sets correct error code and message', function (): void {
    $invalidConfigurationException = InvalidConfigurationException::unsupportedFormat('test', ['test']);

    expect($invalidConfigurationException)->toBeInstanceOf(InvalidConfigurationException::class);
    expect($invalidConfigurationException->getErrorCode())->toBe('UNSUPPORTED_FORMAT');
    expect($invalidConfigurationException->getHelperMessage())->toBe('Supported formats are: test');
});

test('invalidErrorCorrectionLevel sets correct error code and message', function (): void {
    $invalidConfigurationException = InvalidConfigurationException::invalidErrorCorrectionLevel('test', ['test']);

    expect($invalidConfigurationException)->toBeInstanceOf(InvalidConfigurationException::class)
        ->and($invalidConfigurationException->getErrorCode())->toBe('INVALID_ERROR_CORRECTION_LEVEL')
        ->and($invalidConfigurationException->getHelperMessage())->toBe('Supported levels are: test');
});

test('invalidEyeStyle sets correct error code and message', function (): void {
    $invalidConfigurationException = InvalidConfigurationException::invalidEyeStyle('test', ['test']);

    expect($invalidConfigurationException)->toBeInstanceOf(InvalidConfigurationException::class)
        ->and($invalidConfigurationException->getErrorCode())->toBe('INVALID_EYE_STYLE')
        ->and($invalidConfigurationException->getHelperMessage())->toBe('Supported eye styles are: test');
});

test('invalidSize sets correct error code and message', function (): void {
    $invalidConfigurationException = InvalidConfigurationException::invalidSize(1);

    expect($invalidConfigurationException)->toBeInstanceOf(InvalidConfigurationException::class)
        ->and($invalidConfigurationException->getErrorCode())->toBe('INVALID_SIZE')
        ->and($invalidConfigurationException->getHelperMessage())->toBe('Size specifies the size of the QR code in pixels. Must be greater than 0.');
});

test('invalidMargin sets correct error code and message', function (): void {
    $invalidConfigurationException = InvalidConfigurationException::invalidMargin(1);

    expect($invalidConfigurationException)->toBeInstanceOf(InvalidConfigurationException::class)
        ->and($invalidConfigurationException->getErrorCode())->toBe('INVALID_MARGIN')
        ->and($invalidConfigurationException->getHelperMessage())->toBe('Margin specifies the empty space (quiet zone) around the QR code. Must be 0 or greater.');
});

test('invalidGradientType sets correct error code and message', function (): void {
    $invalidConfigurationException = InvalidConfigurationException::invalidGradientType('test', ['test']);

    expect($invalidConfigurationException)->toBeInstanceOf(InvalidConfigurationException::class)
        ->and($invalidConfigurationException->getErrorCode())->toBe('INVALID_GRADIENT_TYPE')
        ->and($invalidConfigurationException->getHelperMessage())->toBe('Supported gradient types are: test');
});

test('invalidImageMergePercentage sets correct error code and message', function (): void {
    $invalidConfigurationException = InvalidConfigurationException::invalidImageMergePercentage(0.5);

    expect($invalidConfigurationException)->toBeInstanceOf(InvalidConfigurationException::class)
        ->and($invalidConfigurationException->getErrorCode())->toBe('INVALID_IMAGE_MERGE_PERCENTAGE')
        ->and($invalidConfigurationException->getHelperMessage())->toBe('Image merge percentage must be between 0 and 1 (exclusive).');
});

test('invalidStyleSize sets correct error code and message', function (): void {
    $invalidConfigurationException = InvalidConfigurationException::invalidStyleSize(0.5);

    expect($invalidConfigurationException)->toBeInstanceOf(InvalidConfigurationException::class)
        ->and($invalidConfigurationException->getErrorCode())->toBe('INVALID_STYLE_SIZE')
        ->and($invalidConfigurationException->getHelperMessage())->toBe('Style size specifies the size of the style between 0 and 1.');
});

test('invalidStyle sets correct error code and message', function (): void {
    $invalidConfigurationException = InvalidConfigurationException::invalidStyle('test', ['test']);

    expect($invalidConfigurationException)->toBeInstanceOf(InvalidConfigurationException::class)
        ->and($invalidConfigurationException->getErrorCode())->toBe('INVALID_STYLE')
        ->and($invalidConfigurationException->getHelperMessage())->toBe('Supported styles are: test');
});

test('invalidEyeNumber sets correct error code and message', function (): void {
    $invalidConfigurationException = InvalidConfigurationException::invalidEyeNumber(1);

    expect($invalidConfigurationException)->toBeInstanceOf(InvalidConfigurationException::class)
        ->and($invalidConfigurationException->getErrorCode())->toBe('INVALID_EYE_NUMBER')
        ->and($invalidConfigurationException->getHelperMessage())->toBe('Eye number specifies the position of the eye (0: top-left, 1: top-right, 2: bottom-left).');
});

test('invalidGrayscale sets correct error code and message', function (): void {
    $invalidConfigurationException = InvalidConfigurationException::invalidGrayscale(1);

    expect($invalidConfigurationException)->toBeInstanceOf(InvalidConfigurationException::class)
        ->and($invalidConfigurationException->getErrorCode())->toBe('INVALID_GRAYSCALE')
        ->and($invalidConfigurationException->getHelperMessage())->toBe('Grayscale value specifies the intensity of the gray color. Must be between 0 and 100.');
});

test('imagePathOutsideApplication sets correct error code and message', function (): void {
    $invalidConfigurationException = InvalidConfigurationException::imagePathOutsideApplication();

    expect($invalidConfigurationException)->toBeInstanceOf(InvalidConfigurationException::class)
        ->and($invalidConfigurationException->getErrorCode())->toBe('IMAGE_PATH_OUTSIDE_APPLICATION')
        ->and($invalidConfigurationException->getHelperMessage())->toBe('Image file path must be inside the application base path.');
});

test('imageDoesNotExist sets correct error code and message', function (): void {
    $invalidConfigurationException = InvalidConfigurationException::imageDoesNotExist('test');

    expect($invalidConfigurationException)->toBeInstanceOf(InvalidConfigurationException::class)
        ->and($invalidConfigurationException->getErrorCode())->toBe('IMAGE_PATH_DOES_NOT_EXIST')
        ->and($invalidConfigurationException->getHelperMessage())->toBe('Image file path must exist.');
});

test('imageFileNotReadable sets correct error code and message', function (): void {
    $invalidConfigurationException = InvalidConfigurationException::imageFileNotReadable('test');

    expect($invalidConfigurationException)->toBeInstanceOf(InvalidConfigurationException::class)
        ->and($invalidConfigurationException->getErrorCode())->toBe('IMAGE_FILE_NOT_READABLE')
        ->and($invalidConfigurationException->getHelperMessage())->toBe('Failed to read image file.');
});

test('invalidColorChannel sets correct error code and message', function (): void {
    $invalidConfigurationException = InvalidConfigurationException::invalidColorChannel('test', 1, 1);

    expect($invalidConfigurationException)->toBeInstanceOf(InvalidConfigurationException::class)
        ->and($invalidConfigurationException->getErrorCode())->toBe('INVALID_COLOR_CHANNEL')
        ->and($invalidConfigurationException->getHelperMessage())->toBe('Check the RGB/CMYK/Alpha values passed to the color methods.');
});

test('invalidColorFormat sets correct error code and message', function (): void {
    $invalidConfigurationException = InvalidConfigurationException::invalidColorFormat();

    expect($invalidConfigurationException)->toBeInstanceOf(InvalidConfigurationException::class)
        ->and($invalidConfigurationException->getErrorCode())->toBe('INVALID_COLOR_FORMAT')
        ->and($invalidConfigurationException->getHelperMessage())->toBe('Unrecognized color format. Please use an array, a hex string, or a comma-separated RGB string.');
});

test('invalidCsvColorString sets correct error code and message', function (): void {
    $invalidConfigurationException = InvalidConfigurationException::invalidCsvColorString();

    expect($invalidConfigurationException)->toBeInstanceOf(InvalidConfigurationException::class)
        ->and($invalidConfigurationException->getErrorCode())->toBe('INVALID_CSV_COLOR_STRING')
        ->and($invalidConfigurationException->getHelperMessage())->toBe('CSV color string must contain exactly 3 or 4 numeric values.');
});

test('invalidHexColorString sets correct error code and message', function (): void {
    $invalidConfigurationException = InvalidConfigurationException::invalidHexColorString();

    expect($invalidConfigurationException)->toBeInstanceOf(InvalidConfigurationException::class)
        ->and($invalidConfigurationException->getErrorCode())->toBe('INVALID_HEX_COLOR_STRING')
        ->and($invalidConfigurationException->getHelperMessage())->toBe('Invalid hex color format. Must be 3 or 6 characters.');
});

test('invalidColorArray sets correct error code and message', function (): void {
    $invalidConfigurationException = InvalidConfigurationException::invalidColorArray();

    expect($invalidConfigurationException)->toBeInstanceOf(InvalidConfigurationException::class)
        ->and($invalidConfigurationException->getErrorCode())->toBe('INVALID_COLOR_ARRAY')
        ->and($invalidConfigurationException->getHelperMessage())->toBe('RGB array values must be numeric. Nested arrays, objects, or invalid strings are not allowed.');
});

test('invalidHexColor sets correct error code and message', function (): void {
    $invalidConfigurationException = InvalidConfigurationException::invalidHexColor();

    expect($invalidConfigurationException)->toBeInstanceOf(InvalidConfigurationException::class)
        ->and($invalidConfigurationException->getErrorCode())->toBe('INVALID_HEX_COLOR')
        ->and($invalidConfigurationException->getHelperMessage())->toBe('Invalid hex color string provided.');
});

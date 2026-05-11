<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Exceptions;

use InvalidArgumentException;
use Linkxtr\QrCode\Exceptions\Concerns\HasHelperMessage;

final class InvalidConfigurationException extends InvalidArgumentException implements QrCodeExceptionInterface
{
    use HasHelperMessage;

    /**
     * @param  array<string>  $supported
     */
    public static function unsupportedFormat(string $format, array $supported): self
    {
        $exception = new self(sprintf('Format must be one of the following values: %s. Got: %s', implode(', ', $supported), $format));
        $exception->errorCode = 'UNSUPPORTED_FORMAT';
        $exception->helperMessage = 'Supported formats are: '.implode(', ', $supported);

        return $exception;
    }

    /**
     * @param  array<string>  $supported
     */
    public static function invalidErrorCorrectionLevel(string $level, array $supported): self
    {
        $exception = new self(sprintf('Error correction level must be one of the following values: %s. Got: %s', implode(', ', $supported), $level));
        $exception->errorCode = 'INVALID_ERROR_CORRECTION_LEVEL';
        $exception->helperMessage = 'Supported levels are: '.implode(', ', $supported);

        return $exception;
    }

    /**
     * @param  array<string>  $supported
     */
    public static function invalidEyeStyle(string $style, array $supported): self
    {
        $exception = new self(sprintf('Eye style must be one of the following values: %s. Got: %s', implode(', ', $supported), $style));
        $exception->errorCode = 'INVALID_EYE_STYLE';
        $exception->helperMessage = 'Supported eye styles are: '.implode(', ', $supported);

        return $exception;
    }

    public static function invalidSize(int $size): self
    {
        $exception = new self(sprintf('Size must be greater than 0. Got: %d', $size));
        $exception->errorCode = 'INVALID_SIZE';
        $exception->helperMessage = 'Size specifies the size of the QR code in pixels. Must be greater than 0.';

        return $exception;
    }

    public static function invalidMargin(int $margin): self
    {
        $exception = new self(sprintf('Margin cannot be negative. Got: %d', $margin));
        $exception->errorCode = 'INVALID_MARGIN';
        $exception->helperMessage = 'Margin specifies the empty space (quiet zone) around the QR code. Must be 0 or greater.';

        return $exception;
    }

    /**
     * @param  array<string>  $supported
     */
    public static function invalidGradientType(string $type, array $supported): self
    {
        $exception = new self(sprintf('Gradient type must be one of the following values: %s. Got: %s', implode(', ', $supported), $type));
        $exception->errorCode = 'INVALID_GRADIENT_TYPE';
        $exception->helperMessage = 'Supported gradient types are: '.implode(', ', $supported);

        return $exception;
    }

    public static function invalidImageMergePercentage(float $percentage): self
    {
        $exception = new self(sprintf('Image merge percentage must be between 0 and 1 (exclusive). Got: %f', $percentage));
        $exception->errorCode = 'INVALID_IMAGE_MERGE_PERCENTAGE';
        $exception->helperMessage = 'Image merge percentage must be between 0 and 1 (exclusive).';

        return $exception;
    }

    public static function invalidStyleSize(float $size): self
    {
        $exception = new self(sprintf('Style size must be between 0 and 1. %s given.', $size));
        $exception->errorCode = 'INVALID_STYLE_SIZE';
        $exception->helperMessage = 'Style size specifies the size of the style between 0 and 1.';

        return $exception;
    }

    /**
     * @param  array<string>  $supported
     */
    public static function invalidStyle(string $style, array $supported): self
    {
        $exception = new self(sprintf('Style must be one of the following values: %s. Got: %s', implode(', ', $supported), $style));
        $exception->errorCode = 'INVALID_STYLE';
        $exception->helperMessage = 'Supported styles are: '.implode(', ', $supported);

        return $exception;
    }

    public static function invalidEyeNumber(int $eyeNumber): self
    {
        $exception = new self(sprintf('Eye number must be 0, 1, or 2. Got: %d', $eyeNumber));
        $exception->errorCode = 'INVALID_EYE_NUMBER';
        $exception->helperMessage = 'Eye number specifies the position of the eye (0: top-left, 1: top-right, 2: bottom-left).';

        return $exception;
    }

    public static function invalidGrayscale(int $gray): self
    {
        $exception = new self(sprintf('Gray value must be between 0 and 100. Got: %d', $gray));
        $exception->errorCode = 'INVALID_GRAYSCALE';
        $exception->helperMessage = 'Grayscale value specifies the intensity of the gray color. Must be between 0 and 100.';

        return $exception;
    }

    public static function imagePathOutsideApplication(): self
    {
        $exception = new self('Image file path must be inside the application base path.');
        $exception->errorCode = 'IMAGE_PATH_OUTSIDE_APPLICATION';
        $exception->helperMessage = 'Image file path must be inside the application base path.';

        return $exception;
    }

    public static function imageDoesNotExist(string $path): self
    {
        $exception = new self(sprintf('Image file does not exist or is not readable: %s', $path));
        $exception->errorCode = 'IMAGE_PATH_DOES_NOT_EXIST';
        $exception->helperMessage = 'Image file path must exist.';

        return $exception;
    }

    public static function imageFileNotReadable(string $path): self
    {
        $exception = new self(sprintf('Failed to read image file: %s', $path));
        $exception->errorCode = 'IMAGE_FILE_NOT_READABLE';
        $exception->helperMessage = 'Failed to read image file.';

        return $exception;
    }

    public static function invalidColorChannel(string $channel, int $min, int $max): self
    {
        $exception = new self(sprintf('%s must be between %d and %d.', $channel, $min, $max));
        $exception->errorCode = 'INVALID_COLOR_CHANNEL';
        $exception->helperMessage = 'Check the RGB/CMYK/Alpha values passed to the color methods.';

        return $exception;
    }

    public static function invalidColorFormat(): self
    {
        $exception = new self('Unrecognized color format. Please use an array, a hex string, or a comma-separated RGB string.');
        $exception->errorCode = 'INVALID_COLOR_FORMAT';
        $exception->helperMessage = 'Unrecognized color format. Please use an array, a hex string, or a comma-separated RGB string.';

        return $exception;
    }

    public static function invalidCsvColorString(): self
    {
        $exception = new self('CSV color string must contain exactly 3 or 4 numeric values.');
        $exception->errorCode = 'INVALID_CSV_COLOR_STRING';
        $exception->helperMessage = 'CSV color string must contain exactly 3 or 4 numeric values.';

        return $exception;
    }

    public static function invalidHexColorString(): self
    {
        $exception = new self('Invalid hex color format. Must be 3 or 6 characters.');
        $exception->errorCode = 'INVALID_HEX_COLOR_STRING';
        $exception->helperMessage = 'Invalid hex color format. Must be 3 or 6 characters.';

        return $exception;
    }

    public static function invalidColorArray(): self
    {
        $exception = new self('RGB array values must be numeric. Nested arrays, objects, or invalid strings are not allowed.');
        $exception->errorCode = 'INVALID_COLOR_ARRAY';
        $exception->helperMessage = 'RGB array values must be numeric. Nested arrays, objects, or invalid strings are not allowed.';

        return $exception;
    }

    public static function invalidHexColor(): self
    {
        $exception = new self('Invalid hex color string provided.');
        $exception->errorCode = 'INVALID_HEX_COLOR';
        $exception->helperMessage = 'Invalid hex color string provided.';

        return $exception;
    }
}

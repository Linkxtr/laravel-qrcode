<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Exceptions;

use Linkxtr\QrCode\Exceptions\Concerns\HasHelperMessage;
use RuntimeException;

final class MissingExtensionException extends RuntimeException implements QrCodeExceptionInterface
{
    use HasHelperMessage;

    public static function imagickRequired(string $reason): self
    {
        $exception = new self(sprintf('The Imagick extension is required %s.', $reason));
        $exception->errorCode = 'MISSING_IMAGICK';
        $exception->helperMessage = 'Install the ext-imagick PHP extension, or switch to the default SVG format which requires no extensions.';

        return $exception;
    }

    public static function gdRequired(string $reason): self
    {
        $exception = new self(sprintf('The GD extension is required %s.', $reason));
        $exception->errorCode = 'MISSING_GD';
        $exception->helperMessage = 'Install the ext-gd PHP extension, or switch to the default SVG format which requires no extensions.';

        return $exception;
    }

    public static function neitherImagickNorGdAvailable(): self
    {
        $exception = new self('The imagick or gd extension is required to generate raster QR codes.');
        $exception->errorCode = 'MISSING_IMAGICK_AND_GD';
        $exception->helperMessage = 'Install either the ext-imagick or ext-gd PHP extension, or switch to the default SVG format which requires no extensions.';

        return $exception;
    }
}

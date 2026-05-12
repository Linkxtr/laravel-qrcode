<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Exceptions;

use ImagickException;
use Linkxtr\QrCode\Exceptions\Concerns\HasHelperMessage;
use RuntimeException;

final class ImageMergeException extends RuntimeException implements QrCodeExceptionInterface
{
    use HasHelperMessage;

    public static function invalidPercentage(): self
    {
        $exception = new self('Percentage for merging the image must be between 0 and 1.');
        $exception->errorCode = 'INVALID_MERGE_PERCENTAGE';
        $exception->helperMessage = 'The percentage provided for merge must be between 0 and 1.';

        return $exception;
    }

    public static function invalidImage(?string $details = null): self
    {
        $exception = new self('Invalid image provided for merge. '.($details ?? ''));
        $exception->errorCode = 'INVALID_MERGE_IMAGE';
        $exception->helperMessage = 'The image provided for merge is invalid. '.($details ?? '');

        return $exception;
    }

    public static function mergeFileNotFound(string $path): self
    {
        $exception = new self('Image file does not exist or is not readable.');
        $exception->errorCode = 'MERGE_FILE_NOT_FOUND';
        $exception->helperMessage = sprintf('Attempted to load merge file from: %s. Make sure the file exists and has proper permissions.', $path);

        return $exception;
    }

    public static function couldNotDetermineEpsDimensions(): self
    {
        $exception = new self('Could not determine EPS dimensions (Missing %%BoundingBox).');
        $exception->errorCode = 'EPS_DIMENSIONS_MISSING';
        $exception->helperMessage = 'The EPS content is missing the %%BoundingBox comment required to determine dimensions.';

        return $exception;
    }

    public static function failedToCreateResizedLogoCanvas(): self
    {
        $exception = new self('Failed to create resized logo canvas.');
        $exception->errorCode = 'FAILED_TO_CREATE_RESIZED_LOGO_CANVAS';
        $exception->helperMessage = 'The image provided for merge is invalid.';

        return $exception;
    }

    public static function couldNotAllocateWhiteColor(): self
    {
        $exception = new self('Could not allocate white color for the logo.');
        $exception->errorCode = 'COULD_NOT_ALLOCATE_WHITE_COLOR';
        $exception->helperMessage = 'Could not allocate white color for the logo.';

        return $exception;
    }

    public static function failedToCaptureHexDataFromOutputBuffer(): self
    {
        $exception = new self('Failed to capture hex data from output buffer.');
        $exception->errorCode = 'FAILED_TO_CAPTURE_HEX_DATA_FROM_OUTPUT_BUFFER';
        $exception->helperMessage = 'Failed to capture hex data from output buffer.';

        return $exception;
    }

    public static function imagickException(ImagickException $imagickException): self
    {
        $imagickException = new self('Imagick merge failed: '.$imagickException->getMessage(), $imagickException->getCode(), $imagickException);
        $imagickException->errorCode = 'IMAGICK_EXCEPTION';
        $imagickException->helperMessage = 'Imagick merge failed: '.$imagickException->getMessage();

        return $imagickException;
    }

    public static function unsupportedFormat(string $message): self
    {
        $exception = new self($message);
        $exception->errorCode = 'UNSUPPORTED_FORMAT';
        $exception->helperMessage = $message;

        return $exception;
    }

    public static function mergeImageDimensionsCannotBeZero(): self
    {
        $exception = new self('Merge image dimensions cannot be zero.');
        $exception->errorCode = 'MERGE_IMAGE_DIMENSIONS_CANNOT_BE_ZERO';
        $exception->helperMessage = 'Merge image dimensions cannot be zero.';

        return $exception;
    }

    public static function mergeCanvasCreationFailed(): self
    {
        $exception = new self('Failed to create image canvas.');
        $exception->errorCode = 'MERGE_CANVAS_CREATION_FAILED';
        $exception->helperMessage = 'Failed to create image canvas.';

        return $exception;
    }

    public static function transparentColorCannotBeCreated(): self
    {
        $exception = new self('Failed to create transparent color.');
        $exception->errorCode = 'TRANSPARENT_COLOR_CANNOT_BE_CREATED';
        $exception->helperMessage = 'Failed to create transparent color.';

        return $exception;
    }

    public static function mergeImageFillFailed(): self
    {
        $exception = new self('Failed to fill image with transparent color.');
        $exception->errorCode = 'MERGE_IMAGE_FILL_FAILED';
        $exception->helperMessage = 'Failed to fill image with transparent color.';

        return $exception;
    }

    public static function failedToSaveAlphaChannelInformation(): self
    {
        $exception = new self('Failed to save alpha channel information.');
        $exception->errorCode = 'FAILED_TO_SAVE_ALPHA_CHANNEL_INFORMATION';
        $exception->helperMessage = 'Failed to save alpha channel information.';

        return $exception;
    }

    public static function failedToRenderImageBinary(): self
    {
        $exception = new self('Failed to render image binary.');
        $exception->errorCode = 'FAILED_TO_RENDER_IMAGE_BINARY';
        $exception->helperMessage = 'Failed to render image binary.';

        return $exception;
    }

    public static function failedToCopySourceImageToCanvas(int $sourceWidth, int $sourceHeight): self
    {
        $message = sprintf('Failed to copy source image to canvas (Source: %dx%d).', $sourceWidth, $sourceHeight);
        $exception = new self($message);
        $exception->errorCode = 'FAILED_TO_COPY_SOURCE_IMAGE_TO_CANVAS';
        $exception->helperMessage = $message;

        return $exception;
    }

    public static function failedToCopyResampleMergeImage(int $targetLogoWidth, int $targetLogoHeight, int $mergeWidth, int $mergeHeight): self
    {
        $message = sprintf('Failed to copy/resample merge image (Target: %dx%d, Source: %dx%d).', $targetLogoWidth, $targetLogoHeight, $mergeWidth, $mergeHeight);
        $exception = new self($message);
        $exception->errorCode = 'FAILED_TO_COPY_RESAMPLE_MERGE_IMAGE';
        $exception->helperMessage = $message;

        return $exception;
    }

    public static function invalidImageData(): self
    {
        $exception = new self('Invalid image data provided to Image.');
        $exception->errorCode = 'INVALID_IMAGE_DATA';
        $exception->helperMessage = 'Invalid image data provided to Image.';

        return $exception;
    }

    public static function couldNotDetermineSvgDimensions(): self
    {
        $exception = new self('Could not determine SVG dimensions. Ensure the SVG has width and height attributes.');
        $exception->errorCode = 'COULD_NOT_DETERMINE_SVG_DIMENSIONS';
        $exception->helperMessage = 'Could not determine SVG dimensions. Ensure the SVG has width and height attributes.';

        return $exception;
    }

    public static function invalidSvgContent(): self
    {
        $exception = new self('Invalid SVG content: closing tag not found.');
        $exception->errorCode = 'INVALID_SVG_CONTENT';
        $exception->helperMessage = 'Invalid SVG content: closing tag not found.';

        return $exception;
    }
}

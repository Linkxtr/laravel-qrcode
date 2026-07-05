<?php

declare(strict_types=1);

use Linkxtr\QrCode\Exceptions\ImageMergeException;

covers(ImageMergeException::class);

test('invalidPercentage sets correct error code and message', function (): void {
    $imageMergeException = ImageMergeException::invalidPercentage();

    expect($imageMergeException)
        ->toBeInstanceOf(ImageMergeException::class)
        ->and($imageMergeException->getErrorCode())
        ->toBe('INVALID_MERGE_PERCENTAGE')
        ->and($imageMergeException->getHelperMessage())
        ->toBe('The percentage provided for merge must be between 0 and 1.');
});

test('invalidImage sets correct error code and message', function (): void {
    $imageMergeException = ImageMergeException::invalidImage();

    expect($imageMergeException)
        ->toBeInstanceOf(ImageMergeException::class)
        ->and($imageMergeException->getMessage())
        ->toBe('Invalid image provided for merge. ')
        ->and($imageMergeException->getErrorCode())
        ->toBe('INVALID_MERGE_IMAGE')
        ->and($imageMergeException->getHelperMessage())
        ->toBe('The image provided for merge is invalid. ');

    $imageMergeException = ImageMergeException::invalidImage('details');

    expect($imageMergeException)
        ->toBeInstanceOf(ImageMergeException::class)
        ->and($imageMergeException->getMessage())
        ->toBe('Invalid image provided for merge. details')
        ->and($imageMergeException->getErrorCode())
        ->toBe('INVALID_MERGE_IMAGE')
        ->and($imageMergeException->getHelperMessage())
        ->toBe('The image provided for merge is invalid. details');
});

test('mergeFileNotFound sets correct error code and message', function (): void {
    $imageMergeException = ImageMergeException::mergeFileNotFound('test');

    expect($imageMergeException)
        ->toBeInstanceOf(ImageMergeException::class)
        ->and($imageMergeException->getErrorCode())
        ->toBe('MERGE_FILE_NOT_FOUND')
        ->and($imageMergeException->getHelperMessage())
        ->toBe('Attempted to load merge file from: test. Make sure the file exists and has proper permissions.');
});

test('couldNotDetermineEpsDimensions sets correct error code and message', function (): void {
    $imageMergeException = ImageMergeException::couldNotDetermineEpsDimensions();

    expect($imageMergeException)
        ->toBeInstanceOf(ImageMergeException::class)
        ->and($imageMergeException->getErrorCode())
        ->toBe('EPS_DIMENSIONS_MISSING')
        ->and($imageMergeException->getHelperMessage())
        ->toBe('The EPS content is missing the %%BoundingBox comment required to determine dimensions.');
});

test('failedToCreateResizedLogoCanvas sets correct error code and message', function (): void {
    $imageMergeException = ImageMergeException::failedToCreateResizedLogoCanvas();

    expect($imageMergeException)
        ->toBeInstanceOf(ImageMergeException::class)
        ->and($imageMergeException->getErrorCode())
        ->toBe('FAILED_TO_CREATE_RESIZED_LOGO_CANVAS')
        ->and($imageMergeException->getHelperMessage())
        ->toBe('The image provided for merge is invalid.');
});

test('couldNotAllocateWhiteColor sets correct error code and message', function (): void {
    $imageMergeException = ImageMergeException::couldNotAllocateWhiteColor();

    expect($imageMergeException)
        ->toBeInstanceOf(ImageMergeException::class)
        ->and($imageMergeException->getErrorCode())
        ->toBe('COULD_NOT_ALLOCATE_WHITE_COLOR')
        ->and($imageMergeException->getHelperMessage())
        ->toBe('Could not allocate white color for the logo.');
});

test('failedToCaptureHexDataFromOutputBuffer sets correct error code and message', function (): void {
    $imageMergeException = ImageMergeException::failedToCaptureHexDataFromOutputBuffer();

    expect($imageMergeException)
        ->toBeInstanceOf(ImageMergeException::class)
        ->and($imageMergeException->getErrorCode())
        ->toBe('FAILED_TO_CAPTURE_HEX_DATA_FROM_OUTPUT_BUFFER')
        ->and($imageMergeException->getHelperMessage())
        ->toBe('Failed to capture hex data from output buffer.');
});

test('imagickException sets correct error code and message', function (): void {
    $imageMergeException = ImageMergeException::imagickException(new ImagickException('error'));

    expect($imageMergeException)
        ->toBeInstanceOf(ImageMergeException::class)
        ->and($imageMergeException->getErrorCode())
        ->toBe('IMAGICK_EXCEPTION')
        ->and($imageMergeException->getHelperMessage())
        ->toBe('Imagick merge failed: Imagick merge failed: error');
});

test('unsupportedFormat sets correct error code and message', function (): void {
    $imageMergeException = ImageMergeException::unsupportedFormat('test');

    expect($imageMergeException)
        ->toBeInstanceOf(ImageMergeException::class)
        ->and($imageMergeException->getErrorCode())
        ->toBe('UNSUPPORTED_FORMAT')
        ->and($imageMergeException->getHelperMessage())
        ->toBe('test');
});

test('mergeImageDimensionsCannotBeZero sets correct error code and message', function (): void {
    $imageMergeException = ImageMergeException::mergeImageDimensionsCannotBeZero();

    expect($imageMergeException)
        ->toBeInstanceOf(ImageMergeException::class)
        ->and($imageMergeException->getErrorCode())
        ->toBe('MERGE_IMAGE_DIMENSIONS_CANNOT_BE_ZERO')
        ->and($imageMergeException->getHelperMessage())
        ->toBe('Merge image dimensions cannot be zero.');
});

test('mergeCanvasCreationFailed sets correct error code and message', function (): void {
    $imageMergeException = ImageMergeException::mergeCanvasCreationFailed();

    expect($imageMergeException)
        ->toBeInstanceOf(ImageMergeException::class)
        ->and($imageMergeException->getErrorCode())
        ->toBe('MERGE_CANVAS_CREATION_FAILED')
        ->and($imageMergeException->getHelperMessage())
        ->toBe('Failed to create image canvas.');
});

test('transparentColorCannotBeCreated sets correct error code and message', function (): void {
    $imageMergeException = ImageMergeException::transparentColorCannotBeCreated();

    expect($imageMergeException)
        ->toBeInstanceOf(ImageMergeException::class)
        ->and($imageMergeException->getErrorCode())
        ->toBe('TRANSPARENT_COLOR_CANNOT_BE_CREATED')
        ->and($imageMergeException->getHelperMessage())
        ->toBe('Failed to create transparent color.');
});

test('failedToRenderImageBinary sets correct error code and message', function (): void {
    $imageMergeException = ImageMergeException::failedToRenderImageBinary();

    expect($imageMergeException)
        ->toBeInstanceOf(ImageMergeException::class)
        ->and($imageMergeException->getErrorCode())
        ->toBe('FAILED_TO_RENDER_IMAGE_BINARY')
        ->and($imageMergeException->getHelperMessage())
        ->toBe('Failed to render image binary.');
});

test('invalidImageData sets correct error code and message', function (): void {
    $imageMergeException = ImageMergeException::invalidImageData();

    expect($imageMergeException)
        ->toBeInstanceOf(ImageMergeException::class)
        ->and($imageMergeException->getErrorCode())
        ->toBe('INVALID_IMAGE_DATA')
        ->and($imageMergeException->getHelperMessage())
        ->toBe('Invalid image data provided to Image.');
});

test('couldNotDetermineSvgDimensions sets correct error code and message', function (): void {
    $imageMergeException = ImageMergeException::couldNotDetermineSvgDimensions();

    expect($imageMergeException)
        ->toBeInstanceOf(ImageMergeException::class)
        ->and($imageMergeException->getErrorCode())
        ->toBe('COULD_NOT_DETERMINE_SVG_DIMENSIONS')
        ->and($imageMergeException->getHelperMessage())
        ->toBe('Could not determine SVG dimensions. Ensure the SVG has width and height attributes.');
});

test('invalidSvgContent sets correct error code and message', function (): void {
    $imageMergeException = ImageMergeException::invalidSvgContent();

    expect($imageMergeException)
        ->toBeInstanceOf(ImageMergeException::class)
        ->and($imageMergeException->getErrorCode())
        ->toBe('INVALID_SVG_CONTENT')
        ->and($imageMergeException->getHelperMessage())
        ->toBe('Invalid SVG content: closing tag not found.');
});

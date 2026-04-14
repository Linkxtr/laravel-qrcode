<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Support;

use ErrorException;
use GdImage;
use InvalidArgumentException;

final readonly class Image
{
    private GdImage $gdImage;

    /**
     * @param  string  $image  Raw binary image data (not a file path).
     */
    public function __construct(string $image)
    {
        try {
            $img = imagecreatefromstring($image);
        } catch (ErrorException $errorException) {
            throw new InvalidArgumentException('Invalid image data provided to Image.', $errorException->getCode(), previous: $errorException);
        }

        if ($img === false) {
            throw new InvalidArgumentException('Invalid image data provided to Image.');
        }

        $this->gdImage = $img;
    }

    /** @return int<0, max> */
    public function getWidth(): int
    {
        return imagesx($this->gdImage);
    }

    /** @return int<0, max> */
    public function getHeight(): int
    {
        return imagesy($this->gdImage);
    }

    public function getImageResource(): GdImage
    {
        return $this->gdImage;
    }
}

<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Support;

use ErrorException;
use GdImage;
use InvalidArgumentException;
use RuntimeException;

final class Image
{
    private ?GdImage $gdImage = null;

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

    public function __destruct()
    {
        $this->gdImage = null;
    }

    /** @return int<0, max>|false */
    public function getWidth(): int|false
    {
        if (! $this->gdImage instanceof GdImage) {
            throw new RuntimeException('Image resource has been released.');
        }

        return imagesx($this->gdImage);
    }

    /** @return int<0, max>|false */
    public function getHeight(): int|false
    {
        if (! $this->gdImage instanceof GdImage) {
            throw new RuntimeException('Image resource has been released.');
        }

        return imagesy($this->gdImage);
    }

    public function getImageResource(): GdImage
    {
        if (! $this->gdImage instanceof GdImage) {
            throw new RuntimeException('Image resource has been released.');
        }

        return $this->gdImage;
    }

    public function setImageResource(GdImage $gdImage): void
    {
        $this->gdImage = $gdImage;
    }
}

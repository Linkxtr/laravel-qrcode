<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Support;

use ErrorException;
use GdImage;
use InvalidArgumentException;
use RuntimeException;

final class Image
{
    private ?GdImage $image = null;

    public function __construct(string $image)
    {
        try {
            $img = imagecreatefromstring($image);
        } catch (ErrorException $e) {
            throw new InvalidArgumentException('Invalid image data provided to Image.', $e->getCode(), previous: $e);
        }

        $this->image = $img ?: null;
    }

    public function __destruct()
    {
        $this->image = null;
    }

    /** @return int<1, max> */
    public function getWidth(): int
    {
        if (!$this->image instanceof \GdImage) {
            throw new RuntimeException('Image resource has been released.');
        }

        return imagesx($this->image);
    }

    /** @return int<1, max> */
    public function getHeight(): int
    {
        if (!$this->image instanceof \GdImage) {
            throw new RuntimeException('Image resource has been released.');
        }

        return imagesy($this->image);
    }

    public function getImageResource(): GdImage
    {
        if (!$this->image instanceof \GdImage) {
            throw new RuntimeException('Image resource has been released.');
        }

        return $this->image;
    }

    public function setImageResource(GdImage $image): void
    {
        $this->image = $image;
    }
}

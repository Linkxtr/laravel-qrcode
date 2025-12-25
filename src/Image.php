<?php

namespace Linkxtr\QrCode;

use GdImage;

class Image
{
    protected ?GdImage $image = null;

    public function __construct(string $image)
    {
        set_error_handler(null);
        $img = imagecreatefromstring($image);
        restore_error_handler();

        if ($img === false) {
            throw new \InvalidArgumentException('Invalid image data provided to Image.');
        }

        $this->image = $img;
    }

    public function __destruct()
    {
        $this->image = null;
    }

    /** @return int<1, max> */
    public function getWidth(): int
    {
        if ($this->image === null) {
            throw new \RuntimeException('Image resource has been released.');
        }

        return imagesx($this->image);
    }

    /** @return int<1, max> */
    public function getHeight(): int
    {
        if ($this->image === null) {
            throw new \RuntimeException('Image resource has been released.');
        }

        return imagesy($this->image);
    }

    public function getImageResource(): GdImage
    {
        if ($this->image === null) {
            throw new \RuntimeException('Image resource has been released.');
        }

        return $this->image;
    }

    public function setImageResource(GdImage $image): void
    {
        $this->image = $image;
    }
}

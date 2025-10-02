<?php

namespace Linkxtr\QrCode;

use GdImage;

class Image
{
    protected GdImage $image;

    public function __construct(string $image)
    {
        $img = @imagecreatefromstring($image);
        
        if ($img === false) {
            throw new \InvalidArgumentException('Invalid image data provided to Image.');
        }

        $this->image = $img;
    }

    public function getWidth(): int
    {
        return imagesx($this->image);
    }

    public function getHeight(): int
    {
        return imagesy($this->image);
    }

    public function getImageResource(): GdImage
    {
        return $this->image;
    }

    public function setImageResource(GdImage $image): void
    {
        $this->image = $image;
    }
}

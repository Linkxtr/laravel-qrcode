<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Support;

use GdImage;
use Linkxtr\QrCode\Exceptions\ImageMergeException;

final readonly class Image
{
    private GdImage $gdImage;

    /**
     * @param  string  $image  Raw binary image data (not a file path).
     */
    public function __construct(string $image)
    {
        $img = @imagecreatefromstring($image);

        if (! $img) {
            throw ImageMergeException::invalidImageData();
        }

        $this->gdImage = $img;
    }

    public function getWidth(): int
    {
        return imagesx($this->gdImage);
    }

    public function getHeight(): int
    {
        return imagesy($this->gdImage);
    }

    public function getImageResource(): GdImage
    {
        return $this->gdImage;
    }
}

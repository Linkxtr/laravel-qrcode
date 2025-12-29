<?php

namespace Linkxtr\QrCode\Renderer\Image;

use GdImage;

// Globals to control mocks are assumed to be defined in the test file scope or global scope
// Note: We need to access them via global keyword

function imagecreatetruecolor(int $width, int $height)
{
    global $mockImageCreateTrueColor;
    if (isset($mockImageCreateTrueColor) && ! $mockImageCreateTrueColor) {
        return false;
    }

    return \imagecreatetruecolor($width, $height);
}

function imagecolorallocate(GdImage $image, int $red, int $green, int $blue)
{
    global $mockImageColorAllocate;
    if (isset($mockImageColorAllocate) && ! $mockImageColorAllocate) {
        return false;
    }

    return \imagecolorallocate($image, $red, $green, $blue);
}

function imagecolorallocatealpha(GdImage $image, int $red, int $green, int $blue, int $alpha)
{
    global $mockImageColorAllocate;
    if (isset($mockImageColorAllocate) && ! $mockImageColorAllocate) {
        return false;
    }

    return \imagecolorallocatealpha($image, $red, $green, $blue, $alpha);
}

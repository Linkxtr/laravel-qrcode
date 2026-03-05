<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Mergers;

use GdImage;
use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\MergerInterface;
use Linkxtr\QrCode\Support\Image;
use RuntimeException;

final readonly class RasterMerger implements MergerInterface
{
    public function __construct(
        private Image $sourceImage,
        private Image $mergeImage,
        private string $format = 'png',
        private float $percentage = 0.2
    ) {
        if (! in_array($this->format, ['png', 'webp'])) {
            throw new InvalidArgumentException('RasterMerger only supports "png" or "webp" formats.');
        }

        if ($this->percentage <= 0 || $this->percentage >= 1) {
            throw new InvalidArgumentException('$percentage must be between 0 and 1');
        }
    }

    public function merge(): string
    {

        $sourceWidth = $this->sourceImage->getWidth();
        $sourceHeight = $this->sourceImage->getHeight();

        if ($sourceWidth === 0 || $sourceHeight === 0) {
            throw new InvalidArgumentException('Source image has zero width or height.');
        }

        $mergeWidth = $this->mergeImage->getWidth();
        $mergeHeight = $this->mergeImage->getHeight();

        if ($mergeWidth === 0 || $mergeHeight === 0) {
            throw new InvalidArgumentException('Merge image has zero width or height.');
        }

        $mergeRatio = $mergeWidth / $mergeHeight;

        $targetLogoWidth = max(1, (int) ($sourceWidth * $this->percentage));
        $targetLogoHeight = max(1, (int) ($targetLogoWidth / $mergeRatio));

        // Constrain to canvas if logo exceeds vertical bounds
        if ($targetLogoHeight > $sourceHeight * $this->percentage) {
            $targetLogoHeight = max(1, (int) ($sourceHeight * $this->percentage));
            $targetLogoWidth = max(1, (int) ($targetLogoHeight * $mergeRatio));
        }

        $centerX = (int) (($sourceWidth - $targetLogoWidth) / 2);
        $centerY = (int) (($sourceHeight - $targetLogoHeight) / 2);

        $canvas = imagecreatetruecolor($sourceWidth, $sourceHeight);

        if (! $canvas) {
            throw new RuntimeException('Failed to create image canvas.');
        }

        imagealphablending($canvas, false);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);

        if (! $transparent) {
            throw new RuntimeException('Failed to create transparent color.');
        }

        if (! imagefill($canvas, 0, 0, $transparent)) {
            throw new RuntimeException('Failed to fill image with transparent color.');
        }

        imagealphablending($canvas, true);

        if (! imagecopy(
            $canvas,
            $this->sourceImage->getImageResource(),
            0, 0, 0, 0,
            $sourceWidth,
            $sourceHeight
        )) {
            throw new RuntimeException(sprintf('Failed to copy source image to canvas (Source: %dx%d).', $sourceWidth, $sourceHeight));
        }

        if (! imagecopyresampled(
            $canvas,
            $this->mergeImage->getImageResource(),
            $centerX, $centerY,
            0, 0,
            $targetLogoWidth, $targetLogoHeight,
            $mergeWidth, $mergeHeight
        )) {
            throw new RuntimeException(sprintf('Failed to copy/resample merge image (Target: %dx%d, Source: %dx%d).', $targetLogoWidth, $targetLogoHeight, $mergeWidth, $mergeHeight));
        }

        if (! imagesavealpha($canvas, true)) {
            throw new RuntimeException('Failed to save alpha channel information.');
        }

        return $this->createOutput($canvas);
    }

    private function createOutput(GdImage $gdImage): string
    {
        ob_start();

        if ($this->format === 'webp') {
            imagewebp($gdImage, null, 90);
        } else {
            imagepng($gdImage);
        }

        $content = ob_get_clean();

        unset($gdImage);

        if ($content === false) {
            throw new RuntimeException('Failed to capture image output from buffer.');
        }

        return $content;
    }
}

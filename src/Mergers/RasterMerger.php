<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Mergers;

use GdImage;
use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\MergerInterface;
use Linkxtr\QrCode\Support\Image;
use RuntimeException;

final class RasterMerger implements MergerInterface
{
    public function __construct(
        private Image $sourceImage,
        private Image $mergeImage,
        private string $format = 'png',
        private float $percentage = 0.2
    ) {
        if (! in_array($this->format, ['png', 'webp'])) {
            throw new InvalidArgumentException('ImageMerge only supports "png" or "webp" formats.');
        }

        if ($this->percentage <= 0 || $this->percentage > 1) {
            throw new InvalidArgumentException('$percentage must be between 0 and 1');
        }
    }

    public function merge(): string
    {

        $sourceWidth = $this->sourceImage->getWidth();
        $sourceHeight = $this->sourceImage->getHeight();
        $mergeWidth = $this->mergeImage->getWidth();
        $mergeHeight = $this->mergeImage->getHeight();
        $mergeRatio = $mergeWidth / $mergeHeight;

        $targetLogoWidth = (int) ($sourceWidth * $this->percentage);
        $targetLogoHeight = (int) ($targetLogoWidth / $mergeRatio);
        $centerX = (int) (($sourceWidth - $targetLogoWidth) / 2);
        $centerY = (int) (($sourceHeight - $targetLogoHeight) / 2);

        $canvas = imagecreatetruecolor($sourceWidth, $sourceHeight);

        if (! $canvas) {
            throw new RuntimeException('Failed to create image canvas.');
        }

        imagealphablending($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);

        if (! $transparent) {
            throw new RuntimeException('Failed to create transparent color.');
        }

        imagefill($canvas, 0, 0, $transparent);
        imagecopy(
            $canvas,
            $this->sourceImage->getImageResource(),
            0, 0, 0, 0,
            $sourceWidth,
            $sourceHeight
        );

        imagecopyresampled(
            $canvas,
            $this->mergeImage->getImageResource(),
            $centerX, $centerY,
            0, 0,
            $targetLogoWidth, $targetLogoHeight,
            $mergeWidth, $mergeHeight
        );

        imagesavealpha($canvas, true);

        return $this->createOutput($canvas);
    }

    private function createOutput(GdImage $canvas): string
    {
        ob_start();

        if ($this->format === 'webp') {
            imagewebp($canvas, null, 90);
        } else {
            imagepng($canvas);
        }

        $content = ob_get_clean();

        unset($canvas);

        return $content ?: '';
    }
}

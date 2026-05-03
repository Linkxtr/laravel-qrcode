<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Mergers;

use GdImage;
use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\MergerInterface;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Support\Image;
use LogicException;
use RuntimeException;

final class RasterMerger implements MergerInterface
{
    private Format $format = Format::PNG;

    private readonly Image $sourceImage;

    private readonly Image $mergeImage;

    public function __construct(
        string $sourceImage,
        string $mergeImage,
        private readonly float $percentage = 0.2
    ) {
        if ($this->percentage <= 0 || $this->percentage >= 1) {
            throw new InvalidArgumentException('$percentage must be between 0 and 1');
        }

        $this->sourceImage = new Image($sourceImage);
        $this->mergeImage = new Image($mergeImage);
    }

    public function setFormat(Format $format): self
    {
        if (! in_array($format, [Format::PNG, Format::WEBP], true)) {
            throw new InvalidArgumentException('RasterMerger only supports "png" or "webp" formats.');
        }

        $this->format = $format;

        return $this;
    }

    public function merge(): string
    {
        $sourceWidth = $this->sourceImage->getWidth();
        $sourceHeight = $this->sourceImage->getHeight();

        $mergeWidth = $this->mergeImage->getWidth();
        $mergeHeight = $this->mergeImage->getHeight();

        // @codeCoverageIgnoreStart
        if ($mergeWidth === 0 || $mergeHeight === 0) {
            throw new InvalidArgumentException('Merge image dimensions cannot be zero.');
        }

        // @codeCoverageIgnoreEnd

        $mergeRatio = $mergeWidth / $mergeHeight;

        $targetLogoWidth = max(1, (int) ($sourceWidth * $this->percentage)); // @pest-mutate-ignore
        $targetLogoHeight = max(1, (int) ($targetLogoWidth / $mergeRatio)); // @pest-mutate-ignore

        // Constrain to canvas if logo exceeds vertical bounds
        if ($targetLogoHeight > $sourceHeight * $this->percentage) {
            $targetLogoHeight = max(1, (int) ($sourceHeight * $this->percentage));
            $targetLogoWidth = max(1, (int) ($targetLogoHeight * $mergeRatio));
        }

        $centerX = (int) (($sourceWidth - $targetLogoWidth) / 2); // @pest-mutate-ignore
        $centerY = (int) (($sourceHeight - $targetLogoHeight) / 2); // @pest-mutate-ignore

        $canvas = imagecreatetruecolor($sourceWidth, $sourceHeight);

        if (! $canvas) {
            throw new RuntimeException('Failed to create image canvas.');
        }

        imagealphablending($canvas, false); // @pest-mutate-ignore
        imagesavealpha($canvas, true); // @pest-mutate-ignore
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127); // @pest-mutate-ignore

        if (! $transparent) {
            throw new RuntimeException('Failed to create transparent color.');
        }

        if (! imagefill($canvas, 0, 0, $transparent)) { // @pest-mutate-ignore
            throw new RuntimeException('Failed to fill image with transparent color.');
        }

        imagealphablending($canvas, true); // @pest-mutate-ignore

        if (! imagecopy(
            $canvas,
            $this->sourceImage->getImageResource(),
            0, 0, 0, 0, // @pest-mutate-ignore
            $sourceWidth,
            $sourceHeight
        )) {
            throw new RuntimeException(sprintf('Failed to copy source image to canvas (Source: %dx%d).', $sourceWidth, $sourceHeight));
        }

        if (! imagecopyresampled(
            $canvas,
            $this->mergeImage->getImageResource(),
            $centerX, $centerY,
            0, 0, // @pest-mutate-ignore
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

        $success = match ($this->format) {
            Format::WEBP => imagewebp($gdImage, null, 90), // @pest-mutate-ignore
            Format::PNG => imagepng($gdImage),
            default => throw new LogicException('RasterMerger only supports "png" or "webp" formats.'),
        };

        $content = ob_get_clean();

        unset($gdImage);

        if (! $success || $content === false || $content === '') {
            throw new RuntimeException('Failed to render image binary.');
        }

        return $content;
    }
}

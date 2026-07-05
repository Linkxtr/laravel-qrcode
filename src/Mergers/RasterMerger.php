<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Mergers;

use GdImage;
use Linkxtr\QrCode\Contracts\MergerInterface;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Exceptions\ImageMergeException;
use Linkxtr\QrCode\Support\Image;

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
            throw ImageMergeException::invalidPercentage();
        }

        $this->sourceImage = new Image($sourceImage);
        $this->mergeImage = new Image($mergeImage);
    }

    public function setFormat(Format $format): self
    {
        if (! in_array($format, [Format::PNG, Format::WEBP], true)) {
            throw ImageMergeException::unsupportedFormat('RasterMerger only supports "png" or "webp" formats.');
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

        $canvas = imagecreatetruecolor(max(1, $sourceWidth), max(1, $sourceHeight));

        if (! $canvas) {
            throw ImageMergeException::mergeCanvasCreationFailed();
        }

        imagealphablending($canvas, false); // @pest-mutate-ignore
        imagesavealpha($canvas, true); // @pest-mutate-ignore
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127); // @pest-mutate-ignore

        if (! $transparent) {
            throw ImageMergeException::transparentColorCannotBeCreated();
        }

        imagefill($canvas, 0, 0, $transparent); // @pest-mutate-ignore

        imagealphablending($canvas, true); // @pest-mutate-ignore

        imagecopy(
            $canvas,
            $this->sourceImage->getImageResource(),
            0, 0, 0, 0, // @pest-mutate-ignore
            $sourceWidth,
            $sourceHeight
        );

        imagecopyresampled(
            $canvas,
            $this->mergeImage->getImageResource(),
            $centerX, $centerY,
            0, 0, // @pest-mutate-ignore
            $targetLogoWidth, $targetLogoHeight,
            $mergeWidth, $mergeHeight
        );

        imagesavealpha($canvas, true); // @pest-mutate-ignore

        return $this->createOutput($canvas);
    }

    private function createOutput(GdImage $gdImage): string
    {
        ob_start();

        $success = match ($this->format) {
            Format::WEBP => imagewebp($gdImage, null, 90), // @pest-mutate-ignore
            Format::PNG => imagepng($gdImage),
            default => throw ImageMergeException::unsupportedFormat('RasterMerger only supports "png" or "webp" formats.'),
        };

        $content = ob_get_clean();

        unset($gdImage);

        if (! $success || $content === '') {
            throw ImageMergeException::failedToRenderImageBinary();
        }

        return $content;
    }
}

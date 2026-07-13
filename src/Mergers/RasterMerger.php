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
    private const ORIGIN = 0;

    private const WEBP_COMPRESSION = 90;

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

        $boxW = $sourceWidth * $this->percentage;
        $boxH = $sourceHeight * $this->percentage;

        $scale = min($boxW / $mergeWidth, $boxH / $mergeHeight);

        $targetLogoWidth = max(1, (int) ($mergeWidth * $scale));
        $targetLogoHeight = max(1, (int) ($mergeHeight * $scale));

        $centerX = (int) (($sourceWidth - $targetLogoWidth) / 2);
        $centerY = (int) (($sourceHeight - $targetLogoHeight) / 2);

        $gdImage = $this->sourceImage->getImageResource();

        imagecopyresampled(
            $gdImage,
            $this->mergeImage->getImageResource(),
            $centerX, $centerY,
            self::ORIGIN, self::ORIGIN,
            $targetLogoWidth, $targetLogoHeight,
            $mergeWidth, $mergeHeight
        );

        imagesavealpha($gdImage, true);

        return $this->createOutput($gdImage);
    }

    private function createOutput(GdImage $gdImage): string
    {
        ob_start();

        $success = match ($this->format) {
            Format::WEBP => imagewebp($gdImage, null, self::WEBP_COMPRESSION),
            Format::PNG => imagepng($gdImage),
            default => throw ImageMergeException::unsupportedFormat('RasterMerger only supports "png" or "webp" formats.'),
        };

        $content = ob_get_clean();

        unset($gdImage);

        if (! $success || $content === false || $content === '') { // @phpstan-ignore identical.alwaysFalse
            throw ImageMergeException::failedToRenderImageBinary();
        }

        return $content;
    }
}

<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Mergers;

use Imagick;
use ImagickException;
use Linkxtr\QrCode\Contracts\MergerInterface;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Exceptions\ImageMergeException;

final class ImagickMerger implements MergerInterface
{
    private Format $format = Format::PNG;

    public function __construct(
        private readonly string $sourceImageContent,
        private readonly string $mergeImageContent,
        private readonly float $percentage = 0.2
    ) {
        if ($this->percentage <= 0 || $this->percentage >= 1) {
            throw ImageMergeException::invalidPercentage();
        }
    }

    public function setFormat(Format $format): self
    {
        if (! in_array($format, [Format::PNG, Format::WEBP], true)) {
            throw ImageMergeException::unsupportedFormat('ImagickMerger only supports "png" or "webp" formats.');
        }

        $this->format = $format;

        return $this;
    }

    public function merge(): string
    {
        $source = new Imagick;
        $merge = new Imagick;

        try {
            $source->readImageBlob($this->sourceImageContent);
            $merge->readImageBlob($this->mergeImageContent);

            $sourceWidth = $source->getImageWidth();
            $sourceHeight = $source->getImageHeight();

            $mergeWidth = $merge->getImageWidth();
            $mergeHeight = $merge->getImageHeight();

            $boxW = $sourceWidth * $this->percentage;
            $boxH = $sourceHeight * $this->percentage;

            $scale = min($boxW / $mergeWidth, $boxH / $mergeHeight);

            $targetLogoWidth = max(1, (int) ($mergeWidth * $scale));
            $targetLogoHeight = max(1, (int) ($mergeHeight * $scale));

            $centerX = (int) (($sourceWidth - $targetLogoWidth) / 2);
            $centerY = (int) (($sourceHeight - $targetLogoHeight) / 2);

            $merge->resizeImage($targetLogoWidth, $targetLogoHeight, Imagick::FILTER_LANCZOS, 1);

            $source->compositeImage($merge, Imagick::COMPOSITE_DEFAULT, $centerX, $centerY);

            $source->setImageFormat($this->format->value);

            return $source->getImageBlob();
        } catch (ImagickException $imagickException) {
            throw ImageMergeException::imagickException($imagickException);
        }
    }
}

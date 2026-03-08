<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Mergers;

use Imagick;
use ImagickException;
use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\MergerInterface;
use RuntimeException;

final readonly class ImagickMerger implements MergerInterface
{
    public function __construct(
        private string $sourceImageContent,
        private string $mergeImageContent,
        private string $format = 'png',
        private float $percentage = 0.2
    ) {
        if (! in_array($this->format, ['png', 'webp'])) {
            throw new InvalidArgumentException('ImagickMerger only supports "png" or "webp" formats.');
        }

        if ($this->percentage <= 0 || $this->percentage >= 1) {
            throw new InvalidArgumentException('$percentage must be between 0 and 1');
        }
    }

    public function merge(): string
    {
        $source = null;
        $merge = null;

        try {
            $source = new Imagick;
            $source->readImageBlob($this->sourceImageContent);

            $merge = new Imagick;
            $merge->readImageBlob($this->mergeImageContent);

            $sourceWidth = $source->getImageWidth();
            $sourceHeight = $source->getImageHeight();

            $mergeWidth = $merge->getImageWidth();
            $mergeHeight = $merge->getImageHeight();

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

            $merge->resizeImage($targetLogoWidth, $targetLogoHeight, Imagick::FILTER_LANCZOS, 1);

            $source->compositeImage($merge, Imagick::COMPOSITE_DEFAULT, $centerX, $centerY);

            $source->setImageFormat($this->format);

            if ($this->format === 'webp') {
                $source->setImageCompressionQuality(90);
            }

            return $source->getImageBlob();

        } catch (ImagickException $imagickException) {
            throw new RuntimeException('Imagick merge failed: '.$imagickException->getMessage(), $imagickException->getCode(), $imagickException);
        } finally {
            $source?->clear();
            $source?->destroy();
            $merge?->clear();
            $merge?->destroy();
        }
    }
}

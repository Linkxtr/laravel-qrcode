<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Mergers;

use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\MergerInterface;

final readonly class SvgMerger implements MergerInterface
{
    public function __construct(
        private string $svgContent,
        private string $mergeImageContent,
        private float $percentage
    ) {}

    public function merge(): string
    {
        if ($this->percentage <= 0 || $this->percentage >= 1) {
            throw new InvalidArgumentException('$percentage must be between 0 and 1');
        }
        $widthFound = preg_match('/width=["\'](\d+)["\']/i', $this->svgContent, $widthMatch);
        $heightFound = preg_match('/height=["\'](\d+)["\']/i', $this->svgContent, $heightMatch);

        if (! $widthFound || ! $heightFound) {
            throw new InvalidArgumentException('Could not determine SVG dimensions. Ensure the SVG has width and height attributes.');
        }

        $svgWidth = (int) $widthMatch[1];
        $svgHeight = (int) $heightMatch[1];

        $imageInfo = getimagesizefromstring($this->mergeImageContent);

        if ($imageInfo === false) {
            throw new InvalidArgumentException('Invalid image data provided for merge. Could not determine image type/size.');
        }

        [$logoWidth, $logoHeight] = $imageInfo;
        $mimeType = $imageInfo['mime'];

        $logoRatio = $logoWidth / $logoHeight;
        $targetWidth = (int) ($svgWidth * $this->percentage);
        $targetHeight = (int) ($targetWidth / $logoRatio);

        $x = (int) (($svgWidth - $targetWidth) / 2);
        $y = (int) (($svgHeight - $targetHeight) / 2);

        $base64Image = base64_encode($this->mergeImageContent);
        $imageUri = "data:{$mimeType};base64,{$base64Image}";

        $imageTag = sprintf(
            '<image x="%d" y="%d" width="%d" height="%d" href="%s" />',
            $x,
            $y,
            $targetWidth,
            $targetHeight,
            $imageUri
        );

        $closingTagPos = strrpos($this->svgContent, '</svg>');

        if ($closingTagPos === false) {
            throw new InvalidArgumentException('Invalid SVG content: closing tag not found.');
        }

        return substr_replace($this->svgContent, $imageTag.'</svg>', $closingTagPos, strlen('</svg>'));
    }
}

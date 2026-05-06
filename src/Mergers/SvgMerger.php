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

        $widthFound = preg_match('/width=["\'](\d+(?:\.\d+)?)\s*(?:px)?["\']/i', $this->svgContent, $widthMatch);
        $heightFound = preg_match('/height=["\'](\d+(?:\.\d+)?)\s*(?:px)?["\']/i', $this->svgContent, $heightMatch);

        if (! $widthFound || ! $heightFound) {
            throw new InvalidArgumentException('Could not determine SVG dimensions. Ensure the SVG has width and height attributes.');
        }

        $svgWidth = (int) $widthMatch[1];
        $svgHeight = (int) $heightMatch[1]; // @pest-mutate-ignore

        $imageInfo = getimagesizefromstring($this->mergeImageContent);

        if ($imageInfo === false) {
            throw new InvalidArgumentException('Invalid image data provided for merge. Could not determine image type/size.');
        }

        [$logoWidth, $logoHeight] = $imageInfo;
        $mimeType = $imageInfo['mime'];

        if ($logoWidth <= 0 || $logoHeight <= 0) {
            throw new InvalidArgumentException('Invalid image dimensions for merge.');
        }

        $logoRatio = $logoWidth / $logoHeight;
        $targetWidth = max(1, (int) ($svgWidth * $this->percentage)); // @pest-mutate-ignore
        $targetHeight = max(1, (int) ($targetWidth / $logoRatio)); // @pest-mutate-ignore

        if ($targetHeight > $svgHeight * $this->percentage) { // @pest-mutate-ignore
            $targetHeight = max(1, (int) ($svgHeight * $this->percentage)); // @pest-mutate-ignore
            $targetWidth = max(1, (int) ($targetHeight * $logoRatio)); // @pest-mutate-ignore
        }

        $x = (int) (($svgWidth - $targetWidth) / 2); // @pest-mutate-ignore
        $y = (int) (($svgHeight - $targetHeight) / 2); // @pest-mutate-ignore

        $base64Image = base64_encode($this->mergeImageContent);
        $imageUri = sprintf('data:%s;base64,%s', $mimeType, $base64Image);

        $imageTag = sprintf(
            '<image x="%d" y="%d" width="%d" height="%d" href="%s" xlink:href="%s" />',
            $x,
            $y,
            $targetWidth,
            $targetHeight,
            $imageUri,
            $imageUri
        );

        $svgContent = $this->svgContent;

        if (! str_contains($svgContent, 'xmlns:xlink=')) {
            $replaced = preg_replace('/<svg\s/i', '<svg xmlns:xlink="http://www.w3.org/1999/xlink" ', $svgContent, 1);
            $svgContent = (string) $replaced; // @pest-mutate-ignore
        }

        $closingTagPos = strrpos($svgContent, '</svg>');

        if ($closingTagPos === false) {
            throw new InvalidArgumentException('Invalid SVG content: closing tag not found.');
        }

        return substr_replace($svgContent, $imageTag.'</svg>', $closingTagPos, strlen('</svg>'));
    }
}

<?php

namespace Linkxtr\QrCode;

use InvalidArgumentException;

final class SvgImageMerge
{
    protected string $svgContent;

    protected string $mergeImageContent;

    protected float $percentage;

    public function __construct(string $svgContent, string $mergeImageContent, float $percentage)
    {
        $this->svgContent = $svgContent;
        $this->mergeImageContent = $mergeImageContent;
        $this->percentage = $percentage;
    }

    public function merge(): string
    {
        if ($this->percentage > 1 || $this->percentage <= 0) {
            throw new InvalidArgumentException('$percentage must be greater than 0 and less than or equal to 1');
        }

        // Parse SVG to get width and height
        preg_match('/width="(\d+)"/', $this->svgContent, $widthMatch);
        preg_match('/height="(\d+)"/', $this->svgContent, $heightMatch);

        if (! isset($widthMatch[1]) || ! isset($heightMatch[1])) {
            throw new InvalidArgumentException('Could not determine SVG dimensions.');
        }

        $svgWidth = (int) $widthMatch[1];
        $svgHeight = (int) $heightMatch[1];

        // Prepare image data
        $base64Image = base64_encode($this->mergeImageContent);
        $mimeType = $this->getMimeType($this->mergeImageContent);
        $imageUri = "data:{$mimeType};base64,{$base64Image}";

        // Calculate dimensions for the merged image
        // We need to know the aspect ratio of the merge image to calculate dimensions correctly
        // Since we have the raw content, we can use getimagesizefromstring if available or create a gd resource
        $mergeImageWidth = 0;
        $mergeImageHeight = 0;

        // Try to get dimensions using GD
        $img = false;
        set_error_handler(function () {
            return true;
        });

        $img = imagecreatefromstring($this->mergeImageContent);

        restore_error_handler();

        if ($img !== false) {
            $mergeImageWidth = imagesx($img);
            $mergeImageHeight = imagesy($img);
            unset($img);
        } else {
            // Fallback or error? If we can't determine size, we might assume square or throw error.
            // Given the requirements, we should probably throw an error if the image is invalid.
            throw new InvalidArgumentException('Invalid image data provided for merge.');
        }

        $mergeRatio = $mergeImageWidth / $mergeImageHeight;

        $targetWidth = intval($svgWidth * $this->percentage);
        $targetHeight = intval($targetWidth / $mergeRatio);

        $x = intval(($svgWidth - $targetWidth) / 2);
        $y = intval(($svgHeight - $targetHeight) / 2);

        // Create <image> tag
        $imageTag = sprintf(
            '<image x="%d" y="%d" width="%d" height="%d" href="%s" />',
            $x,
            $y,
            $targetWidth,
            $targetHeight,
            $imageUri
        );

        // Inject before </svg>
        $pos = strrpos($this->svgContent, '</svg>');
        if ($pos === false) {
            throw new InvalidArgumentException('Invalid SVG content.');
        }

        return substr_replace($this->svgContent, $imageTag.'</svg>', $pos, strlen('</svg>'));
    }

    protected function getMimeType(string $content): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($content);

        return $mime ?: 'application/octet-stream';
    }
}

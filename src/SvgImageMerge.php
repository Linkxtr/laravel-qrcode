<?php

namespace Linkxtr\QrCode;

use DOMDocument;
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

        $doc = new DOMDocument();
        $internalErrors = libxml_use_internal_errors(true);
        
        // Load SVG with strict XML parser options if needed, but default is usually strict enough
        // Handle empty or invalid content
        if (! $this->svgContent || ! $doc->loadXML($this->svgContent)) {
            libxml_clear_errors();
            libxml_use_internal_errors($internalErrors);
            throw new InvalidArgumentException('Invalid SVG content.');
        }
        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);

        $svg = $doc->documentElement;
        if (! $svg) {
            throw new InvalidArgumentException('Invalid SVG content.');
        }

        $svgWidth = $this->getDimension($svg->getAttribute('width'));
        $svgHeight = $this->getDimension($svg->getAttribute('height'));

        // Fallback to viewBox
        if ($svgWidth === null || $svgHeight === null) {
            $viewBox = $svg->getAttribute('viewBox');
            if ($viewBox) {
                $parts = preg_split('/[\s,]+/', trim($viewBox));
                if ($parts !== false && count($parts) === 4) {
                    $svgWidth = $svgWidth ?? (float) $parts[2];
                    $svgHeight = $svgHeight ?? (float) $parts[3];
                }
            }
        }

        // Fallback to style attribute
        if ($svgWidth === null || $svgHeight === null) {
            $style = $svg->getAttribute('style');
            if ($style) {
                if ($svgWidth === null && preg_match('/width:\s*([\d.]+)(\w*)/', $style, $matches)) {
                   $svgWidth = (float) $matches[1];
                }
                if ($svgHeight === null && preg_match('/height:\s*([\d.]+)(\w*)/', $style, $matches)) {
                   $svgHeight = (float) $matches[1];
                }
            }
        }

        if ($svgWidth === null || $svgHeight === null) {
            throw new InvalidArgumentException('Could not determine SVG dimensions.');
        }

        // Prepare image data
        $base64Image = base64_encode($this->mergeImageContent);
        $mimeType = $this->getMimeType($this->mergeImageContent);
        $imageUri = "data:{$mimeType};base64,{$base64Image}";

        // Calculate dimensions for the merged image
        $mergeImageWidth = 0;
        $mergeImageHeight = 0;

        $img = false;
        $internalErrors = libxml_use_internal_errors(true); // Suppress GD warnings too just in case
        // Actually GD warning suppression is done via set_error_handler in previous code
        
        try {
             $img = @imagecreatefromstring($this->mergeImageContent);
        } catch (\Throwable $e) {
            // ignore
        }

        if ($img !== false) {
            $mergeImageWidth = imagesx($img);
            $mergeImageHeight = imagesy($img);
            // Destroy not strictly needed in modern PHP as resources are objects
        } else {
            throw new InvalidArgumentException('Invalid image data provided for merge.');
        }

        $mergeRatio = $mergeImageWidth / $mergeImageHeight;

        $targetWidth = intval($svgWidth * $this->percentage);
        $targetHeight = intval($targetWidth / $mergeRatio);

        $x = intval(($svgWidth - $targetWidth) / 2);
        $y = intval(($svgHeight - $targetHeight) / 2);

        // Inject image using DOM
        $imageNode = $doc->createElement('image');
        $imageNode->setAttribute('x', (string)$x);
        $imageNode->setAttribute('y', (string)$y);
        $imageNode->setAttribute('width', (string)$targetWidth);
        $imageNode->setAttribute('height', (string)$targetHeight);
        $imageNode->setAttribute('href', $imageUri);

        $svg->appendChild($imageNode);

        $output = $doc->saveXML();
        
        if ($output === false) {
             throw new \RuntimeException('Failed to save SVG XML.');
        }

        return $output;
    }

    protected function getDimension(string $value): ?float
    {
        if ($value === '') {
            return null;
        }
        
        // Strip units
        if (preg_match('/^([\d.]+)/', $value, $matches)) {
            return (float) $matches[1];
        }

        return null;
    }

    protected function getMimeType(string $content): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($content);

        return $mime ?: 'application/octet-stream';
    }
}

<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Mergers;

use DOMDocument;
use DOMElement;
use Linkxtr\QrCode\Contracts\MergerInterface;
use Linkxtr\QrCode\Exceptions\ImageMergeException;

final readonly class SvgMerger implements MergerInterface
{
    private DOMDocument $domDocument;

    public function __construct(
        private string $svgContent,
        private string $mergeImageContent,
        private float $percentage,
        ?DOMDocument $domDocument = null
    ) {
        $this->domDocument = $domDocument ?? new DOMDocument;
    }

    public function merge(): string
    {
        if ($this->percentage <= 0 || $this->percentage >= 1) {
            throw ImageMergeException::invalidPercentage();
        }

        $libxmlState = libxml_use_internal_errors(true);
        $this->domDocument->loadXML($this->svgContent);
        libxml_clear_errors();
        libxml_use_internal_errors($libxmlState);

        $svgNode = $this->domDocument->documentElement;

        if (! $svgNode instanceof DOMElement || $svgNode->nodeName !== 'svg') {
            throw ImageMergeException::invalidSvgContent();
        }

        $svgWidth = (float) $svgNode->getAttribute('width');
        $svgHeight = (float) $svgNode->getAttribute('height');

        if ($svgWidth <= 0 || $svgHeight <= 0) {
            throw ImageMergeException::couldNotDetermineSvgDimensions();
        }

        $imageInfo = getimagesizefromstring($this->mergeImageContent);

        if ($imageInfo === false) {
            throw ImageMergeException::invalidImage();
        }

        [$logoWidth, $logoHeight] = $imageInfo;
        $mimeType = $imageInfo['mime'];

        if ($logoWidth <= 0 || $logoHeight <= 0) {
            throw ImageMergeException::invalidImage();
        }

        $boxWidth = $svgWidth * $this->percentage;
        $boxHeight = $svgHeight * $this->percentage;

        $scale = min($boxWidth / $logoWidth, $boxHeight / $logoHeight);

        $targetWidth = max(1.0, $logoWidth * $scale);
        $targetHeight = max(1.0, $logoHeight * $scale);

        $x = ($svgWidth - $targetWidth) / 2;
        $y = ($svgHeight - $targetHeight) / 2;

        $base64Image = base64_encode($this->mergeImageContent);
        $imageUri = sprintf('data:%s;base64,%s', $mimeType, $base64Image);

        if (! $svgNode->hasAttributeNS('http://www.w3.org/2000/xmlns/', 'xlink')) {
            $svgNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xlink', 'http://www.w3.org/1999/xlink');
        }

        $imageElement = $this->domDocument->createElementNS('http://www.w3.org/2000/svg', 'image');
        $imageElement->setAttribute('x', (string) round($x, 4));
        $imageElement->setAttribute('y', (string) round($y, 4));
        $imageElement->setAttribute('width', (string) round($targetWidth, 4));
        $imageElement->setAttribute('height', (string) round($targetHeight, 4));
        $imageElement->setAttribute('href', $imageUri);
        $imageElement->setAttributeNS('http://www.w3.org/1999/xlink', 'xlink:href', $imageUri);

        $svgNode->appendChild($imageElement);

        $output = $this->domDocument->saveXML($svgNode);

        if ($output === false) {
            throw ImageMergeException::invalidSvgContent();
        }

        return $output;
    }
}

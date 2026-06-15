<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Mergers;

use DOMDocument;
use DOMElement;
use Linkxtr\QrCode\Contracts\MergerInterface;
use Linkxtr\QrCode\Exceptions\ImageMergeException;

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
            throw ImageMergeException::invalidPercentage();
        }

        $domDocument = new DOMDocument;

        $libxmlState = libxml_use_internal_errors(true);
        $loaded = $domDocument->loadXML($this->svgContent);
        libxml_clear_errors();
        libxml_use_internal_errors($libxmlState);

        if (! $loaded) {
            throw ImageMergeException::invalidSvgContent();
        }

        $svgNode = $domDocument->documentElement;

        if (! $svgNode instanceof DOMElement || $svgNode->nodeName !== 'svg') {
            throw ImageMergeException::invalidSvgContent();
        }

        $widthAttr = $svgNode->getAttribute('width');
        $heightAttr = $svgNode->getAttribute('height');

        if ($widthAttr === '' || $heightAttr === '') {
            throw ImageMergeException::couldNotDetermineSvgDimensions();
        }

        $svgWidth = (int) $widthAttr;
        $svgHeight = (int) $heightAttr;

        $imageInfo = getimagesizefromstring($this->mergeImageContent);

        if ($imageInfo === false) {
            throw ImageMergeException::invalidImage();
        }

        [$logoWidth, $logoHeight] = $imageInfo;
        $mimeType = $imageInfo['mime'];

        if ($logoWidth <= 0 || $logoHeight <= 0) {
            throw ImageMergeException::invalidImage();
        }

        $logoRatio = $logoWidth / $logoHeight;
        $targetWidth = max(1, (int) ($svgWidth * $this->percentage));
        $targetHeight = max(1, (int) ($targetWidth / $logoRatio));

        if ($targetHeight > $svgHeight * $this->percentage) {
            $targetHeight = max(1, (int) ($svgHeight * $this->percentage));
            $targetWidth = max(1, (int) ($targetHeight * $logoRatio));
        }

        $x = (int) (($svgWidth - $targetWidth) / 2);
        $y = (int) (($svgHeight - $targetHeight) / 2);

        $base64Image = base64_encode($this->mergeImageContent);
        $imageUri = sprintf('data:%s;base64,%s', $mimeType, $base64Image);

        if (! $svgNode->hasAttributeNS('http://www.w3.org/2000/xmlns/', 'xlink')) {
            $svgNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xlink', 'http://www.w3.org/1999/xlink');
        }

        $imageElement = $domDocument->createElementNS('http://www.w3.org/2000/svg', 'image');
        $imageElement->setAttribute('x', (string) $x);
        $imageElement->setAttribute('y', (string) $y);
        $imageElement->setAttribute('width', (string) $targetWidth);
        $imageElement->setAttribute('height', (string) $targetHeight);
        $imageElement->setAttribute('href', $imageUri);
        $imageElement->setAttributeNS('http://www.w3.org/1999/xlink', 'xlink:href', $imageUri);

        $svgNode->appendChild($imageElement);

        $output = $domDocument->saveXML($svgNode);

        // @codeCoverageIgnoreStart
        if ($output === false) {
            throw ImageMergeException::invalidSvgContent();
        }

        // @codeCoverageIgnoreEnd

        return $output;
    }
}

<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Support\Bacon;

use Linkxtr\QrCode\Contracts\MergerInterface;
use Linkxtr\QrCode\DTOs\Config;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Exceptions\MissingExtensionException;
use Linkxtr\QrCode\Mergers\EpsMerger;
use Linkxtr\QrCode\Mergers\ImagickMerger;
use Linkxtr\QrCode\Mergers\RasterMerger;
use Linkxtr\QrCode\Mergers\SvgMerger;
use Linkxtr\QrCode\Support\Environment;

final readonly class MergerFactory
{
    public function __construct(private Config $config) {}

    public static function make(Config $config): self
    {
        return new self($config);
    }

    public function merge(string $content): string
    {
        return $this->getMerger($content)->merge();
    }

    /**
     * @throws MissingExtensionException
     */
    private function getMerger(string $content): MergerInterface
    {
        $format = $this->config->getFormat();
        $imageMerge = $this->config->getImageMerge();
        $percentage = $this->config->getImagePercentage();

        if ($format === Format::EPS && ! Environment::hasExtension('gd')) {
            throw MissingExtensionException::gdRequired('to merge images into EPS format');
        }

        return match ($format) {
            Format::EPS => new EpsMerger($content, $imageMerge, $percentage),
            Format::SVG => new SvgMerger($content, $imageMerge, $percentage),
            default => Environment::hasExtension('imagick')
                ? (new ImagickMerger($content, $imageMerge, $percentage))->setFormat($format)
                : (new RasterMerger($content, $imageMerge, $percentage))->setFormat($format),
        };
    }
}

<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Support\Bacon;

use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererInterface;
use BaconQrCode\Renderer\RendererStyle\Gradient;
use Linkxtr\QrCode\DTOs\Config;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Enums\Style;
use Linkxtr\QrCode\Exceptions\MissingExtensionException;

final readonly class RendererFactory
{
    /**
     * The PNG compression level.
     * Only applicable to PNG format when using GDLibRenderer.
     */
    private const PNG_COMPRESSION_LEVEL = 9;

    public function __construct(private Config $config) {}

    public static function make(Config $config): RendererInterface
    {
        return (new self($config))->build();
    }

    private function build(): RendererInterface
    {
        return $this->canUseImageRenderer() ?
            new ImageRenderer(
                StyleResolver::resolve($this->config),
                BackendFactory::make($this->config->getFormat())
            ) :
            new GDLibRenderer(
                $this->config->getSize(),
                $this->config->getMargin(),
                $this->config->getFormat()->value,
                self::PNG_COMPRESSION_LEVEL,
                FillResolver::resolve($this->config)
            );
    }

    /**
     * @throws MissingExtensionException
     */
    private function canUseImageRenderer(): bool
    {
        $format = $this->config->getFormat();

        if ($format === Format::SVG || $format === Format::EPS) {
            return true;
        }

        if (! extension_loaded('imagick') && ! extension_loaded('gd')) {
            throw MissingExtensionException::neitherImagickNorGdAvailable();
        }

        if (extension_loaded('imagick')) {
            return true;
        }

        if ($format !== Format::PNG) {
            throw MissingExtensionException::imagickRequired(sprintf('to generate the %s format', $format->value));
        }

        if ($this->config->getStyle() !== Style::SQUARE) {
            throw MissingExtensionException::imagickRequired('to use non-square module styles (e.g., DOT, ROUND). Please enable the Imagick extension or use the SQUARE style');
        }

        if ($this->config->getGradient() instanceof Gradient) {
            throw MissingExtensionException::imagickRequired('to use gradients. Please enable the Imagick extension or use solid colors');
        }

        return false;
    }
}

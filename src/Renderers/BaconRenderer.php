<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Renderers;

use BaconQrCode\Renderer\Color\Cmyk;
use BaconQrCode\Renderer\Color\ColorInterface;
use BaconQrCode\Renderer\Color\Gray;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Eye\CompositeEye;
use BaconQrCode\Renderer\Eye\EyeInterface;
use BaconQrCode\Renderer\Eye\ModuleEye;
use BaconQrCode\Renderer\Eye\PointyEye;
use BaconQrCode\Renderer\Eye\SimpleCircleEye;
use BaconQrCode\Renderer\Eye\SquareEye;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Renderer\Image\EpsImageBackEnd;
use BaconQrCode\Renderer\Image\ImageBackEndInterface;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Module\DotsModule;
use BaconQrCode\Renderer\Module\ModuleInterface;
use BaconQrCode\Renderer\Module\RoundnessModule;
use BaconQrCode\Renderer\Module\SquareModule;
use BaconQrCode\Renderer\RendererInterface;
use BaconQrCode\Renderer\RendererStyle\EyeFill;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\Gradient;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\HtmlString;
use Linkxtr\QrCode\DTOs\Config;
use Linkxtr\QrCode\Enums\ColorModel;
use Linkxtr\QrCode\Enums\EyeStyle;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Enums\Style;
use Linkxtr\QrCode\Mergers\EpsMerger;
use Linkxtr\QrCode\Mergers\ImagickMerger;
use Linkxtr\QrCode\Mergers\RasterMerger;
use Linkxtr\QrCode\Mergers\SvgMerger;
use Linkxtr\QrCode\Support\Image;
use Linkxtr\QrCode\ValueObjects\ColorValue;
use RuntimeException;

final class BaconRenderer
{
    /**
     * The PNG compression level.
     * Only applicable to PNG format when using GDLibRenderer.
     */
    private const PNG_COMPRESSION_LEVEL = 9;
    public function __construct(private Config $config)
    {
    }

    /**
     * Render the QR code based on the provided payload and config.
     */
    public function render(string $payload): HtmlString
    {
        $renderer = $this->getRenderer();
        $writer = new Writer($renderer);

        $content = $writer->writeString(
            $payload,
            $this->config->getEncoding(),
            $this->config->getErrorCorrectionLevel()->toBaconErrorCorrectionLevel()
        );

        if ($this->config->getImageMerge() !== '') {
            $content = $this->mergeImage($content);
        }

        return new HtmlString($content);
    }

    private function getRenderer(): RendererInterface
    {
        if (! extension_loaded('imagick') && ! extension_loaded('gd')) {
            throw new RuntimeException('The imagick or gd extension is required to generate QR codes.');
        }

        if (extension_loaded('imagick')) {
            return new ImageRenderer(
                $this->getRendererStyle(),
                $this->getFormatter()
            );
        }

        if ($this->config->getFormat() !== Format::PNG) {
            throw new RuntimeException('The imagick extension is required to generate QR codes in '.$this->config->getFormat()->value.' format.');
        }

        return new GDLibRenderer(
            $this->config->getSize(),
            $this->config->getMargin(),
            $this->config->getFormat()->value,
            self::PNG_COMPRESSION_LEVEL,
            $this->getFill()
        );
    }

    private function getRendererStyle(): RendererStyle
    {
        return new RendererStyle($this->config->getSize(), $this->config->getMargin(), $this->getModule(), $this->getEye(), $this->getFill());
    }

    private function getBackEnd(): ImageBackEndInterface
    {
        return match ($this->config->getFormat()) {
            Format::SVG => new SvgImageBackEnd(),
            Format::EPS => new EpsImageBackEnd(),
            default => new ImagickImageBackEnd($this->config->getFormat()->value),
        };
    }

    private function getModule(): ModuleInterface
    {
        if ($this->config->getStyle() === Style::DOT) {
            return new DotsModule($this->config->getStyleSize());
        }

        if ($this->config->getStyle() === Style::ROUND) {
            return new RoundnessModule($this->config->getStyleSize());
        }

        return SquareModule::instance();
    }
    
    private function mergeImage(string $qrCode): string
    {
        if ($this->config->getFormat() === Format::EPS) {
            $merger = new EpsMerger($qrCode, $this->config->getImageMerge(), $this->config->getImagePercentage());

            return $merger->merge();
        }

        if ($this->config->getFormat() === Format::SVG) {
            $merger = new SvgMerger($qrCode, $this->config->getImageMerge(), $this->config->getImagePercentage());

            return $merger->merge();
        }

        if (extension_loaded('imagick') && in_array($this->config->getFormat(), [Format::PNG, Format::WEBP], true)) {
            $merger = new ImagickMerger($qrCode, $this->config->getImageMerge(), $this->config->getFormat()->value, $this->config->getImagePercentage());

            return $merger->merge();
        }

        $merger = new RasterMerger(new Image($qrCode), new Image($this->config->getImageMerge()), $this->config->getFormat()->value, $this->config->getImagePercentage());

        return $merger->merge();
    }

    private function getFormatter(): ImageBackEndInterface
    {
        return match ($this->config->getFormat()) {
            Format::PNG => new ImagickImageBackEnd('png'),
            Format::WEBP => new ImagickImageBackEnd('webp'),
            Format::EPS => new EpsImageBackEnd,
            Format::SVG => new SvgImageBackEnd,
        };
    }

    private function getFill(): Fill
    {
        $foregroundColor = $this->buildColor($this->config->getColorValue()) ?? new Rgb(0, 0, 0);
        $backgroundColor = $this->buildColor($this->config->getBackgroundColorValue()) ?? new Rgb(255, 255, 255);
        $eye0 = $this->config->getEyeColors()[0] ?? EyeFill::inherit();
        $eye1 = $this->config->getEyeColors()[1] ?? EyeFill::inherit();
        $eye2 = $this->config->getEyeColors()[2] ?? EyeFill::inherit();

        if ($this->config->getGradient() instanceof Gradient) {
            return Fill::withForegroundGradient($backgroundColor, $this->config->getGradient(), $eye0, $eye1, $eye2);
        }

        return Fill::withForegroundColor($backgroundColor, $foregroundColor, $eye0, $eye1, $eye2);
    }

    private function buildColor(?ColorValue $colorValue): ?ColorInterface
    {
        if (! $colorValue instanceof ColorValue) {
            return null;
        }

        if ($this->config->getColorModel() === ColorModel::GRAY) {
            return new Gray($colorValue->c1);
        }

        if ($this->config->getColorModel() === ColorModel::CMYK) {
            return new Cmyk($colorValue->c1, $colorValue->c2, $colorValue->c3, $colorValue->c4 ?? 0);
        }

        return $this->config->createColor($colorValue->c1, $colorValue->c2, $colorValue->c3, $colorValue->c4);
    }

    private function getEye(): EyeInterface
    {
        $module = $this->getModule();
        $externalEye = $this->getEyeInstance($this->config->getEyeStyle(), $module);

        if ($this->config->getInternalEyeStyle() instanceof EyeStyle) {
            $internalEye = $this->getEyeInstance($this->config->getInternalEyeStyle(), $module);

            return new CompositeEye($externalEye, $internalEye);
        }

        return $externalEye;
    }

    private function getEyeInstance(?EyeStyle $eyeStyle, ModuleInterface $module): EyeInterface
    {
        return match ($eyeStyle) {
            EyeStyle::SQUARE => SquareEye::instance(),
            EyeStyle::CIRCLE => SimpleCircleEye::instance(),
            EyeStyle::POINTY => PointyEye::instance(),
            null => new ModuleEye($module),
        };
    }
}
<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Support\Bacon;

use BaconQrCode\Renderer\Eye\CompositeEye;
use BaconQrCode\Renderer\Eye\EyeInterface;
use BaconQrCode\Renderer\Eye\ModuleEye;
use BaconQrCode\Renderer\Eye\PointyEye;
use BaconQrCode\Renderer\Eye\SimpleCircleEye;
use BaconQrCode\Renderer\Eye\SquareEye;
use BaconQrCode\Renderer\Module\DotsModule;
use BaconQrCode\Renderer\Module\ModuleInterface;
use BaconQrCode\Renderer\Module\RoundnessModule;
use BaconQrCode\Renderer\Module\SquareModule;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use Linkxtr\QrCode\DTOs\Config;
use Linkxtr\QrCode\Enums\EyeStyle;
use Linkxtr\QrCode\Enums\Style;

final readonly class StyleResolver
{
    public function __construct(private Config $config) {}

    public static function resolve(Config $config): RendererStyle
    {
        return (new self($config))->build();
    }

    private function build(): RendererStyle
    {
        $module = $this->resolveModule();

        return new RendererStyle(
            $this->config->getSize(),
            $this->config->getMargin(),
            $module,
            $this->resolveEye($module),
            FillResolver::resolve($this->config)
        );
    }

    private function resolveModule(): ModuleInterface
    {
        $size = $this->config->getStyleSize();

        return match ($this->config->getStyle()) {
            Style::DOT => new DotsModule($size),
            Style::ROUND => new RoundnessModule($size),
            default => SquareModule::instance(),
        };
    }

    private function resolveEye(ModuleInterface $module): EyeInterface
    {
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

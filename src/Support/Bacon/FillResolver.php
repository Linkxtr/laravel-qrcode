<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Support\Bacon;

use BaconQrCode\Renderer\Color\ColorInterface as BaconColorInterface;
use BaconQrCode\Renderer\RendererStyle\EyeFill;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\Gradient;
use Linkxtr\QrCode\Contracts\ColorInterface;
use Linkxtr\QrCode\DTOs\Config;

final readonly class FillResolver
{
    public function __construct(private Config $config) {}

    public static function resolve(Config $config): Fill
    {
        return (new self($config))->build();
    }

    private function build(): Fill
    {
        $foregroundColor = $this->buildColor($this->config->getColorValue());
        $backgroundColor = $this->buildColor($this->config->getBackgroundColorValue());
        $eye0 = $this->config->getEyeColors()[0] ?? EyeFill::inherit();
        $eye1 = $this->config->getEyeColors()[1] ?? EyeFill::inherit();
        $eye2 = $this->config->getEyeColors()[2] ?? EyeFill::inherit();

        if ($this->config->getGradient() instanceof Gradient) {
            return Fill::withForegroundGradient($backgroundColor, $this->config->getGradient(), $eye0, $eye1, $eye2);
        }

        return Fill::withForegroundColor($backgroundColor, $foregroundColor, $eye0, $eye1, $eye2);
    }

    private function buildColor(ColorInterface $color): BaconColorInterface
    {
        return $color->toBaconColor();
    }
}

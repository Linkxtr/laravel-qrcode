<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\ValueObjects\Colors;

use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\Cmyk as BaconCmyk;
use BaconQrCode\Renderer\Color\ColorInterface as BaconColorInterface;
use Linkxtr\QrCode\Contracts\ColorInterface;
use Linkxtr\QrCode\Exceptions\InvalidConfigurationException;

final readonly class Cmyk implements ColorInterface
{
    public function __construct(
        public int $cyan,
        public int $magenta,
        public int $yellow,
        public int $black,
        public int $alpha = 100
    ) {
        $this->validate($cyan, 'Cyan');
        $this->validate($magenta, 'Magenta');
        $this->validate($yellow, 'Yellow');
        $this->validate($black, 'Black');

        if ($alpha < 0 || $alpha > 100) {
            throw InvalidConfigurationException::invalidColorChannel('Alpha', 0, 100);
        }
    }

    public function getAlpha(): int
    {
        return $this->alpha;
    }

    public function toBaconColor(): BaconColorInterface
    {
        $cmyk = new BaconCmyk($this->cyan, $this->magenta, $this->yellow, $this->black);

        if ($this->alpha < 100) {
            return new Alpha($this->alpha, $cmyk);
        }

        return $cmyk;
    }

    public function toRgb(): Rgb
    {
        $c = $this->cyan / 100;
        $m = $this->magenta / 100;
        $y = $this->yellow / 100;
        $k = $this->black / 100;
        $r = (int) round(255 * (1 - $c) * (1 - $k));
        $g = (int) round(255 * (1 - $m) * (1 - $k));
        $b = (int) round(255 * (1 - $y) * (1 - $k));

        return new Rgb(
            $r,
            $g,
            $b,
            $this->alpha
        );
    }

    public function toCmyk(): Cmyk
    {
        return $this;
    }

    public function toGray(): Gray
    {
        $c = $this->cyan / 100;
        $m = $this->magenta / 100;
        $y = $this->yellow / 100;
        $k = $this->black / 100;
        $r = (1 - $c) * (1 - $k);
        $g = (1 - $m) * (1 - $k);
        $b = (1 - $y) * (1 - $k);

        $luminance = ($r * 0.299) + ($g * 0.587) + ($b * 0.114);

        return new Gray(
            (int) round($luminance * 100),
            $this->alpha
        );
    }

    private function validate(int $value, string $name): void
    {
        if ($value < 0 || $value > 100) {
            throw InvalidConfigurationException::invalidColorChannel($name, 0, 100);
        }
    }
}

<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\ValueObjects\Colors;

use BaconQrCode\Renderer\Color\Cmyk as BaconCmyk;
use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\ColorInterface;

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
            throw new InvalidArgumentException('Alpha must be between 0 and 100.');
        }
    }

    public function getAlpha(): int
    {
        return $this->alpha;
    }

    public function toBaconColor(): BaconCmyk
    {
        return new BaconCmyk($this->cyan, $this->magenta, $this->yellow, $this->black);
    }

    public function toRgb(): Rgb
    {
        return new Rgb(
            $this->cyan,
            $this->magenta,
            $this->yellow,
            $this->alpha
        );
    }

    public function toCmyk(): Cmyk
    {
        return $this;
    }

    public function toGray(): Gray
    {
        return new Gray(
            $this->black,
            $this->alpha
        );
    }

    private function validate(int $value, string $name): void
    {
        if ($value < 0 || $value > 100) {
            throw new InvalidArgumentException($name.' must be between 0 and 100.');
        }
    }
}

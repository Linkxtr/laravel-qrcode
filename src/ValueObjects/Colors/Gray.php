<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\ValueObjects\Colors;

use BaconQrCode\Renderer\Color\Gray as BaconGray;
use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\ColorInterface;

final readonly class Gray implements ColorInterface
{
    public function __construct(
        public int $gray,
        public int $alpha = 100
    ) {
        if ($gray < 0 || $gray > 100) {
            throw new InvalidArgumentException('Gray must be between 0 and 100.');
        }

        if ($alpha < 0 || $alpha > 100) {
            throw new InvalidArgumentException('Alpha must be between 0 and 100.');
        }
    }

    public function getAlpha(): int
    {
        return $this->alpha;
    }

    public function toBaconColor(): BaconGray
    {
        return new BaconGray($this->gray);
    }

    public function toRgb(): Rgb
    {
        return new Rgb(
            $this->gray,
            $this->gray,
            $this->gray,
            $this->alpha
        );
    }

    public function toCmyk(): Cmyk
    {
        return new Cmyk(
            $this->gray,
            $this->gray,
            $this->gray,
            $this->alpha
        );
    }

    public function toGray(): Gray
    {
        return $this;
    }
}

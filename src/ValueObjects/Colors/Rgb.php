<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\ValueObjects\Colors;

use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\ColorInterface as BaconColorInterface;
use BaconQrCode\Renderer\Color\Rgb as BaconRgb;
use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\ColorInterface;

final readonly class Rgb implements ColorInterface
{
    public function __construct(
        public int $red,
        public int $green,
        public int $blue,
        public int $alpha = 100
    ) {
        $this->validate($red, 'Red');
        $this->validate($green, 'Green');
        $this->validate($blue, 'Blue');

        if ($alpha < 0 || $alpha > 100) {
            throw new InvalidArgumentException('Alpha must be between 0 and 100.');
        }
    }

    public static function fromHex(string $hex, int $alpha = 100): self
    {
        $cleanHex = ltrim($hex, '#');

        if (strlen($cleanHex) === 3) {
            $cleanHex = sprintf('%1$s%1$s%2$s%2$s%3$s%3$s', $cleanHex[0], $cleanHex[1], $cleanHex[2]);
        } elseif (strlen($cleanHex) !== 6) {
            throw new InvalidArgumentException('Invalid hex color format. Must be 3 or 6 characters.');
        }

        if (! ctype_xdigit($cleanHex)) {
            throw new InvalidArgumentException('Invalid hex color string provided.');
        }

        return new self(
            intval(substr($cleanHex, 0, 2), 16),
            intval(substr($cleanHex, 2, 2), 16),
            intval(substr($cleanHex, 4), 16),
            $alpha
        );
    }

    public function getAlpha(): int
    {
        return $this->alpha;
    }

    public function toBaconColor(): BaconColorInterface
    {
        $rgb = new BaconRgb($this->red, $this->green, $this->blue);

        if ($this->alpha < 100) {
            return new Alpha($this->alpha, $rgb);
        }

        return $rgb;
    }

    public function toRgb(): Rgb
    {
        return $this;
    }

    public function toCmyk(): Cmyk
    {
        // avoid division by zero with input rgb(0,0,0), by handling it as a specific case
        if ($this->red === 0 && $this->green === 0 && $this->blue === 0) {
            return new Cmyk(0, 0, 0, 100, $this->alpha);
        }

        $c = 1 - ($this->red / 255);
        $m = 1 - ($this->green / 255);
        $y = 1 - ($this->blue / 255);
        $k = min($c, $m, $y);

        return new Cmyk(
            (int) round(100 * ($c - $k) / (1 - $k)),
            (int) round(100 * ($m - $k) / (1 - $k)),
            (int) round(100 * ($y - $k) / (1 - $k)),
            (int) round(100 * $k),
            $this->alpha
        );
    }

    public function toGray(): Gray
    {
        $luminance = ($this->red * 0.299) + ($this->green * 0.587) + ($this->blue * 0.114);
        $grayValue = (int) round(($luminance / 255) * 100);

        return new Gray($grayValue, $this->alpha);
    }

    private function validate(int $value, string $name): void
    {
        if ($value < 0 || $value > 255) {
            throw new InvalidArgumentException($name.' must be between 0 and 255.');
        }
    }
}

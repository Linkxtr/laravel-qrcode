<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Components;

use Illuminate\Support\HtmlString;
use Illuminate\View\Component;
use InvalidArgumentException;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Facades\QrCode;

final class QrCodeComponent extends Component
{
    public function __construct(
        public string $data,
        public int $size = 100,
        public string $format = 'svg',
        public ?string $color = null,
        public ?string $backgroundColor = null,
        public int $margin = 0,
        public ?string $style = null,
        public ?string $errorCorrection = null,
        public ?string $encoding = null,
        public ?string $eye = null,
        public ?string $eyeColor0 = null,
        public ?string $eyeColor1 = null,
        public ?string $eyeColor2 = null,
        public ?string $gradient = null,
        public ?string $gradientType = null,
        public ?string $merge = null,
        public ?string $mergeString = null,
        public float $mergePercentage = .2,
        public bool|string $mergeAbsolute = false,
    ) {}

    public function render(): HtmlString
    {
        if (! in_array($this->format, Format::toArray())) {
            throw new InvalidArgumentException('Invalid format.');
        }

        if ($this->format === 'eps') {
            throw new InvalidArgumentException('EPS format is not supported for HTML embedding in the Blade component.');
        }

        $generator = QrCode::size($this->size)->format($this->format)->margin($this->margin);

        if ($this->color) {
            $rgb = $this->parseColor($this->color);
            if ($rgb) {
                $generator->color($rgb[0], $rgb[1], $rgb[2]);
            }
        }

        if ($this->backgroundColor) {
            $rgb = $this->parseColor($this->backgroundColor);
            if ($rgb) {
                $generator->backgroundColor($rgb[0], $rgb[1], $rgb[2]);
            }
        }

        if ($this->style) {
            $generator->style($this->style);
        }

        if ($this->errorCorrection) {
            $generator->errorCorrection($this->errorCorrection);
        }

        if ($this->encoding) {
            $generator->encoding($this->encoding);
        }

        if ($this->eye) {
            $generator->eye($this->eye);
        }

        if ($this->eyeColor0) {
            $colors = $this->parseMultiColor($this->eyeColor0);
            if ($colors && count($colors) >= 6) {
                $generator->eyeColor(0, ...array_slice($colors, 0, 6));
            }
        }

        if ($this->eyeColor1) {
            $colors = $this->parseMultiColor($this->eyeColor1);
            if ($colors && count($colors) >= 6) {
                $generator->eyeColor(1, ...array_slice($colors, 0, 6));
            }
        }

        if ($this->eyeColor2) {
            $colors = $this->parseMultiColor($this->eyeColor2);
            if ($colors && count($colors) >= 6) {
                $generator->eyeColor(2, ...array_slice($colors, 0, 6));
            }
        }

        if ($this->gradient) {
            $colors = $this->parseMultiColor($this->gradient);
            $type = $this->gradientType ?? 'vertical';
            if ($colors && count($colors) >= 6) {
                // startRed, startGreen, startBlue, endRed, endGreen, endBlue, type
                $generator->gradient($colors[0], $colors[1], $colors[2], $colors[3], $colors[4], $colors[5], $type);
            }
        }

        if ($this->merge) {
            $mergeAbsolute = is_string($this->mergeAbsolute)
                ? strtolower($this->mergeAbsolute) === 'true'
                : $this->mergeAbsolute;

            if (str_contains($this->merge, '..')) {
                throw new InvalidArgumentException('Invalid merge path, path traversal is not allowed.');
            }

            $generator->merge($this->merge, $this->mergePercentage, $mergeAbsolute);
        } elseif ($this->mergeString) {
            $generator->mergeString($this->mergeString, $this->mergePercentage);
        }

        $htmlString = $generator->generate($this->data);

        if ($this->format === 'svg') {
            return $htmlString;
        }

        return new HtmlString('<img src="data:image/'.$this->format.';base64,'.base64_encode($htmlString->__toString()).'" alt="QR Code" />');
    }

    /**
     * Parse a "R,G,B" string or "#HEX" string into an array of [R, G, B].
     *
     * @return array<int, int>|null
     */
    private function parseColor(string $color): ?array
    {
        if (str_contains($color, ',')) {
            $parts = array_map(trim(...), explode(',', $color));
            if (count($parts) === 3) {
                return [
                    max(0, min(255, (int) $parts[0])),
                    max(0, min(255, (int) $parts[1])),
                    max(0, min(255, (int) $parts[2])),
                ];
            }

            return null;
        }

        if (str_starts_with($color, '#')) {
            $hex = ltrim($color, '#');

            if (! ctype_xdigit($hex)) {
                return null;
            }

            if (strlen($hex) === 3) {
                $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
                $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
                $b = hexdec(str_repeat(substr($hex, 2, 1), 2));

                return [(int) $r, (int) $g, (int) $b];
            }

            if (strlen($hex) === 6) {
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));

                return [(int) $r, (int) $g, (int) $b];
            }
        }

        return null;
    }

    /**
     * Parse multiple colors separated by '|' into a flat array of RGB integers.
     * Hex colors can also use comma separation: "#ff0000, `#00ff00`".
     * RGB format requires pipe: "255,0,0|0,255,0".
     * Example: "#ff0000|#00ff00" -> [255, 0, 0, 0, 255, 0]
     *
     * @return array<int, int>|null
     */
    private function parseMultiColor(string $multiColor): ?array
    {
        $multiColor = str_replace([', #', ',#'], '|#', $multiColor);
        $multiColor = str_replace(';', '|', $multiColor);

        $parts = array_filter(explode('|', $multiColor));

        $result = [];
        foreach ($parts as $part) {
            $rgb = $this->parseColor(trim($part));
            if ($rgb) {
                $result = array_merge($result, $rgb);
            }
        }

        return $result === [] ? null : $result;
    }
}

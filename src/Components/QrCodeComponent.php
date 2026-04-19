<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Components;

use Closure;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Illuminate\View\ComponentAttributeBag;
use InvalidArgumentException;
use Linkxtr\QrCode\Facades\QrCode;
use Linkxtr\QrCode\ValueObjects\Colors\Rgb;

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
        public float $mergePercentage = 0.2,
        public bool|string $mergeAbsolute = false,
    ) {}

    public function render(): Closure
    {
        if (! in_array($this->format, ['svg', 'png', 'webp'], true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Format "%s" is not supported in the Blade component. Supported HTML embed formats are: %s.',
                    $this->format,
                    implode(', ', ['svg', 'png', 'webp'])
                )
            );
        }

        $generator = QrCode::size($this->size)->format($this->format)->margin($this->margin);

        if ($this->color !== null && $rgb = $this->resolveColor($this->color)) {
            $generator->color($rgb[0], $rgb[1], $rgb[2], $rgb[3] ?? null);
        }

        if ($this->backgroundColor !== null && $rgb = $this->resolveColor($this->backgroundColor)) {
            $generator->backgroundColor($rgb[0], $rgb[1], $rgb[2], $rgb[3] ?? null);
        }

        if ($this->style !== null) {
            $generator->style($this->style);
        }

        if ($this->errorCorrection !== null) {
            $generator->errorCorrection($this->errorCorrection);
        }

        if ($this->encoding !== null) {
            $generator->encoding($this->encoding);
        }

        if ($this->eye !== null) {
            $generator->eye($this->eye);
        }

        if ($this->eyeColor0 !== null && $colors = $this->resolveMultiColor($this->eyeColor0)) {
            $generator->eyeColor(0, ...$colors);
        }

        if ($this->eyeColor1 !== null && $colors = $this->resolveMultiColor($this->eyeColor1)) {
            $generator->eyeColor(1, ...$colors);
        }

        if ($this->eyeColor2 !== null && $colors = $this->resolveMultiColor($this->eyeColor2)) {
            $generator->eyeColor(2, ...$colors);
        }

        if ($this->gradient !== null && $colors = $this->resolveMultiColor($this->gradient)) {
            $generator->gradient($colors[0], $colors[1], $colors[2], $colors[3], $colors[4], $colors[5], $this->gradientType ?? 'vertical');
        }

        if ($this->merge !== null) {
            $absolute = is_string($this->mergeAbsolute)
                ? filter_var($this->mergeAbsolute, FILTER_VALIDATE_BOOLEAN)
                : $this->mergeAbsolute;

            if (str_contains($this->merge, '..')) {
                throw new InvalidArgumentException('Invalid merge path, path traversal is not allowed.');
            }

            $generator->merge($this->merge, $this->mergePercentage, $absolute);
        } elseif ($this->mergeString !== null) {
            $generator->mergeString($this->mergeString, $this->mergePercentage);
        }

        $htmlString = (string) $generator->generate($this->data);

        return function (array $data) use ($htmlString): string {
            /** @var ComponentAttributeBag $attributes */
            $attributes = $data['attributes'];

            if ($this->format === 'svg') {
                $svg = $htmlString;

                if (! str_contains($svg, '<title')) {
                    $translatedTitle = __('QR Code');
                    $title = e(is_string($translatedTitle) ? $translatedTitle : 'QR Code');

                    $replacedSvg = preg_replace('/(<svg[^>]*>)/i', '$1<title>'.$title.'</title>', $svg, 1);
                    $svg = is_string($replacedSvg) ? $replacedSvg : $svg;
                }

                $mergedAttributes = $attributes->merge([
                    'role' => 'img',
                    'aria-label' => __('QR Code'),
                ]);

                return Str::replaceFirst('<svg', '<svg '.$mergedAttributes->toHtml(), $svg);
            }

            $mergedAttributes = $attributes->except('src')->merge([
                'alt' => __('QR Code'),
            ]);

            $base64 = base64_encode($htmlString);

            return '<img '.$mergedAttributes->toHtml().' src="data:image/'.$this->format.';base64,'.$base64.'" />';
        };
    }

    /**
     * Safely resolve Hex or CSV strings into an array of integers.
     *
     * @return array{0: int, 1: int, 2: int, 3?: int|null}|null
     */
    private function resolveColor(string $color): ?array
    {
        $color = trim($color);

        try {
            if (str_starts_with($color, '#')) {
                $rgb = Rgb::fromHex($color);
            } elseif (str_contains($color, ',')) {
                $parts = array_map(fn (string $p): int => (int) $p, explode(',', $color));
                $count = count($parts);

                if ($count !== 3 && $count !== 4) {
                    return null;
                }

                $rgb = new Rgb(
                    $parts[0],
                    $parts[1],
                    $parts[2],
                    $count === 4 ? $parts[3] : 100
                );
            } else {
                return null;
            }

            return [$rgb->red, $rgb->green, $rgb->blue, $rgb->getAlpha() === 100 ? null : $rgb->getAlpha()];
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    /**
     * Resolve multiple colors separated by common delimiters into a flat array.
     * Guarantees returning exactly 6 integers if successful, preventing unpack errors.
     *
     * @return array<int, int>|null
     */
    private function resolveMultiColor(string $multiColor): ?array
    {
        $multiColor = str_replace([';', ', #', ',#'], ['|', '|#', '|#'], $multiColor);

        $parts = explode('|', $multiColor);

        $colors = [];
        foreach ($parts as $part) {
            $resolved = $this->resolveColor($part);
            if ($resolved !== null) {
                $colors[] = $resolved;
            }
        }

        if ($colors === []) {
            return null;
        }

        if (count($colors) === 1) {
            return [
                $colors[0][0], $colors[0][1], $colors[0][2],
                $colors[0][0], $colors[0][1], $colors[0][2],
            ];
        }

        return [
            $colors[0][0], $colors[0][1], $colors[0][2],
            $colors[1][0], $colors[1][1], $colors[1][2],
        ];
    }
}

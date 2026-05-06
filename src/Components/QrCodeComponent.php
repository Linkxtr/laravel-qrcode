<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Components;

use Closure;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Illuminate\View\ComponentAttributeBag;
use InvalidArgumentException;
use Linkxtr\QrCode\Enums\GradientType;
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
            $generator = $generator->color($rgb->red, $rgb->green, $rgb->blue, $rgb->alpha);
        }

        if ($this->backgroundColor !== null && $rgb = $this->resolveColor($this->backgroundColor)) {
            $generator = $generator->backgroundColor($rgb->red, $rgb->green, $rgb->blue, $rgb->alpha);
        }

        if ($this->style !== null) {
            $generator = $generator->style($this->style);
        }

        if ($this->errorCorrection !== null) {
            $generator = $generator->errorCorrection($this->errorCorrection);
        }

        if ($this->encoding !== null) {
            $generator = $generator->encoding($this->encoding);
        }

        if ($this->eye !== null) {
            $generator = $generator->eye($this->eye);
        }

        foreach ([0 => $this->eyeColor0, 1 => $this->eyeColor1, 2 => $this->eyeColor2] as $index => $eyeColor) {
            if ($eyeColor !== null && $colors = $this->resolveMultiColor($eyeColor)) {
                $generator = $generator->eyeColor($index, $colors[0]->toArray(), $colors[1]->toArray());
            }
        }

        if ($this->gradient !== null && $colors = $this->resolveMultiColor($this->gradient)) {
            $generator = $generator->gradient(
                $colors[0]->toArray(),
                $colors[1]->toArray(),
                $this->gradientType ?? GradientType::VERTICAL
            );
        }

        if ($this->merge !== null) {
            if (str_contains($this->merge, '..')) {
                throw new InvalidArgumentException('Invalid merge path, path traversal is not allowed.');
            }

            $generator = $generator->merge($this->merge, $this->mergePercentage);
        } elseif ($this->mergeString !== null) {
            $generator = $generator->mergeString($this->mergeString, $this->mergePercentage);
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
     * Safely resolve Hex or CSV strings into an Rgb object.
     */
    private function resolveColor(string $color): ?Rgb
    {
        try {
            return Rgb::parse($color);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    /**
     * Resolve multiple colors separated by common delimiters into a flat array.
     * Guarantees returning exactly 6 integers if successful, preventing unpack errors.
     *
     * @return array{0: Rgb, 1: Rgb}|null
     */
    private function resolveMultiColor(string $multiColor): ?array
    {
        $multiColor = str_replace([';', ', #', ',#'], ['|', '|#', '|#'], $multiColor);

        $parts = explode('|', $multiColor);

        $colors = [];
        foreach ($parts as $part) {
            $resolved = $this->resolveColor($part);
            if ($resolved instanceof Rgb) {
                $colors[] = $resolved;
            }
        }

        if ($colors === []) {
            return null;
        }

        return [
            $colors[0],
            $colors[1] ?? $colors[0],
        ];
    }
}

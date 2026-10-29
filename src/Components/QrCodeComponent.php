<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Components;

use Closure;
use DOMDocument;
use DOMElement;
use Illuminate\View\Component;
use Illuminate\View\ComponentAttributeBag;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Enums\GradientType;
use Linkxtr\QrCode\Exceptions\GenerationException;
use Linkxtr\QrCode\Exceptions\InvalidConfigurationException;
use Linkxtr\QrCode\Facades\QrCode;
use Linkxtr\QrCode\ValueObjects\Colors\Rgb;
use Stringable;

final class QrCodeComponent extends Component
{
    public function __construct(
        public string $data,
        public ?int $size = null,
        public ?string $format = null,
        public ?string $color = null,
        public ?string $backgroundColor = null,
        public ?int $margin = null,
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
        $format = $this->format !== null ? $this->validateFormat($this->format) : $this->resolveFormatFromConfig();

        $generator = QrCode::format($format);

        if ($this->size !== null) {
            $generator = $generator->size($this->size);
        }

        if ($this->margin !== null) {
            $generator = $generator->margin($this->margin);
        }

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
            $generator = $generator->merge($this->merge, $this->mergePercentage);
        } elseif ($this->mergeString !== null) {
            $generator = $generator->mergeString($this->mergeString, $this->mergePercentage);
        }

        return function (array $data) use ($generator, $format): string {
            $htmlString = (string) $generator->generate($this->data);

            /** @var ComponentAttributeBag $attributes */
            $attributes = $data['attributes'];

            if ($format === Format::SVG) {
                return $this->prepareSvg($htmlString, $attributes);
            }

            $componentAttributeBag = $attributes->except('src')->merge([
                'alt' => __('QR Code'),
            ]);

            $base64 = base64_encode($htmlString);

            return '<img '.$componentAttributeBag->toHtml().' src="data:image/'.$format->value.';base64,'.$base64.'" />';
        };
    }

    private function validateFormat(string $format): Format
    {
        $formatEnum = Format::tryFrom($format);

        if ($formatEnum === null || $formatEnum === Format::EPS) {
            throw InvalidConfigurationException::unsupportedFormat(
                $format,
                ['svg', 'png', 'webp']
            );
        }

        return $formatEnum;
    }

    private function resolveFormatFromConfig(): Format
    {
        $format = config('qrcode.format');

        if (is_string($format)) {
            return $this->validateFormat($format);
        }

        return Format::SVG;
    }

    private function prepareSvg(string $svg, ComponentAttributeBag $componentAttributeBag): string
    {
        if (preg_match('/^\s*<\?xml[^>]*\?>\s*/i', $svg, $matches)) {
            $svg = substr($svg, strlen($matches[0]));
        }

        $domDocument = new DOMDocument;
        $libxmlState = libxml_use_internal_errors(true);

        try {
            $loaded = $domDocument->loadXML('<?xml version="1.0" encoding="UTF-8"?><root>'.$svg.'</root>');

            if (! $loaded) {
                throw GenerationException::invalidSvgString();
            }

            $domNodeList = $domDocument->getElementsByTagName('svg');

            if ($domNodeList->length === 0) {
                throw GenerationException::invalidSvgString();
            }

            /** @var DOMElement $firstSvgNode */
            $firstSvgNode = $domNodeList->item(0);

            $translatedTitle = __('QR Code');
            $titleText = is_string($translatedTitle) ? $translatedTitle : 'QR Code';

            $hasTitle = false;
            foreach ($firstSvgNode->childNodes as $child) {
                if ($child->nodeName === 'title') {
                    $hasTitle = true;
                }
            }

            if (! $hasTitle) {
                $titleNode = $domDocument->createElementNS('http://www.w3.org/2000/svg', 'title');
                $titleNode->appendChild($domDocument->createTextNode($titleText));

                if ($firstSvgNode->firstChild) {
                    $firstSvgNode->insertBefore($titleNode, $firstSvgNode->firstChild);
                } else {
                    $firstSvgNode->appendChild($titleNode);
                }
            }

            $mergedAttributes = $componentAttributeBag->merge([
                'role' => 'img',
                'aria-label' => __('QR Code'),
            ]);

            foreach ($mergedAttributes->getAttributes() as $key => $value) {
                if (is_scalar($value) || $value instanceof Stringable || $value === null) {
                    $firstSvgNode->setAttribute($key, (string) $value);
                }
            }

            $output = '';

            /** @var DOMElement $documentElement */
            $documentElement = $domDocument->documentElement;

            foreach ($documentElement->childNodes as $child) {
                /** @var string $serialized */
                $serialized = $domDocument->saveXML($child, LIBXML_NOEMPTYTAG);

                $output .= $serialized;
            }

            return $output;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($libxmlState);
        }
    }

    /**
     * Safely resolve Hex or CSV strings into an Rgb object.
     */
    private function resolveColor(string $color): ?Rgb
    {
        try {
            return Rgb::parse($color);
        } catch (InvalidConfigurationException $invalidConfigurationException) {
            logger()->warning('QrCodeComponent: '.$invalidConfigurationException->getMessage());

            return null;
        }
    }

    /**
     * Resolve a string containing one or two colors (separated by `;`, `,#`, or `, #`)
     * into a pair of Rgb value objects. If only one color is parsable, it is reused
     * for both slots. Returns null when no color can be parsed.
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

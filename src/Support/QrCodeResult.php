<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Support;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Linkxtr\QrCode\Enums\Format;
use Stringable;

final readonly class QrCodeResult implements Htmlable, Responsable, Stringable
{
    public function __construct(
        private string $content,
        private Format $format
    ) {}

    public function __toString(): string
    {
        return $this->content;
    }

    public function toHtml(): string
    {
        if ($this->format === Format::SVG) {
            return $this->content;
        }

        return sprintf('<img src="%s" alt="QR Code" />', $this->toDataUri());
    }

    public function toDataUri(): string
    {
        if ($this->format === Format::SVG) {
            return 'data:image/svg+xml;base64,'.base64_encode($this->content);
        }

        return 'data:image/'.$this->format->value.';base64,'.base64_encode($this->content);
    }

    /**
     * @param  Request  $request
     */
    public function toResponse(mixed $request): Response
    {
        $contentType = $this->format === Format::SVG
            ? 'image/svg+xml'
            : 'image/'.$this->format->value;

        return response($this->content, 200, [
            'Content-Type' => $contentType,
        ]);
    }

    public function isSvg(): bool
    {
        return $this->format === Format::SVG;
    }
}

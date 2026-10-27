<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Support;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Exceptions\CannotWriteFileException;
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
            return (string) preg_replace('/^\s*<\?xml[^>]*\?>\s*/i', '', $this->content);
        }

        $alt = __('QR Code');
        $altText = is_string($alt) ? $alt : 'QR Code';

        return sprintf('<img src="%s" alt="%s" />', $this->toDataUri(), e($altText));
    }

    public function toDataUri(): string
    {
        $mimeType = $this->getMimeType();

        return sprintf('data:%s;base64,%s', $mimeType, base64_encode($this->content));
    }

    /**
     * @param  Request  $request
     */
    public function toResponse(mixed $request): Response
    {
        $etag = '"'.md5($this->content).'"';

        return response($this->content, 200, [
            'Content-Type' => $this->getMimeType(),
            'ETag' => $etag,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function isSvg(): bool
    {
        return $this->format === Format::SVG;
    }

    public function getMimeType(): string
    {
        return match ($this->format) {
            Format::SVG => 'image/svg+xml',
            Format::EPS => 'application/postscript',
            default => 'image/'.$this->format->value,
        };
    }

    public function saveToFile(string $filename): void
    {
        $directory = dirname($filename);

        if (! is_dir($directory)) {
            throw CannotWriteFileException::toPath($filename);
        }

        $bytesWritten = @file_put_contents($filename, $this->content);

        if ($bytesWritten === false) {
            throw CannotWriteFileException::toPath($filename);
        }
    }
}

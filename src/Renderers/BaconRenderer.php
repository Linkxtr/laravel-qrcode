<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Renderers;

use BaconQrCode\Writer;
use Linkxtr\QrCode\DTOs\Config;
use Linkxtr\QrCode\Support\Bacon\MergerFactory;
use Linkxtr\QrCode\Support\Bacon\RendererFactory;
use Linkxtr\QrCode\Support\QrCodeResult;

final readonly class BaconRenderer
{
    public function __construct(private Config $config) {}

    /**
     * Render the QR code based on the provided payload and config.
     */
    public function render(string $payload): QrCodeResult
    {
        $renderer = RendererFactory::make($this->config);
        $writer = new Writer($renderer);

        $content = $writer->writeString(
            $payload,
            $this->config->getEncoding(),
            $this->config->getErrorCorrectionLevel()->toBaconErrorCorrectionLevel()
        );

        if ($this->config->getImageMerge() !== '') {
            $content = MergerFactory::make($this->config)->merge($content);
        }

        return new QrCodeResult($content, $this->config->getFormat());
    }
}

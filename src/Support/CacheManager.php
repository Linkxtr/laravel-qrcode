<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Support;

use Illuminate\Support\Facades\Cache;
use Linkxtr\QrCode\DTOs\Config;
use Linkxtr\QrCode\Renderers\BaconRenderer;

final readonly class CacheManager
{
    public function __construct(private Config $config, private string $payload) {}

    public static function handle(Config $config, string $paylod): QrCodeResult
    {
        $cache = new self($config, $paylod);

        return $cache->getResult();
    }

    private function getResult(): QrCodeResult
    {
        if (! $this->config->shouldCache()) {
            return $this->generate();
        }

        /** @var string $cachedQrCode */
        $cachedQrCode = Cache::remember($this->getKey(), $this->config->getCacheTtl(), fn (): string => $this->generate()->__toString());

        return new QrCodeResult($cachedQrCode, $this->config->getFormat());
    }

    private function generate(): QrCodeResult
    {
        $baconRenderer = new BaconRenderer($this->config);

        return $baconRenderer->render($this->payload);
    }

    private function getKey(): string
    {
        $serializedConfig = serialize($this->config);

        return 'qrcode_'.md5($this->payload.$serializedConfig);
    }
}

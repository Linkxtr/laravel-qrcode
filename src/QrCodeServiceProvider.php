<?php

declare(strict_types=1);

namespace Linkxtr\QrCode;

use Illuminate\Support\ServiceProvider;

final class QrCodeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('qrcode', fn(): \Linkxtr\QrCode\Generator => new Generator);
    }

    /**
     * @return class-string[]
     */
    public function provides(): array
    {
        return [Generator::class];
    }
}

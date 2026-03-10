<?php

declare(strict_types=1);

namespace Linkxtr\QrCode;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Linkxtr\QrCode\Components\QrCodeComponent;

final class QrCodeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Blade::component('qr-code', QrCodeComponent::class);
    }

    public function register(): void
    {
        $this->app->bind('qrcode', fn (): Generator => new Generator);
    }

    /**
     * @return class-string[]
     */
    public function provides(): array
    {
        return [Generator::class];
    }
}

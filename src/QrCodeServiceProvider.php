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
        $this->publishes([
            __DIR__.'/../config/qrcode.php' => config_path('qrcode.php'),
        ], 'qrcode-config');

        Blade::component('qr-code', QrCodeComponent::class);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/qrcode.php', 'qrcode');

        $this->app->bind('qrcode', function (): Generator {
            /** @var array<string, mixed> $config */
            $config = config('qrcode', []);

            return new Generator($config);
        });
    }

    /**
     * @return string[]
     */
    public function provides(): array
    {
        return [Generator::class, 'qrcode'];
    }
}

<?php

namespace Linkxtr\QrCode;

use Illuminate\Support\ServiceProvider;

class QrCodeServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/qrcode.php', 'qrcode'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/qrcode.php' => config_path('qrcode.php'),
            ], 'qrcode-config');
        }
    }
}

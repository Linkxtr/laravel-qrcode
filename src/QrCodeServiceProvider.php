<?php

declare(strict_types=1);

namespace Linkxtr\QrCode;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Linkxtr\QrCode\Components\QrCodeComponent;
use Linkxtr\QrCode\Console\Commands\GenerateQrCodeCommand;

final class QrCodeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/qrcode.php' => config_path('qrcode.php'),
            ], 'qrcode-config');

            $this->commands([
                GenerateQrCodeCommand::class,
            ]);
        }

        $this->loadViewComponentsAs('qrcode', [
            QrCodeComponent::class,
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/qrcode.php', 'qrcode'
        );

        $this->app->singleton('qrcode', function (): Generator {
            $config = Config::array('qrcode', []);

            return new Generator($config);
        });

        $this->app->alias('qrcode', Generator::class);
    }
}

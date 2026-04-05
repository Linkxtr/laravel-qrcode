<?php

declare(strict_types=1);

namespace Linkxtr\QrCode;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Linkxtr\QrCode\Components\QrCodeComponent;
use Linkxtr\QrCode\Console\Commands\GenerateQrCodeCommand;

final class QrCodeServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/qrcode.php' => config_path('qrcode.php'),
        ], 'qrcode-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateQrCodeCommand::class,
            ]);
        }

        Blade::component('qr-code', QrCodeComponent::class);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/qrcode.php', 'qrcode');

        $this->app->bind(Generator::class, function (): Generator {
            $config = Config::array('qrcode', []);

            return new Generator($config);
        });

        $this->app->alias(Generator::class, 'qrcode');
    }

    /**
     * @return string[]
     */
    public function provides(): array
    {
        return [Generator::class, 'qrcode'];
    }
}

<?php

namespace Linkxtr\QrCode;

use Illuminate\Support\ServiceProvider;

class QrCodeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('qrcode', function () {
            return new QrCode;
        });
    }

    /**
     * @return class-string[]
     */
    public function provides(): array
    {
        return [QrCode::class];
    }
}

<?php

declare(strict_types=1);

namespace Tests\Support;

use Linkxtr\QrCode\QrCodeServiceProvider;
use Linkxtr\QrCode\Facades\QrCode;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            QrCodeServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'QrCode' => QrCode::class,
        ];
    }
}

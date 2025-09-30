<?php

namespace Tests;

use Linkxtr\QrCode\QrCodeServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            QrCodeServiceProvider::class,
        ];
    }
}

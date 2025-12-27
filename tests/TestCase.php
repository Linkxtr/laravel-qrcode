<?php

namespace Tests;

use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
        // additional setup
    }

    protected function getPackageProviders($app)
    {
        return [
            \Linkxtr\QrCode\QrCodeServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'QrCode' => \Linkxtr\QrCode\Facades\QrCode::class,
        ];
    }
}

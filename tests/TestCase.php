<?php

namespace Tests;

use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
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

    protected function tearDown(): void
    {
        parent::tearDown();
        global $mockFilePutContents;
        $mockFilePutContents = false;
    }
}

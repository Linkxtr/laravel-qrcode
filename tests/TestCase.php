<?php

declare(strict_types=1);

namespace Tests;

use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        global $mockFilePutContents;
        $mockFilePutContents = false;
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

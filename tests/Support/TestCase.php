<?php

declare(strict_types=1);

namespace Tests\Support;

use Linkxtr\QrCode\QrCodeServiceProvider;
use Linkxtr\QrCode\Facades\QrCode;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        global $mockFileGetContents, $mockImagickLoaded, $mockGdLoaded,
               $mockFilePutContents, $mockImageColorAllocateAlpha, $mockImageColorAllocate,
               $mockImageCreateTrueColor, $mockObGetClean, $mockImageFill,
               $mockImageCopy, $mockImageCopyResampled, $mockImageSaveAlpha,
               $mock_imagepng_empty, $mockImageCreateFromString;

        $mockFileGetContents = null;
        $mockImagickLoaded = true;
        $mockGdLoaded = true;
        $mockFilePutContents = null;
        $mockImageColorAllocateAlpha = null;
        $mockImageColorAllocate = null;
        $mockImageCreateTrueColor = null;
        $mockObGetClean = null;
        $mockImageFill = null;
        $mockImageCopy = null;
        $mockImageCopyResampled = null;
        $mockImageSaveAlpha = null;
        $mock_imagepng_empty = null;
        $mockImageCreateFromString = null;

        parent::tearDown();
    }

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

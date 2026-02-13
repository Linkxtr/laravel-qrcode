<?php

declare(strict_types=1);

use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Eye\SimpleCircleEye;
use BaconQrCode\Renderer\Eye\SquareEye;
use BaconQrCode\Renderer\Image\EpsImageBackEnd;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\Module\DotsModule;
use BaconQrCode\Renderer\Module\RoundnessModule;
use BaconQrCode\Renderer\Module\SquareModule;
use BaconQrCode\Renderer\RendererStyle\Gradient;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use Illuminate\Support\HtmlString;
use Linkxtr\QrCode\Generator;
use Linkxtr\QrCode\Support\Image;

require_once __DIR__.'/Support/Overrides.php';

covers(Generator::class);

beforeEach(function () {
    global $mockImagickLoaded;
    $mockImagickLoaded = true;
});

test('chaining is working', function () {
    expect((new Generator)->size(100))->toBeInstanceOf(Generator::class);
    expect((new Generator)->format('png'))->toBeInstanceOf(Generator::class);
    expect((new Generator)->color(100, 100, 100))->toBeInstanceOf(Generator::class);
    expect((new Generator)->color(100, 100, 100, 25))->toBeInstanceOf(Generator::class);
    expect((new Generator)->backgroundColor(100, 100, 100))->toBeInstanceOf(Generator::class);
    expect((new Generator)->backgroundColor(100, 100, 100, 25))->toBeInstanceOf(Generator::class);
    expect((new Generator)->eyeColor(0, 100, 100, 100))->toBeInstanceOf(Generator::class);
    expect((new Generator)->gradient(100, 100, 100, 100, 100, 100, 'vertical'))->toBeInstanceOf(Generator::class);
    expect((new Generator)->eye('square'))->toBeInstanceOf(Generator::class);
    expect((new Generator)->style('square'))->toBeInstanceOf(Generator::class);
    expect((new Generator)->encoding('utf-8'))->toBeInstanceOf(Generator::class);
    expect((new Generator)->errorCorrection('L'))->toBeInstanceOf(Generator::class);
    expect((new Generator)->margin(10))->toBeInstanceOf(Generator::class);
});

test('error correction level supported', function () {
    expect((new Generator)->errorCorrection('L'))->toBeInstanceOf(Generator::class);
    expect((new Generator)->errorCorrection('l'))->toBeInstanceOf(Generator::class);
    expect((new Generator)->errorCorrection('M'))->toBeInstanceOf(Generator::class);
    expect((new Generator)->errorCorrection('m'))->toBeInstanceOf(Generator::class);
    expect((new Generator)->errorCorrection('Q'))->toBeInstanceOf(Generator::class);
    expect((new Generator)->errorCorrection('q'))->toBeInstanceOf(Generator::class);
    expect((new Generator)->errorCorrection('H'))->toBeInstanceOf(Generator::class);
    expect((new Generator)->errorCorrection('h'))->toBeInstanceOf(Generator::class);
});

test('gradient supported', function () {
    expect((new Generator)->gradient(100, 100, 100, 100, 100, 100, 'vertical'))->toBeInstanceOf(Generator::class);
    expect((new Generator)->gradient(100, 100, 100, 100, 100, 100, 'horizontal'))->toBeInstanceOf(Generator::class);
    expect((new Generator)->gradient(100, 100, 100, 100, 100, 100, 'diagonal'))->toBeInstanceOf(Generator::class);
    expect((new Generator)->gradient(100, 100, 100, 100, 100, 100, 'inverse_diagonal'))->toBeInstanceOf(Generator::class);
    expect((new Generator)->gradient(100, 100, 100, 100, 100, 100, 'radial'))->toBeInstanceOf(Generator::class);
});

test('size is passed to renderer', function () {
    $qrCode = (new Generator)->size(200);

    expect($qrCode->getRendererStyle()->getSize())->toBe(200);
});

test('default size is 100', function () {
    $qrCode = new Generator;

    expect($qrCode->getRendererStyle()->getSize())->toBe(100);
});

test('margin is passed to renderer', function () {
    $qrCode = (new Generator)->margin(20);

    expect($qrCode->getRendererStyle()->getMargin())->toBe(20);
});

test('default margin is 0', function () {
    $qrCode = new Generator;

    expect($qrCode->getRendererStyle()->getMargin())->toBe(0);
});

test('format is passed to formatter', function () {
    $qrCode = (new Generator)->format('png');
    expect($qrCode->getFormatter())->toBeInstanceOf(ImagickImageBackEnd::class);

    $qrCode = (new Generator)->format('svg');
    expect($qrCode->getFormatter())->toBeInstanceOf(SvgImageBackEnd::class);

    $qrCode = (new Generator)->format('eps');
    expect($qrCode->getFormatter())->toBeInstanceOf(EpsImageBackEnd::class);
});

it('throws exception if format is not supported', function () {
    (new Generator)->format('format');
})->throws(InvalidArgumentException::class);

test('color is passed to formatter', function () {
    $qrCode = (new Generator)->color(100, 150, 200);
    expect($qrCode->getFill()->getForegroundColor()->toRgb()->getRed())->toBe(100);
    expect($qrCode->getFill()->getForegroundColor()->toRgb()->getGreen())->toBe(150);
    expect($qrCode->getFill()->getForegroundColor()->toRgb()->getBlue())->toBe(200);

    $qrCode = (new Generator)->backgroundColor(50, 75, 100);
    expect($qrCode->getFill()->getBackgroundColor()->toRgb()->getRed())->toBe(50);
    expect($qrCode->getFill()->getBackgroundColor()->toRgb()->getGreen())->toBe(75);
    expect($qrCode->getFill()->getBackgroundColor()->toRgb()->getBlue())->toBe(100);

    $qrCode = (new Generator)->color(100, 150, 200, 100);
    /** @var Alpha $foregroundColor */
    $foregroundColor = $qrCode->getFill()->getForegroundColor();
    expect($foregroundColor)->toBeInstanceOf(Alpha::class);
    expect($foregroundColor->getAlpha())->toBe(100);

    $qrCode = (new Generator)->backgroundColor(50, 75, 100, 75);
    /** @var Alpha $backgroundColor */
    $backgroundColor = $qrCode->getFill()->getBackgroundColor();
    expect($backgroundColor)->toBeInstanceOf(Alpha::class);
    expect($backgroundColor->getAlpha())->toBe(75);
});

test('default background color is white and foreground color is black', function () {
    $qrCode = new Generator;
    expect($qrCode->getFill()->getBackgroundColor()->toRgb()->getRed())->toBe(255);
    expect($qrCode->getFill()->getBackgroundColor()->toRgb()->getGreen())->toBe(255);
    expect($qrCode->getFill()->getBackgroundColor()->toRgb()->getBlue())->toBe(255);
    expect($qrCode->getFill()->getForegroundColor()->toRgb()->getRed())->toBe(0);
    expect($qrCode->getFill()->getForegroundColor()->toRgb()->getGreen())->toBe(0);
    expect($qrCode->getFill()->getForegroundColor()->toRgb()->getBlue())->toBe(0);
});

test('eye color is passed to renderer', function () {
    $qrCode = (new Generator)->eyeColor(0, 0, 10, 50, 1, 8, 18);
    $qrCode->eyeColor(1, 100, 20, 60, 2, 10, 20);
    $qrCode->eyeColor(2, 200, 30, 70, 3, 12, 22);

    expect($qrCode->getFill()->getTopLeftEyeFill()->getExternalColor()->toRgb()->getRed())->toBe(0);
    expect($qrCode->getFill()->getTopRightEyeFill()->getExternalColor()->toRgb()->getRed())->toBe(100);
    expect($qrCode->getFill()->getBottomLeftEyeFill()->getExternalColor()->toRgb()->getRed())->toBe(200);
    expect($qrCode->getFill()->getTopLeftEyeFill()->getExternalColor()->toRgb()->getGreen())->toBe(10);
    expect($qrCode->getFill()->getTopRightEyeFill()->getExternalColor()->toRgb()->getGreen())->toBe(20);
    expect($qrCode->getFill()->getBottomLeftEyeFill()->getExternalColor()->toRgb()->getGreen())->toBe(30);
    expect($qrCode->getFill()->getTopLeftEyeFill()->getExternalColor()->toRgb()->getBlue())->toBe(50);
    expect($qrCode->getFill()->getTopRightEyeFill()->getExternalColor()->toRgb()->getBlue())->toBe(60);
    expect($qrCode->getFill()->getBottomLeftEyeFill()->getExternalColor()->toRgb()->getBlue())->toBe(70);

    expect($qrCode->getFill()->getTopLeftEyeFill()->getInternalColor()->toRgb()->getRed())->toBe(1);
    expect($qrCode->getFill()->getTopRightEyeFill()->getInternalColor()->toRgb()->getRed())->toBe(2);
    expect($qrCode->getFill()->getBottomLeftEyeFill()->getInternalColor()->toRgb()->getRed())->toBe(3);
    expect($qrCode->getFill()->getTopLeftEyeFill()->getInternalColor()->toRgb()->getGreen())->toBe(8);
    expect($qrCode->getFill()->getTopRightEyeFill()->getInternalColor()->toRgb()->getGreen())->toBe(10);
    expect($qrCode->getFill()->getBottomLeftEyeFill()->getInternalColor()->toRgb()->getGreen())->toBe(12);
    expect($qrCode->getFill()->getTopLeftEyeFill()->getInternalColor()->toRgb()->getBlue())->toBe(18);
    expect($qrCode->getFill()->getTopRightEyeFill()->getInternalColor()->toRgb()->getBlue())->toBe(20);
    expect($qrCode->getFill()->getBottomLeftEyeFill()->getInternalColor()->toRgb()->getBlue())->toBe(22);
});

it('throws exception if eye color greater than 2', function () {
    (new Generator)->eyeColor(3, 0, 0, 0, 255, 255, 255);
})->throws(InvalidArgumentException::class);

it('throws exception if eye color less than 0', function () {
    (new Generator)->eyeColor(-1, 0, 0, 0, 255, 255, 255);
})->throws(InvalidArgumentException::class);

test('gradient is passed to renderer', function () {
    $qrCode = (new Generator)->gradient(100, 150, 200, 50, 75, 100, 'vertical');
    expect($qrCode->getFill()->getForegroundGradient())->toBeInstanceOf(Gradient::class);
});

it('throws exception if gradient type is not supported', function () {
    (new Generator)->gradient(100, 150, 200, 50, 75, 100, 'foo');
})->throws(InvalidArgumentException::class);

test('eye style is passed to renderer', function () {
    $qrCode = (new Generator)->eye('circle');
    expect($qrCode->getEye())->toBeInstanceOf(SimpleCircleEye::class);

    $qrCode = (new Generator)->eye('square');
    expect($qrCode->getEye())->toBeInstanceOf(SquareEye::class);
});

it('throws exception if eye style is not supported', function () {
    (new Generator)->eye('dot');
})->throws(InvalidArgumentException::class);

test('module style is passed to renderer', function () {
    $qrCode = (new Generator)->style('dot', 1);
    expect($qrCode->getModule())->toBeInstanceOf(DotsModule::class);

    $qrCode = (new Generator)->style('square');
    expect($qrCode->getModule())->toBeInstanceOf(SquareModule::class);

    $qrCode = (new Generator)->style('round');
    expect($qrCode->getModule())->toBeInstanceOf(RoundnessModule::class);
});

it('throws exception if module style is not supported', function () {
    (new Generator)->style('triangle');
})->throws(InvalidArgumentException::class);

it('throws exception if roundness module with negative roundness is set', function () {
    (new Generator)->style('round', -.5);
})->throws(InvalidArgumentException::class);

it('throws exception if roundness module with more than 1 roundness is set', function () {
    (new Generator)->style('round', 1.1);
})->throws(InvalidArgumentException::class);

it('throws exception if roundness module with 0 roundness is set', function () {
    (new Generator)->style('round', 0);
})->throws(InvalidArgumentException::class);

it('throws exception if dot module with negative roundness is set', function () {
    (new Generator)->style('dot', -.5);
})->throws(InvalidArgumentException::class);

it('throws exception if dot module with more than 1 roundness is set', function () {
    (new Generator)->style('dot', 1.1);
})->throws(InvalidArgumentException::class);

it('throws exception if dot module with 0 roundness is set', function () {
    (new Generator)->style('dot', 0);
})->throws(InvalidArgumentException::class);

test('get renderer return a renderer instance', function () {
    $qrCode = new Generator;
    expect($qrCode->getRendererStyle())->not->toBeNull()->toBeInstanceOf(RendererStyle::class);
});

it('throws exception if data type is not supported', function () {
    (new Generator)->notReal('fooBar');
})->throws(BadMethodCallException::class);

it('return html string', function () {
    $qrCode = new Generator;
    expect($qrCode->generate('This is a test'))->toBeInstanceOf(HtmlString::class);
});

it('saves generated qrcode to file', function () {
    $file = __DIR__.'/generated_qr.svg';

    if (file_exists($file)) {
        unlink($file);
    }

    try {
        (new Generator)->generate('test file', $file);
        expect(file_exists($file))->toBeTrue();
        expect(file_get_contents($file))->toContain('<svg');
    } finally {
        unlink($file);
    }
});

test('Data types magic call', function () {
    $qrCode = new Generator;
    expect($qrCode->BTC('btcaddress', 0.0034))->toBeInstanceOf(HtmlString::class);
    expect($qrCode->Email('email@example.com'))->toBeInstanceOf(HtmlString::class);
    expect($qrCode->Geo('40.7128', '-74.0060'))->toBeInstanceOf(HtmlString::class);
    expect($qrCode->PhoneNumber('1234567890'))->toBeInstanceOf(HtmlString::class);
    expect($qrCode->SMS('1234567890'))->toBeInstanceOf(HtmlString::class);
    expect($qrCode->WiFi(['ssid' => 'SSID']))->toBeInstanceOf(HtmlString::class);
    expect($qrCode->WhatsApp('+1234567890', 'Hello'))->toBeInstanceOf(HtmlString::class);
    expect($qrCode->CalendarEvent([
        'summary' => 'Team Meeting',
        'start' => '2024-06-01 10:00:00 UTC',
        'end' => '2024-06-01 11:00:00 UTC',
    ]))->toBeInstanceOf(HtmlString::class);
});

test('Data types magic call is case sensitive', function () {
    (new Generator)->geo('40.7128', '-74.0060');
})->throws(BadMethodCallException::class);

it('merges image into qrcode with png format', function () {
    $pngData = (new Generator)
        ->format('png')
        ->size(300)
        ->merge(__DIR__.'/images/linkxtr.png', 0.2, true)
        ->generate('test');

    $image = new Image($pngData->__toString());
    expect($image->getWidth())->toBe(300);
});

it('merges image into qrcode with webp format', function () {
    $webpData = (new Generator)
        ->format('webp')
        ->size(300)
        ->merge(__DIR__.'/images/linkxtr.png', 0.2, true)
        ->generate('test');

    $image = new Image($webpData->__toString());
    expect($image->getWidth())->toBe(300);
});

it('merges string content with png format', function () {
    $content = file_get_contents(__DIR__.'/images/linkxtr.png');
    $pngData = (new Generator)
        ->format('png')
        ->mergeString($content, 0.2)
        ->generate('test');

    expect($pngData)->not->toBeEmpty();
});

it('merges string content with webp format', function () {
    $content = file_get_contents(__DIR__.'/images/linkxtr.png');
    $webpData = (new Generator)
        ->format('webp')
        ->mergeString($content, 0.2)
        ->generate('test');

    expect($webpData)->not->toBeEmpty();
});

it('can merge image with relative path with png format', function () {
    // base_path() is mocked in Overrides.php to return __DIR__ (tests/)
    $path = 'images/linkxtr.png';

    $pngData = (new Generator)
        ->format('png')
        ->merge($path, 0.2, false)
        ->generate('test');

    expect($pngData)->not->toBeEmpty();
});

it('can merge image with relative path with webp format', function () {
    // base_path() is mocked in Overrides.php to return __DIR__ (tests/)
    $path = 'images/linkxtr.png';

    $webpData = (new Generator)
        ->format('webp')
        ->merge($path, 0.2, false)
        ->generate('test');

    expect($webpData)->not->toBeEmpty();
});

it('can merge image with eps format', function () {
    $epsData = (new Generator)
        ->format('eps')
        ->size(300)
        ->merge(__DIR__.'/images/linkxtr.png', 0.2, true)
        ->generate('test');

    expect($epsData)->not->toBeEmpty();
});

it('throws exception if error correction level is not supported', function () {
    (new Generator)->errorCorrection('foo');
})->throws(InvalidArgumentException::class);

it('throws exception if file_put_contents fails', function () {
    global $mockFilePutContents;
    $mockFilePutContents = true;

    try {
        (new Generator)->generate('test file', __DIR__.'/fail_file.svg');
    } finally {
        $mockFilePutContents = false;
    }
})->throws(RuntimeException::class, 'Failed to write QR code to file');

it('throws exception if file_get_contents fails', function () {
    global $mockFileGetContents;
    $mockFileGetContents = false;

    try {
        (new Generator)->merge('some_image.png');
    } finally {
        $mockFileGetContents = null;
    }
})->throws(InvalidArgumentException::class, 'Failed to read image file');

it('throws exception if imagick and gd are not loaded and format is png', function () {
    global $mockImagickLoaded;
    global $mockGdLoaded;
    $mockImagickLoaded = false;
    $mockGdLoaded = false;

    try {
        (new Generator)->format('png')->generate('test');
    } finally {
        $mockImagickLoaded = true;
        $mockGdLoaded = true;
    }
})->throws(RuntimeException::class, 'The imagick or gd extension is required to generate QR codes.');

it('throws exception if imagick is not loaded and format is webp', function () {
    global $mockImagickLoaded;
    $mockImagickLoaded = false;

    try {
        (new Generator)->format('webp')->generate('test');
    } finally {
        $mockImagickLoaded = true;
    }
})->throws(RuntimeException::class, 'The imagick extension is required to generate QR codes in webp format.');

test('webp format is supported', function () {
    $qrCode = (new Generator)->format('webp');
    expect($qrCode->getFormatter())->toBeInstanceOf(ImagickImageBackEnd::class);
});

test('can generate webp', function () {
    $qrCode = (new Generator)->format('webp')->generate('test');
    expect($qrCode)->toBeInstanceOf(HtmlString::class);

    $data = $qrCode->toHtml();
    // Check magic bytes for WebP (RIFF....WEBP)
    // RIFF is bytes 0-3, WEBP is bytes 8-11
    expect(substr($data, 0, 4))->toBe('RIFF');
    expect(substr($data, 8, 4))->toBe('WEBP');
});

it('throws exception if data type class does not implement interface', function () {
    (new Generator)->InvalidDataType();
})->throws(BadMethodCallException::class);

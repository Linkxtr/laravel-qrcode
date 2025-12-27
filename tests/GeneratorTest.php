<?php

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
use DASPRiD\Enum\Exception\IllegalArgumentException;
use Illuminate\Support\HtmlString;
use Linkxtr\QrCode\Generator;
use Linkxtr\QrCode\Image;

require_once __DIR__.'/Overrides.php';

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

test('size is passed to renderer', function () {
    $qrCode = (new Generator)->size(200);

    expect($qrCode->getRendererStyle()->getSize())->toBe(200);
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
    $qrCode = (new Generator)->style('dot');
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

it('throws exception if roundness module with 1 roundness is set', function () {
    (new Generator)->style('round', 1);
})->throws(InvalidArgumentException::class);

it('throws exception if dot module with negative roundness is set', function () {
    (new Generator)->style('dot', -.5);
})->throws(InvalidArgumentException::class);

it('throws exception if dot module with more than 1 roundness is set', function () {
    (new Generator)->style('dot', 1.1);
})->throws(InvalidArgumentException::class);

it('throws exception if dot module with 1 roundness is set', function () {
    (new Generator)->style('dot', 1);
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
});

it('merges image into qrcode', function () {
    $pngData = (new Generator)
        ->format('png')
        ->size(300)
        ->merge(__DIR__.'/images/linkxtr.png', 0.2, true)
        ->generate('test');

    $image = new Image($pngData);
    expect($image->getWidth())->toBe(300);
});

it('merges string content', function () {
    $content = file_get_contents(__DIR__.'/images/linkxtr.png');
    $pngData = (new Generator)
        ->format('png')
        ->mergeString($content, 0.2)
        ->generate('test');

    expect($pngData)->not->toBeEmpty();
});

it('can merge image with relative path', function () {
    // base_path() is mocked in Overrides.php to return __DIR__ (tests/)
    $path = 'images/linkxtr.png';

    $pngData = (new Generator)
        ->format('png')
        ->merge($path, 0.2, false)
        ->generate('test');

    expect($pngData)->not->toBeEmpty();
});

it('throws exception if error correction level is not supported', function () {
    (new Generator)->errorCorrection('foo');
})->throws(InvalidArgumentException::class);
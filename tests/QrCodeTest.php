<?php

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
use Linkxtr\QrCode\QrCode;

test('chaining is working', function () {
    expect(new QrCode()->size(100))->toBeInstanceOf(QrCode::class);
    expect(new QrCode()->format('png'))->toBeInstanceOf(QrCode::class);
    expect(new QrCode()->color(100, 100, 100))->toBeInstanceOf(QrCode::class);
    expect(new QrCode()->backgroundColor(100, 100, 100))->toBeInstanceOf(QrCode::class);
    expect(new QrCode()->eyeColor(0, 100, 100, 100))->toBeInstanceOf(QrCode::class);
    expect(new QrCode()->gradient(100, 100, 100, 100, 100, 100, 'vertical'))->toBeInstanceOf(QrCode::class);
    expect(new QrCode()->eye('square'))->toBeInstanceOf(QrCode::class);
    expect(new QrCode()->style('square'))->toBeInstanceOf(QrCode::class);
    expect(new QrCode()->encoding('utf-8'))->toBeInstanceOf(QrCode::class);
    expect(new QrCode()->errorCorrection('L'))->toBeInstanceOf(QrCode::class);
    expect(new QrCode()->margin(10))->toBeInstanceOf(QrCode::class);
});

test('size is passed to renderer', function () {
    $qrCode = new QrCode()->size(200);

    expect($qrCode->getRendererStyle()->getSize())->toBe(200);
});

test('style format is passed to renderer', function () {
    $qrCode = new QrCode()->format('png');
    expect($qrCode->getFormatter())->toBeInstanceOf(ImagickImageBackEnd::class);

    $qrCode = new QrCode()->format('svg');
    expect($qrCode->getFormatter())->toBeInstanceOf(SvgImageBackEnd::class);

    $qrCode = new QrCode()->format('eps');
    expect($qrCode->getFormatter())->toBeInstanceOf(EpsImageBackEnd::class);
});

it('throws exception if format is not supported', function () {
    new QrCode()->format('jpg');
})->throws(InvalidArgumentException::class);

test('color is passed to renderer', function () {
    $qrCode = new QrCode()->color(100, 150, 200);
    expect($qrCode->getFill()->getForegroundColor()->toRgb()->getRed())->toBe(100);
    expect($qrCode->getFill()->getForegroundColor()->toRgb()->getGreen())->toBe(150);
    expect($qrCode->getFill()->getForegroundColor()->toRgb()->getBlue())->toBe(200);

    $qrCode = new QrCode()->backgroundColor(50, 75, 100);
    expect($qrCode->getFill()->getBackgroundColor()->toRgb()->getRed())->toBe(50);
    expect($qrCode->getFill()->getBackgroundColor()->toRgb()->getGreen())->toBe(75);
    expect($qrCode->getFill()->getBackgroundColor()->toRgb()->getBlue())->toBe(100);

    $qrCode = new QrCode()->color(100, 150, 200, 100);
    expect($qrCode->getFill()->getForegroundColor()->getAlpha())->toBe(100);

    $qrCode = new QrCode()->backgroundColor(50, 75, 100, 75);
    expect($qrCode->getFill()->getBackgroundColor()->getAlpha())->toBe(75);
});

test('eye color is passed to renderer', function () {
    $qrCode = new QrCode()->eyeColor(0, 0, 10, 50, 1, 8, 18);
    $qrCode->eyeColor(1, 100, 20, 60, 2, 10, 20);
    $qrCode->eyeColor(2, 200, 30, 70, 3, 12, 22);

    expect($qrCode->getFill()->getTopLeftEyeFill()->getExternalColor()->getRed())->toBe(0);
    expect($qrCode->getFill()->getTopRightEyeFill()->getExternalColor()->getRed())->toBe(100);
    expect($qrCode->getFill()->getBottomLeftEyeFill()->getExternalColor()->getRed())->toBe(200);
    expect($qrCode->getFill()->getTopLeftEyeFill()->getExternalColor()->getGreen())->toBe(10);
    expect($qrCode->getFill()->getTopRightEyeFill()->getExternalColor()->getGreen())->toBe(20);
    expect($qrCode->getFill()->getBottomLeftEyeFill()->getExternalColor()->getGreen())->toBe(30);
    expect($qrCode->getFill()->getTopLeftEyeFill()->getExternalColor()->getBlue())->toBe(50);
    expect($qrCode->getFill()->getTopRightEyeFill()->getExternalColor()->getBlue())->toBe(60);
    expect($qrCode->getFill()->getBottomLeftEyeFill()->getExternalColor()->getBlue())->toBe(70);

    expect($qrCode->getFill()->getTopLeftEyeFill()->getInternalColor()->getRed())->toBe(1);
    expect($qrCode->getFill()->getTopRightEyeFill()->getInternalColor()->getRed())->toBe(2);
    expect($qrCode->getFill()->getBottomLeftEyeFill()->getInternalColor()->getRed())->toBe(3);
    expect($qrCode->getFill()->getTopLeftEyeFill()->getInternalColor()->getGreen())->toBe(8);
    expect($qrCode->getFill()->getTopRightEyeFill()->getInternalColor()->getGreen())->toBe(10);
    expect($qrCode->getFill()->getBottomLeftEyeFill()->getInternalColor()->getGreen())->toBe(12);
    expect($qrCode->getFill()->getTopLeftEyeFill()->getInternalColor()->getBlue())->toBe(18);
    expect($qrCode->getFill()->getTopRightEyeFill()->getInternalColor()->getBlue())->toBe(20);
    expect($qrCode->getFill()->getBottomLeftEyeFill()->getInternalColor()->getBlue())->toBe(22);
});

it('throws exception if eye color greater than 2', function () {
    new QrCode()->eyeColor(3, 0, 0, 0, 255, 255, 255);
})->throws(InvalidArgumentException::class);

it('throws exception if eye color less than 0', function () {
    new QrCode()->eyeColor(-1, 0, 0, 0, 255, 255, 255);
})->throws(InvalidArgumentException::class);

test('gradient is passed to renderer', function () {
    $qrCode = new QrCode()->gradient(100, 150, 200, 50, 75, 100, 'vertical');
    expect($qrCode->getFill()->getForegroundGradient())->toBeInstanceOf(Gradient::class);
});

it('throws exception if gradient type is not supported', function () {
    new QrCode()->gradient(100, 150, 200, 50, 75, 100, 'foo');
})->throws(IllegalArgumentException::class);

test('eye style is passed to renderer', function () {
    $qrCode = new QrCode()->eye('circle');
    expect($qrCode->getEye())->toBeInstanceOf(SimpleCircleEye::class);

    $qrCode = new QrCode()->eye('square');
    expect($qrCode->getEye())->toBeInstanceOf(SquareEye::class);
});

it('throws exception if eye style is not supported', function () {
    new QrCode()->eye('dot');
})->throws(InvalidArgumentException::class);

test('module style is passed to renderer', function () {
    $qrCode = new QrCode()->style('dot');
    expect($qrCode->getModule())->toBeInstanceOf(DotsModule::class);

    $qrCode = new QrCode()->style('square');
    expect($qrCode->getModule())->toBeInstanceOf(SquareModule::class);

    $qrCode = new QrCode()->style('round');
    expect($qrCode->getModule())->toBeInstanceOf(RoundnessModule::class);
});

it('throws exception if module style is not supported', function () {
    new QrCode()->style('triangle');
})->throws(InvalidArgumentException::class);

it('throws exception if roundness module with negative roundness is set', function () {
    new QrCode()->style('round', -.5);
})->throws(InvalidArgumentException::class);

it('throws exception if roundness module with more than 1 roundness is set', function () {
    new QrCode()->style('round', 1.1);
})->throws(InvalidArgumentException::class);

it('throws exception if roundness module with 1 roundness is set', function () {
    new QrCode()->style('round', 1);
})->throws(InvalidArgumentException::class);

it('throws exception if dot module with negative roundness is set', function () {
    new QrCode()->style('dot', -.5);
})->throws(InvalidArgumentException::class);

it('throws exception if dot module with more than 1 roundness is set', function () {
    new QrCode()->style('dot', 1.1);
})->throws(InvalidArgumentException::class);

it('throws exception if dot module with 1 roundness is set', function () {
    new QrCode()->style('dot', 1);
})->throws(InvalidArgumentException::class);

test('get renderer return a renderer instance', function () {
    $qrCode = new QrCode;
    expect($qrCode->getRendererStyle())->not->toBeNull()->toBeInstanceOf(RendererStyle::class);
});

it('throws exception if data type is not supported', function () {
    new QrCode()->notReal('fooBar');
})->throws(BadMethodCallException::class);

it('return html string', function () {
    $qrCode = new QrCode;
    expect($qrCode->generate('This is a test'))->toBeInstanceOf(HtmlString::class);
});

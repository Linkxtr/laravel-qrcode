<?php

declare(strict_types=1);

use BaconQrCode\Renderer\RendererStyle\EyeFill;
use BaconQrCode\Renderer\RendererStyle\Gradient;
use Illuminate\Support\HtmlString;
use Linkxtr\QrCode\Enums\ColorModel;
use Linkxtr\QrCode\Enums\ErrorCorrectionLevel;
use Linkxtr\QrCode\Enums\EyeStyle;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Enums\GradientType;
use Linkxtr\QrCode\Enums\Style;
use Linkxtr\QrCode\Generator;

require_once __DIR__.'/../Support/Overrides.php';

covers(Generator::class);

// beforeEach(function () {
//     global $mockImagickLoaded, $mockGdLoaded;
//     $mockImagickLoaded = true;
//     $mockGdLoaded = true;
// });

it('passes array config from constructor to the underlying DTO', function () {
    $generator = new Generator(['size' => 350, 'margin' => 2]);
    $config = invade($generator)->config;

    expect($config->getSize())->toBe(350)
        ->and($config->getMargin())->toBe(2);
});

it('can register and call custom macros that return a string payload', function () {
    Generator::macro('myMacro', function (string $data) {
        return 'dummy:'.$data;
    });

    $generator = new Generator([]);

    expect($generator->myMacro('hello-world'))->toBeInstanceOf(HtmlString::class);
})->after(function () {
    Generator::flushMacros();
});

it('can register and call custom macros that return pre-styled generation', function () {
    Generator::macro('myStyledMacro', function (string $data) {
        return $this->size(500)->generate($data);
    });

    $generator = new Generator([]);

    expect($generator->myStyledMacro('hello-world'))->toBeInstanceOf(HtmlString::class);
})->after(function () {
    Generator::flushMacros();
});

test('macro returning HtmlString is returned directly without regeneration', function () {
    $expectedHtml = new HtmlString('<svg id="exact-macro-match"></svg>');

    Generator::macro('returnsHtml', function () use ($expectedHtml) {
        return $expectedHtml;
    });

    $generator = new Generator;
    $result = $generator->returnsHtml();

    expect($result)->toBe($expectedHtml)
        ->and($result->toHtml())->toBe('<svg id="exact-macro-match"></svg>');
})->after(function () {
    Generator::flushMacros();
});

test('macro returning a Stringable object is successfully generated', function () {
    Generator::macro('returnsStringable', function () {
        return new class implements Stringable
        {
            public function __toString(): string
            {
                return 'stringable-payload';
            }
        };
    });

    $generator = new Generator;
    $result = $generator->returnsStringable();

    expect($result)->toBeInstanceOf(HtmlString::class)
        ->and($result->toHtml())->toContain('<svg');
})->after(function () {
    Generator::flushMacros();
});

it('throws an exception for unsupported type returns from macro', function () {
    Generator::macro('returnArray', function () {
        return ['an' => 'array'];
    });

    $generator = new Generator([]);

    $generator->returnArray();
})->throws(UnexpectedValueException::class, 'Macro "returnArray" must return a string, Stringable, or HtmlString. array returned.')->after(function () {
    Generator::flushMacros();
});

it('still delegates to data types if macro is not registered', function () {
    $generator = new Generator([]);

    $result = $generator->Email('test@example.com');

    expect($result)->toBeInstanceOf(HtmlString::class);
    expect((string) $result)->toContain('<svg');
});

test('fluent configuration methods delegate to config and return self', function () {
    $generator = new Generator;

    expect($generator->size(500))->toBeInstanceOf(Generator::class)
        ->and(invade($generator)->config->getSize())->toBe(500);

    expect($generator->margin(10))->toBeInstanceOf(Generator::class)
        ->and(invade($generator)->config->getMargin())->toBe(10);

    expect($generator->format('png'))->toBeInstanceOf(Generator::class)
        ->and(invade($generator)->config->getFormat())->toBe(Format::PNG);

    expect($generator->errorCorrection('H'))->toBeInstanceOf(Generator::class)
        ->and(invade($generator)->config->getErrorCorrectionLevel())->toBe(ErrorCorrectionLevel::H);

    expect($generator->encoding('ISO-8859-1'))->toBeInstanceOf(Generator::class)
        ->and(invade($generator)->config->getEncoding())->toBe('ISO-8859-1');

    expect($generator->style('dot', 0.8))->toBeInstanceOf(Generator::class)
        ->and(invade($generator)->config->getStyle())->toBe(Style::DOT)
        ->and(invade($generator)->config->getStyleSize())->toBe(0.8);

    expect($generator->eye('circle'))->toBeInstanceOf(Generator::class)
        ->and(invade($generator)->config->getEyeStyle())->toBe(EyeStyle::CIRCLE);

    expect($generator->internalEye('square'))->toBeInstanceOf(Generator::class)
        ->and(invade($generator)->config->getInternalEyeStyle())->toBe(EyeStyle::SQUARE);

    expect($generator->color(10, 20, 30))->toBeInstanceOf(Generator::class)
        ->and(invade($generator)->config->getColorValue()->c1)->toBe(10);

    expect($generator->backgroundColor(10, 20, 30))->toBeInstanceOf(Generator::class)
        ->and(invade($generator)->config->getBackgroundColorValue()->c1)->toBe(10);

    expect($generator->cmyk())->toBeInstanceOf(Generator::class)
        ->and(invade($generator)->config->getColorModel())->toBe(ColorModel::CMYK);

    expect($generator->rgb())->toBeInstanceOf(Generator::class)
        ->and(invade($generator)->config->getColorModel())->toBe(ColorModel::RGB);

    expect($generator->gray(50))->toBeInstanceOf(Generator::class)
        ->and(invade($generator)->config->getColorModel())->toBe(ColorModel::GRAY);

    expect($generator->eyeColor(0, 20, 50, 70))->toBeInstanceOf(Generator::class)
        ->and(invade($generator)->config->getEyeColors()[0])->toBeInstanceOf(EyeFill::class);

    expect($generator->gradient(10, 20, 30, 40, 50, 60, GradientType::DIAGONAL))->toBeInstanceOf(Generator::class)
        ->and(invade($generator)->config->getGradient())->toBeInstanceOf(Gradient::class);

    expect($generator->mergeString('test'))->toBeInstanceOf(Generator::class)
        ->and(invade($generator)->config->getImageMerge())->toBe('test');

    expect($generator->merge('Support/Fixtures/images/linkxtr.png'))->toBeInstanceOf(Generator::class)
        ->and(invade($generator)->config->getImageMerge())->toBe(file_get_contents(__DIR__.'/../Support/Fixtures/images/linkxtr.png'));
});

test('generate throws exception if file_put_contents fails', function () {
    $generator = new Generator;

    global $mockFilePutContents;
    $mockFilePutContents = true;

    expect(fn () => $generator->generate('fail-test', 'fail-test.svg'))
        ->toThrow(RuntimeException::class, 'Failed to write QR code to file: fail-test.svg');
})->after(function () {
    global $mockFilePutContents;
    $mockFilePutContents = null;
});

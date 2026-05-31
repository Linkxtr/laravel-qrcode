<?php

declare(strict_types=1);

use BaconQrCode\Renderer\RendererStyle\EyeFill;
use BaconQrCode\Renderer\RendererStyle\Gradient;
use Linkxtr\QrCode\Enums\ColorModel;
use Linkxtr\QrCode\Enums\ErrorCorrectionLevel;
use Linkxtr\QrCode\Enums\EyeStyle;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Enums\GradientType;
use Linkxtr\QrCode\Enums\Style;
use Linkxtr\QrCode\Exceptions\CannotWriteFileException;
use Linkxtr\QrCode\Generator;
use Linkxtr\QrCode\Support\QrCodeResult;

covers(Generator::class);

it('passes array config from constructor to the underlying DTO', function (): void {
    $generator = new Generator(['size' => 350, 'margin' => 2]);
    $config = invade($generator)->config;

    expect($config->getSize())->toBe(350)
        ->and($config->getMargin())->toBe(2);
});

it('can register and call custom macros that return a string payload', function (): void {
    Generator::macro('myMacro', fn (string $data): string => 'dummy:'.$data);

    $generator = new Generator([]);

    expect($generator->myMacro('hello-world'))->toBeInstanceOf(QrCodeResult::class);
})->after(function (): void {
    Generator::flushMacros();
});

it('can register and call custom macros that return pre-styled generation', function (): void {
    Generator::macro('myStyledMacro', fn (string $data) => $this->size(500)->generate($data));

    $generator = new Generator([]);

    expect($generator->myStyledMacro('hello-world'))->toBeInstanceOf(QrCodeResult::class);
})->after(function (): void {
    Generator::flushMacros();
});

test('macro returning QrCodeResult is returned directly without regeneration', function (): void {
    $expectedHtml = new QrCodeResult('<svg id="exact-macro-match"></svg>', Format::SVG);

    Generator::macro('returnsHtml', fn (): QrCodeResult => $expectedHtml);

    $generator = new Generator;
    $result = $generator->returnsHtml();

    expect($result)->toBe($expectedHtml)
        ->and($result->toHtml())->toBe('<svg id="exact-macro-match"></svg>');
})->after(function (): void {
    Generator::flushMacros();
});

test('macro returning a Stringable object is successfully generated', function (): void {
    Generator::macro('returnsStringable', fn (): Stringable => new class implements Stringable
    {
        public function __toString(): string
        {
            return 'stringable-payload';
        }
    });

    $generator = new Generator;
    $result = $generator->returnsStringable();

    expect($result)->toBeInstanceOf(QrCodeResult::class)
        ->and($result->toHtml())->toContain('<svg');
})->after(function (): void {
    Generator::flushMacros();
});

it('throws an exception for unsupported type returns from macro', function (): void {
    Generator::macro('returnArray', fn (): array => ['an' => 'array']);

    $generator = new Generator([]);

    $generator->returnArray();
})->throws(UnexpectedValueException::class, 'Macro "returnArray" must return a string, Stringable, or QrCodeResult. array returned.')
    ->after(function (): void {
        Generator::flushMacros();
    });

it('still delegates to data types if macro is not registered', function (): void {
    $generator = new Generator([]);

    $qrCodeResult = $generator->Email('test@example.com');

    expect($qrCodeResult)->toBeInstanceOf(QrCodeResult::class);
    expect((string) $qrCodeResult)->toContain('<svg');
});

test('fluent configuration methods return a cloned instance with updated config', function (): void {
    $generator = new Generator;

    $sized = $generator->size(500);
    expect($sized)->toBeInstanceOf(Generator::class)->not->toBe($generator)
        ->and(invade($sized)->config->getSize())->toBe(500);

    $margined = $generator->margin(10);
    expect($margined)->toBeInstanceOf(Generator::class)->not->toBe($generator)
        ->and(invade($margined)->config->getMargin())->toBe(10);

    $formatted = $generator->format('png');
    expect($formatted)->toBeInstanceOf(Generator::class)->not->toBe($generator)
        ->and(invade($formatted)->config->getFormat())->toBe(Format::PNG);

    $errored = $generator->errorCorrection('H');
    expect($errored)->toBeInstanceOf(Generator::class)->not->toBe($generator)
        ->and(invade($errored)->config->getErrorCorrectionLevel())->toBe(ErrorCorrectionLevel::H);

    $encoded = $generator->encoding('ISO-8859-1');
    expect($encoded)->toBeInstanceOf(Generator::class)->not->toBe($generator)
        ->and(invade($encoded)->config->getEncoding())->toBe('ISO-8859-1');

    $styled = $generator->style('dot', 0.8);
    expect($styled)->toBeInstanceOf(Generator::class)->not->toBe($generator)
        ->and(invade($styled)->config->getStyle())->toBe(Style::DOT)
        ->and(invade($styled)->config->getStyleSize())->toBe(0.8);

    $eyed = $generator->eye('circle');
    expect($eyed)->toBeInstanceOf(Generator::class)->not->toBe($generator)
        ->and(invade($eyed)->config->getEyeStyle())->toBe(EyeStyle::CIRCLE);

    $internalEyed = $generator->internalEye('square');
    expect($internalEyed)->toBeInstanceOf(Generator::class)->not->toBe($generator)
        ->and(invade($internalEyed)->config->getInternalEyeStyle())->toBe(EyeStyle::SQUARE);

    $colored = $generator->color(10, 20, 30);
    expect($colored)->toBeInstanceOf(Generator::class)->not->toBe($generator)
        ->and(invade($colored)->config->getColorValue()->red)->toBe(10);

    $bgColored = $generator->backgroundColor(10, 20, 30);
    expect($bgColored)->toBeInstanceOf(Generator::class)->not->toBe($generator)
        ->and(invade($bgColored)->config->getBackgroundColorValue()->red)->toBe(10);

    $cmyk = $generator->cmyk();
    expect($cmyk)->toBeInstanceOf(Generator::class)->not->toBe($generator)
        ->and(invade($cmyk)->config->getColorModel())->toBe(ColorModel::CMYK);

    $rgb = $cmyk->rgb();
    expect($rgb)->toBeInstanceOf(Generator::class)->not->toBe($cmyk)
        ->and(invade($rgb)->config->getColorModel())->toBe(ColorModel::RGB);

    $gray = $generator->gray(50);
    expect($gray)->toBeInstanceOf(Generator::class)->not->toBe($generator)
        ->and(invade($gray)->config->getColorModel())->toBe(ColorModel::GRAY);

    $eyeColor0 = $generator->eyeColor(0, [20, 50, 70]);
    expect($eyeColor0)->toBeInstanceOf(Generator::class)->not->toBe($generator)
        ->and(invade($eyeColor0)->config->getEyeColors()[0])->toBeInstanceOf(EyeFill::class);

    $eyeColor1 = $generator->eyeColor(1, [10, 20, 30], [40, 50, 60]);
    expect($eyeColor1)->toBeInstanceOf(Generator::class)->not->toBe($generator)
        ->and(invade($eyeColor1)->config->getEyeColors()[1])->toBeInstanceOf(EyeFill::class);

    $eyeColor2 = $generator->eyeColor(2, '#1a2b3c', 'fff');
    expect($eyeColor2)->toBeInstanceOf(Generator::class)->not->toBe($generator)
        ->and(invade($eyeColor2)->config->getEyeColors()[2])->toBeInstanceOf(EyeFill::class);

    $gradient = $generator->gradient([10, 20, 30], [40, 50, 60], GradientType::DIAGONAL);
    expect($gradient)->toBeInstanceOf(Generator::class)->not->toBe($generator)
        ->and(invade($gradient)->config->getGradient())->toBeInstanceOf(Gradient::class);

    $mergeString = $generator->mergeString('test', 0.1);
    expect($mergeString)->toBeInstanceOf(Generator::class)->not->toBe($generator)
        ->and(invade($mergeString)->config->getImageMerge())->toBe('test')
        ->and(invade($mergeString)->config->getImagePercentage())->toBe(0.1);

    $expectedContent = file_get_contents(realpath(__DIR__.'/../Support/Fixtures/images/linkxtr.png'));
    $merged = $generator->merge(realpath(__DIR__.'/../Support/Fixtures/images/linkxtr.png'), 0.3);
    expect($merged)->toBeInstanceOf(Generator::class)->not->toBe($generator)
        ->and(invade($merged)->config->getImageMerge())->toBe($expectedContent)
        ->and(invade($merged)->config->getImagePercentage())->toBe(0.3);

    expect(invade($generator)->config->getSize())->toBe(400);
});

test('generate throws exception if file_put_contents fails', function (): void {
    $generator = new Generator;

    global $mockFilePutContents;
    $mockFilePutContents = true;

    expect(fn (): QrCodeResult => $generator->generate('fail-test', 'fail-test.svg'))
        ->toThrow(RuntimeException::class, 'Failed to write QR code to file: fail-test.svg');
});

test('it mathematically rejects arrays and objects inside color configurations', function (): void {
    $generator = new Generator;

    expect(fn (): Generator => $generator->eyeColor(0, [255, ['nested'], 0]))
        ->toThrow(InvalidArgumentException::class, 'RGB array values must be numeric.');

    expect(fn (): Generator => $generator->gradient([255, 0, 0], [0, new stdClass, 0]))
        ->toThrow(InvalidArgumentException::class, 'RGB array values must be numeric.');
});

it('throws an exception when attempting to save to a non-existent directory', function (): void {
    $generator = new Generator;

    $invalidPath = __DIR__.'/this_directory_does_not_exist/qrcode.png';

    expect(fn (): QrCodeResult => $generator->generate('Hello World', $invalidPath))
        ->toThrow(CannotWriteFileException::class);
});

it('can successfully generate and save a qr code to a file', function (): void {
    $generator = new Generator;

    $tempFile = sys_get_temp_dir().'/test_qrcode_'.uniqid().'.svg';

    if (file_exists($tempFile)) {
        unlink($tempFile);
    }

    $generator->format('svg')->generate('Hello World', $tempFile);

    expect(file_exists($tempFile))->toBeTrue()
        ->and(filesize($tempFile))->toBeGreaterThan(0);

    unlink($tempFile);
});

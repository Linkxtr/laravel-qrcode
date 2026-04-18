<?php

declare(strict_types=1);

use BaconQrCode\Renderer\Color\Rgb as BaconRgb;
use BaconQrCode\Renderer\RendererStyle\EyeFill;
use BaconQrCode\Renderer\RendererStyle\Gradient;
use Linkxtr\QrCode\DTOs\Config;
use Linkxtr\QrCode\Enums\ColorModel;
use Linkxtr\QrCode\Enums\ErrorCorrectionLevel;
use Linkxtr\QrCode\Enums\EyeStyle;
use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Enums\GradientType;
use Linkxtr\QrCode\Enums\Style;
use Linkxtr\QrCode\ValueObjects\Colors\Cmyk;
use Linkxtr\QrCode\ValueObjects\Colors\Gray;
use Linkxtr\QrCode\ValueObjects\Colors\Rgb;

require_once __DIR__.'/../../Support/Overrides.php';

covers(Config::class);

test('it initializes with default values', function () {
    $config = new Config;

    expect($config->getSize())->toBe(400)
        ->and($config->getMargin())->toBe(4)
        ->and($config->getFormat())->toBe(Format::SVG)
        ->and($config->getErrorCorrectionLevel())->toBe(ErrorCorrectionLevel::M)
        ->and($config->getEncoding())->toBe('UTF-8')
        ->and($config->getColorModel())->toBe(ColorModel::RGB)
        ->and($config->getStyle())->toBe(Style::SQUARE)
        ->and($config->getStyleSize())->toBe(0.5)
        ->and($config->getEyeStyle())->toBeNull()
        ->and($config->getInternalEyeStyle())->toBeNull()
        ->and($config->getGradient())->toBeNull()
        ->and($config->getImageMerge())->toBe('')
        ->and($config->getImagePercentage())->toBe(0.2)
        ->and($config->getColorValue()->red)->toBe(0)
        ->and($config->getColorValue()->green)->toBe(0)
        ->and($config->getColorValue()->blue)->toBe(0)
        ->and($config->getColorValue()->alpha)->toBe(100)
        ->and($config->getBackgroundColorValue()->red)->toBe(255)
        ->and($config->getBackgroundColorValue()->green)->toBe(255)
        ->and($config->getBackgroundColorValue()->blue)->toBe(255)
        ->and($config->getBackgroundColorValue()->alpha)->toBe(100);
});

test('it seeds configuration from array payload', function () {
    $config = new Config([
        'size' => 1,
        'margin' => 0,
        'format' => 'PNG',
        'error_correction' => 'h',
        'encoding' => 'iso-8859-1',
        'color' => [10, 20, 30, 40],
        'background_color' => [0, 255, 0],
    ]);

    expect($config->getSize())->toBe(1)
        ->and($config->getMargin())->toBe(0)
        ->and($config->getFormat())->toBe(Format::PNG)
        ->and($config->getErrorCorrectionLevel())->toBe(ErrorCorrectionLevel::H)
        ->and($config->getEncoding())->toBe('ISO-8859-1')
        ->and($config->getColorValue()->red)->toBe(10)
        ->and($config->getColorValue()->green)->toBe(20)
        ->and($config->getColorValue()->blue)->toBe(30)
        ->and($config->getColorValue()->alpha)->toBe(40)
        ->and($config->getBackgroundColorValue()->red)->toBe(0)
        ->and($config->getBackgroundColorValue()->green)->toBe(255)
        ->and($config->getBackgroundColorValue()->blue)->toBe(0)
        ->and($config->getBackgroundColorValue()->alpha)->toBe(100);
});

test('it seeds color config with keyed array', function () {
    $config = new Config([
        'color' => ['r' => 255, 'g' => 0, 'b' => 40, 'a' => 50],
        'background_color' => ['r' => 0, 'g' => 200, 'b' => 0, 'a' => 50],
    ]);

    expect($config->getColorValue()->red)->toBe(255)
        ->and($config->getColorValue()->green)->toBe(0)
        ->and($config->getColorValue()->blue)->toBe(40)
        ->and($config->getColorValue()->alpha)->toBe(50)
        ->and($config->getBackgroundColorValue()->red)->toBe(0)
        ->and($config->getBackgroundColorValue()->green)->toBe(200)
        ->and($config->getBackgroundColorValue()->blue)->toBe(0)
        ->and($config->getBackgroundColorValue()->alpha)->toBe(50);
});

test('it falls back to default size when provide invalid size and margin', function () {
    $config = new Config([
        'size' => -1,
        'margin' => -1,
    ]);

    expect($config->getSize())->toBe(400)
        ->and($config->getMargin())->toBe(4);

    $config = new Config([
        'size' => 0,
    ]);

    expect($config->getSize())->toBe(400);
});

test('it sets valid sizes and throws on boundaries', function () {
    $config = new Config;

    $config->setSize(1);
    expect($config->getSize())->toBe(1);

    expect(fn () => $config->setSize(0))->toThrow(InvalidArgumentException::class, 'Size must be greater than 0');
    expect(fn () => $config->setSize(-1))->toThrow(InvalidArgumentException::class, 'Size must be greater than 0');
});

test('it sets valid margins and throws on boundaries', function () {
    $config = new Config;

    $config->setMargin(0);
    expect($config->getMargin())->toBe(0);

    expect(fn () => $config->setMargin(-1))->toThrow(InvalidArgumentException::class, 'Margin cannot be negative');
});

test('it resolves format from string or enum', function () {
    $config = new Config;

    $config->setFormat(Format::EPS);
    expect($config->getFormat())->toBe(Format::EPS);

    $config->setFormat('webp');
    expect($config->getFormat())->toBe(Format::WEBP);

    expect(fn () => $config->setFormat('invalid_format'))->toThrow(InvalidArgumentException::class, '$format must be one of the following values: '.implode(', ', Format::toArray()));
});

test('it sets error correction level from string or enum', function () {
    $config = new Config;

    $config->setErrorCorrectionLevel(ErrorCorrectionLevel::H);
    expect($config->getErrorCorrectionLevel())->toBe(ErrorCorrectionLevel::H);

    $config->setErrorCorrectionLevel('l');
    expect($config->getErrorCorrectionLevel())->toBe(ErrorCorrectionLevel::L);

    expect(fn () => $config->setErrorCorrectionLevel('invalid'))->toThrow(InvalidArgumentException::class, '$level must be one of the following values: '.implode(', ', ErrorCorrectionLevel::toArray()));
});

test('it sets style from string or enum', function () {
    $config = new Config;

    $config->setupStyle(Style::DOT, 0.5);
    expect($config->getStyle())->toBe(Style::DOT);
    expect($config->getStyleSize())->toBe(0.5);

    $config->setupStyle('round', 0.1);
    expect($config->getStyle())->toBe(Style::ROUND);
    expect($config->getStyleSize())->toBe(0.1);

    expect(fn () => $config->setupStyle('invalid', 0.1))->toThrow(InvalidArgumentException::class, '$style must be one of the following values: '.implode(', ', Style::toArray()));
});

test('it sets style size and validates percentages', function () {
    $config = new Config;

    $config->setupStyle(Style::DOT, 0.1);
    $config->setupStyle(Style::DOT, 1.0);
    expect($config->getStyle())->toBe(Style::DOT);
    expect($config->getStyleSize())->toBe(1.0);
    expect(fn () => $config->setupStyle(Style::DOT, 0.0))->toThrow(InvalidArgumentException::class, 'Style size must be between 0 and 1');
    expect(fn () => $config->setupStyle(Style::DOT, 1.1))->toThrow(InvalidArgumentException::class, 'Style size must be between 0 and 1');
});

test('it sets eye style from string or enum', function () {
    $config = new Config;

    $config->setEyeStyle(EyeStyle::SQUARE);
    expect($config->getEyeStyle())->toBe(EyeStyle::SQUARE);

    $config->setEyeStyle('circle');
    expect($config->getEyeStyle())->toBe(EyeStyle::CIRCLE);

    expect(fn () => $config->setEyeStyle('invalid'))->toThrow(InvalidArgumentException::class, '$style must be one of the following values: '.implode(', ', EyeStyle::toArray()));
});

test('it sets internal eye style from string or enum', function () {
    $config = new Config;

    $config->setInternalEyeStyle(EyeStyle::SQUARE);
    expect($config->getInternalEyeStyle())->toBe(EyeStyle::SQUARE);

    $config->setInternalEyeStyle('circle');
    expect($config->getInternalEyeStyle())->toBe(EyeStyle::CIRCLE);

    expect(fn () => $config->setInternalEyeStyle('invalid'))->toThrow(InvalidArgumentException::class, '$style must be one of the following values: '.implode(', ', EyeStyle::toArray()));
});

test('it sets encoding', function () {
    $config = new Config;

    $config->setEncoding('ISO-8859-1');
    expect($config->getEncoding())->toBe('ISO-8859-1');

    $config->setEncoding('utf-8');
    expect($config->getEncoding())->toBe('UTF-8');
});

test('it validates RGB boundaries across all color methods', function () {
    $config = new Config;

    $config->setupColor(255, 255, 255);
    $config->setupBackgroundColor(0, 0, 0);

    expect($config->getColorValue()->red)->toBe(255)
        ->and($config->getColorValue()->green)->toBe(255)
        ->and($config->getColorValue()->blue)->toBe(255)
        ->and($config->getColorValue()->alpha)->toBe(100)
        ->and($config->getBackgroundColorValue()->red)->toBe(0)
        ->and($config->getBackgroundColorValue()->green)->toBe(0)
        ->and($config->getBackgroundColorValue()->blue)->toBe(0)
        ->and($config->getBackgroundColorValue()->alpha)->toBe(100);

    expect(fn () => $config->setupColor(-1, 0, 0))->toThrow(InvalidArgumentException::class, 'Red must be between 0 and 255.');
    expect(fn () => $config->setupColor(256, 0, 0))->toThrow(InvalidArgumentException::class, 'Red must be between 0 and 255.');
});

test('it falls back to default color if any of the values is not an integer', function () {
    $config = new Config([
        'size' => 250,
        'margin' => 2,
        'format' => 'png',
        'error_correction' => 'H',
        'encoding' => 'ISO-8859-1',
        'color' => ['invalid', 'string', [], null],
        'background_color' => ['invalid', 'string', [], 2.5],
    ]);

    expect($config->getColorValue()->red)->toBe(0)
        ->and($config->getColorValue()->green)->toBe(0)
        ->and($config->getColorValue()->blue)->toBe(0)
        ->and($config->getColorValue()->alpha)->toBe(100)
        ->and($config->getBackgroundColorValue()->red)->toBe(255)
        ->and($config->getBackgroundColorValue()->green)->toBe(255)
        ->and($config->getBackgroundColorValue()->blue)->toBe(255)
        ->and($config->getBackgroundColorValue()->alpha)->toBe(100);
});

test('it handles grayscale configuration', function () {
    $config = new Config;
    $config->setGrayscale(0, 0);

    expect($config->getColorModel())->toBe(ColorModel::GRAY)
        ->and($config->getColorValue()->gray)->toBe(0)
        ->and($config->getBackgroundColorValue()->gray)->toBe(0);

    $config->setGrayscale(100, 100);

    expect($config->getColorModel())->toBe(ColorModel::GRAY)
        ->and($config->getColorValue()->gray)->toBe(100)
        ->and($config->getBackgroundColorValue()->gray)->toBe(100);

    $config->setGrayscale(50);

    expect($config->getColorModel())->toBe(ColorModel::GRAY)
        ->and($config->getColorValue()->gray)->toBe(50)
        ->and($config->getBackgroundColorValue()->gray)->toBe(100);

    expect(fn () => $config->setGrayscale(-1))->toThrow(InvalidArgumentException::class, 'Gray value must be between 0 and 100');
    expect(fn () => $config->setGrayscale(101))->toThrow(InvalidArgumentException::class, 'Gray value must be between 0 and 100');
    expect(fn () => $config->setGrayscale(50, 101))->toThrow(InvalidArgumentException::class, 'Background gray value must be between 0 and 100');
    expect(fn () => $config->setGrayscale(50, -1))->toThrow(InvalidArgumentException::class, 'Background gray value must be between 0 and 100');
});

test('it handles eye color configuration and validates eye numbers and colors', function () {
    $config = new Config;

    $config->setupEyeColor(0, 255, 0, 0, 0, 255, 0);
    $config->setupEyeColor(2, 0, 0, 255);

    expect($config->getEyeColors()[0])->toBeInstanceOf(EyeFill::class)
        ->and($config->getEyeColors()[0]->getExternalColor())->toBeInstanceOf(BaconRgb::class)
        ->and($config->getEyeColors()[0]->getExternalColor()->getRed())->toBe(255)
        ->and($config->getEyeColors()[0]->getExternalColor()->getGreen())->toBe(0)
        ->and($config->getEyeColors()[0]->getExternalColor()->getBlue())->toBe(0)
        ->and($config->getEyeColors()[0]->getInternalColor())->toBeInstanceOf(BaconRgb::class)
        ->and($config->getEyeColors()[0]->getInternalColor()->getRed())->toBe(0)
        ->and($config->getEyeColors()[0]->getInternalColor()->getGreen())->toBe(255)
        ->and($config->getEyeColors()[0]->getInternalColor()->getBlue())->toBe(0)
        ->and($config->getEyeColors()[2])->toBeInstanceOf(EyeFill::class)
        ->and($config->getEyeColors()[2]->getExternalColor())->toBeInstanceOf(BaconRgb::class)
        ->and($config->getEyeColors()[2]->getExternalColor()->getRed())->toBe(0)
        ->and($config->getEyeColors()[2]->getExternalColor()->getGreen())->toBe(0)
        ->and($config->getEyeColors()[2]->getExternalColor()->getBlue())->toBe(255)
        ->and($config->getEyeColors()[2]->getInternalColor())->toBeInstanceOf(BaconRgb::class)
        ->and($config->getEyeColors()[2]->getInternalColor()->getRed())->toBe(0)
        ->and($config->getEyeColors()[2]->getInternalColor()->getGreen())->toBe(0)
        ->and($config->getEyeColors()[2]->getInternalColor()->getBlue())->toBe(0);

    expect(fn () => $config->setupEyeColor(-1, 0, 0, 0))->toThrow(InvalidArgumentException::class, 'Eye number must be 0, 1, or 2');
    expect(fn () => $config->setupEyeColor(3, 0, 0, 0))->toThrow(InvalidArgumentException::class, 'Eye number must be 0, 1, or 2');
    expect(fn () => $config->setupEyeColor(0, -1, 0, 0))->toThrow(InvalidArgumentException::class, 'Red must be between 0 and 255.');
    expect(fn () => $config->setupEyeColor(0, 256, 0, 0))->toThrow(InvalidArgumentException::class, 'Red must be between 0 and 255.');
    expect(fn () => $config->setupEyeColor(0, 0, -1, 0))->toThrow(InvalidArgumentException::class, 'Green must be between 0 and 255.');
    expect(fn () => $config->setupEyeColor(0, 0, 256, 0))->toThrow(InvalidArgumentException::class, 'Green must be between 0 and 255.');
    expect(fn () => $config->setupEyeColor(0, 0, 0, -1))->toThrow(InvalidArgumentException::class, 'Blue must be between 0 and 255.');
    expect(fn () => $config->setupEyeColor(0, 0, 0, 256))->toThrow(InvalidArgumentException::class, 'Blue must be between 0 and 255.');
});

test('it configures gradients from strings and enums', function () {
    $config = new Config;

    $config->setupGradient(255, 0, 0, 0, 0, 255, 'Diagonal');
    expect($config->getGradient())->toBeInstanceOf(Gradient::class);

    $config->setupGradient(255, 0, 0, 0, 0, 255, GradientType::RADIAL);
    expect($config->getGradient())->toBeInstanceOf(Gradient::class);

    expect(fn () => $config->setupGradient(0, 0, 0, 0, 0, 0, 'invalid'))->toThrow(InvalidArgumentException::class, '$type must be one of the following values: '.implode(', ', GradientType::toArray()));
});

test('it throws exception when gradient colors are invalid', function () {
    $config = new Config;

    expect(fn () => $config->setupGradient(-1, 0, 0, 0, 0, 0, 'diagonal'))->toThrow(InvalidArgumentException::class, 'Red must be between 0 and 255.');
    expect(fn () => $config->setupGradient(256, 0, 0, 0, 0, 0, 'diagonal'))->toThrow(InvalidArgumentException::class, 'Red must be between 0 and 255.');
    expect(fn () => $config->setupGradient(0, -1, 0, 0, 0, 0, 'diagonal'))->toThrow(InvalidArgumentException::class, 'Green must be between 0 and 255.');
    expect(fn () => $config->setupGradient(0, 256, 0, 0, 0, 0, 'diagonal'))->toThrow(InvalidArgumentException::class, 'Green must be between 0 and 255.');
    expect(fn () => $config->setupGradient(0, 0, -1, 0, 0, 0, 'diagonal'))->toThrow(InvalidArgumentException::class, 'Blue must be between 0 and 255.');
    expect(fn () => $config->setupGradient(0, 0, 256, 0, 0, 0, 'diagonal'))->toThrow(InvalidArgumentException::class, 'Blue must be between 0 and 255.');
    expect(fn () => $config->setupGradient(0, 0, 0, -1, 0, 0, 'diagonal'))->toThrow(InvalidArgumentException::class, 'Red must be between 0 and 255.');
    expect(fn () => $config->setupGradient(0, 0, 0, 256, 0, 0, 'diagonal'))->toThrow(InvalidArgumentException::class, 'Red must be between 0 and 255.');
    expect(fn () => $config->setupGradient(0, 0, 0, 0, -1, 0, 'diagonal'))->toThrow(InvalidArgumentException::class, 'Green must be between 0 and 255.');
    expect(fn () => $config->setupGradient(0, 0, 0, 0, 256, 0, 'diagonal'))->toThrow(InvalidArgumentException::class, 'Green must be between 0 and 255.');
    expect(fn () => $config->setupGradient(0, 0, 0, 0, 0, -1, 'diagonal'))->toThrow(InvalidArgumentException::class, 'Blue must be between 0 and 255.');
    expect(fn () => $config->setupGradient(0, 0, 0, 0, 0, 256, 'diagonal'))->toThrow(InvalidArgumentException::class, 'Blue must be between 0 and 255.');
});

test('it sets up image merge from absolute file path', function () {
    $config = new Config;

    $config->setupMergePath(__DIR__.'/../../Support/Fixtures/images/linkxtr.png', 0.1, true);
    expect($config->getImageMerge())->toBe(file_get_contents(__DIR__.'/../../Support/Fixtures/images/linkxtr.png'))
        ->and($config->getImagePercentage())->toBe(0.1);

    expect(fn () => $config->setupMergePath(__DIR__.'/../../Support/Fixtures/images/linkxtr.png', 0.0, true))->toThrow(InvalidArgumentException::class, 'Image merge percentage must be between 0 and 1 (exclusive)');
    expect(fn () => $config->setupMergePath(__DIR__.'/../../Support/Fixtures/images/linkxtr.png', 1.0, true))->toThrow(InvalidArgumentException::class, 'Image merge percentage must be between 0 and 1 (exclusive)');
});

test('it sets up image merge from relative file path', function () {
    $config = new Config;

    $config->setupMergePath('Support/Fixtures/images/linkxtr.png', 0.1, false);
    expect($config->getImageMerge())->toBe(file_get_contents(__DIR__.'/../../Support/Fixtures/images/linkxtr.png'))
        ->and($config->getImagePercentage())->toBe(0.1);

    expect(fn () => $config->setupMergePath('/Support/Fixtures/images/linkxtr.png', 0.0))->toThrow(InvalidArgumentException::class, 'Image merge percentage must be between 0 and 1 (exclusive)');
    expect(fn () => $config->setupMergePath('Support/Fixtures/images/linkxtr.png', 1.0))->toThrow(InvalidArgumentException::class, 'Image merge percentage must be between 0 and 1 (exclusive)');
});

test('it throws exception when path does not exist', function () {
    $config = new Config;

    expect(fn () => $config->setupMergePath('non_existent_path', 0.1, false))->toThrow(InvalidArgumentException::class, 'Image file does not exist or is not readable: '.\Linkxtr\QrCode\DTOs\base_path('non_existent_path'));
    expect(fn () => $config->setupMergePath('/non_existent_path', 0.1, true))->toThrow(InvalidArgumentException::class, 'Image file does not exist or is not readable: /non_existent_path');
});

test('it throws exception when path is a directory', function () {
    $config = new Config;

    expect(fn () => $config->setupMergePath(__DIR__, 0.1, true))
        ->toThrow(
            InvalidArgumentException::class,
            'Image file does not exist or is not readable: '.__DIR__
        );
});

test('it throws exception when path is not readable', function () {
    $config = new Config;
    try {
        $filePath = __DIR__.'/../../Support/Fixtures/restricted.png';
        file_put_contents($filePath, 'restricted');
        chmod($filePath, 000);

        expect(fn () => $config->setupMergePath($filePath, 0.1, false))->toThrow(InvalidArgumentException::class, 'Image file does not exist or is not readable: '.\Linkxtr\QrCode\DTOs\base_path($filePath));
    } finally {
        chmod($filePath, 0777);
        unlink($filePath);
    }
});

test('it throws exception when file_get_contents returns false', function () {
    $config = new Config;

    global $mockFileGetContents;
    $mockFileGetContents = false;

    $path = __DIR__.'/../../Support/Fixtures/images/linkxtr.png';

    expect(fn () => $config->setupMergePath($path, 0.1, true))->toThrow(InvalidArgumentException::class, 'Failed to read image file: '.$path);

    $mockFileGetContents = null;
});

test('it sets up string image merges and validates percentages', function () {
    $config = new Config;

    $config->setupMergeString('image_data', 0.1);
    $config->setupMergeString('image_data', 0.99);

    expect($config->getImageMerge())->toBe('image_data')
        ->and($config->getImagePercentage())->toBe(0.99);

    expect(fn () => $config->setupMergeString('data', 0.0))->toThrow(InvalidArgumentException::class, 'Image merge percentage must be between 0 and 1 (exclusive)');
    expect(fn () => $config->setupMergeString('data', 1.0))->toThrow(InvalidArgumentException::class, 'Image merge percentage must be between 0 and 1 (exclusive)');
});

test('it sets up color model and converts existing colors', function () {
    $config = new Config;

    $config->setupColor(255, 0, 0);
    expect($config->getColorValue())->toBeInstanceOf(Rgb::class)
        ->and($config->getColorValue()->red)->toBe(255)
        ->and($config->getColorValue()->green)->toBe(0)
        ->and($config->getColorValue()->blue)->toBe(0);

    $config->setColorModel(ColorModel::CMYK);
    expect($config->getColorValue())->toBeInstanceOf(Cmyk::class)
        ->and($config->getColorValue()->cyan)->toBe(0)
        ->and($config->getColorValue()->magenta)->toBe(100)
        ->and($config->getColorValue()->yellow)->toBe(100)
        ->and($config->getColorValue()->black)->toBe(0);

    $config->setupColor(0, 0, 0, 0);
    $config->setupBackgroundColor(0, 0, 0, 100);
    expect($config->getColorValue())->toBeInstanceOf(Cmyk::class)
        ->and($config->getColorValue()->cyan)->toBe(0)
        ->and($config->getColorValue()->magenta)->toBe(0)
        ->and($config->getColorValue()->yellow)->toBe(0)
        ->and($config->getColorValue()->black)->toBe(0)
        ->and($config->getBackgroundColorValue())->toBeInstanceOf(Cmyk::class)
        ->and($config->getBackgroundColorValue()->cyan)->toBe(0)
        ->and($config->getBackgroundColorValue()->magenta)->toBe(0)
        ->and($config->getBackgroundColorValue()->yellow)->toBe(0)
        ->and($config->getBackgroundColorValue()->black)->toBe(100);

    $config->setColorModel(ColorModel::GRAY);
    expect($config->getColorValue())->toBeInstanceOf(Gray::class)
        ->and($config->getColorValue()->gray)->toBe(0)
        ->and($config->getBackgroundColorValue())->toBeInstanceOf(Gray::class)
        ->and($config->getBackgroundColorValue()->gray)->toBe(100);
});

test('it handles c4 parameter fallback and override in setupColor across all color models', function () {
    $config = new Config;

    $config->setColorModel(ColorModel::RGB);

    $config->setupColor(10, 20, 30);
    expect($config->getColorValue()->alpha)->toBe(100);

    $config->setupColor(10, 20, 30, 50);
    expect($config->getColorValue()->alpha)->toBe(50);

    $config->setColorModel(ColorModel::CMYK);

    $config->setupColor(10, 20, 30);
    expect($config->getColorValue()->black)->toBe(100);

    $config->setupColor(10, 20, 30, 50);
    expect($config->getColorValue()->black)->toBe(50);

    $config->setColorModel(ColorModel::GRAY);
    $config->setupColor(10, 0, 0);
    expect($config->getColorValue()->alpha)->toBe(100);

    $config->setupColor(10, 0, 0, 50);
    expect($config->getColorValue()->alpha)->toBe(50);
});

test('it handles c4 parameter fallback and override in setupBackgroundColor across all color models', function () {
    $config = new Config;

    $config->setColorModel(ColorModel::RGB);
    
    $config->setupBackgroundColor(10, 20, 30);
    expect($config->getBackgroundColorValue()->alpha)->toBe(100);
    
    $config->setupBackgroundColor(10, 20, 30, 50);
    expect($config->getBackgroundColorValue()->alpha)->toBe(50);

    $config->setColorModel(ColorModel::CMYK);
    
    $config->setupBackgroundColor(10, 20, 30);
    expect($config->getBackgroundColorValue()->black)->toBe(100);
    
    $config->setupBackgroundColor(10, 20, 30, 50);
    expect($config->getBackgroundColorValue()->black)->toBe(50);

    $config->setColorModel(ColorModel::GRAY);
    
    $config->setupBackgroundColor(10, 0, 0);
    expect($config->getBackgroundColorValue()->alpha)->toBe(100);
    
    $config->setupBackgroundColor(10, 0, 0, 50);
    expect($config->getBackgroundColorValue()->alpha)->toBe(50);
});
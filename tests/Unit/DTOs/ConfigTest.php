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
use Linkxtr\QrCode\Exceptions\InvalidConfigurationException;
use Linkxtr\QrCode\ValueObjects\Colors\Cmyk;
use Linkxtr\QrCode\ValueObjects\Colors\Gray;
use Linkxtr\QrCode\ValueObjects\Colors\Rgb;

require_once __DIR__.'/../../Support/Overrides.php';

covers(Config::class);

test('it initializes with default values', function (): void {
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

test('it seeds configuration from array payload', function (): void {
    $config = Config::fromArray([
        'size' => 1,
        'margin' => 0,
        'format' => 'PNG',
        'error_correction' => 'h',
        'encoding' => 'iso-8859-1',
        'color' => '10, 20, 30, 40',
        'background_color' => '0, 255, 0',
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

test('it falls back to default size when provide invalid size and margin', function (): void {
    $config = Config::fromArray([
        'size' => -1,
        'margin' => -1,
    ]);

    expect($config->getSize())->toBe(400)
        ->and($config->getMargin())->toBe(4);

    $config = Config::fromArray([
        'size' => 0,
    ]);

    expect($config->getSize())->toBe(400);
});

test('it sets valid sizes and throws on boundaries', function (): void {
    $config = new Config;

    $config->setSize(1);

    expect($config->getSize())->toBe(1);

    expect(fn () => $config->setSize(0))->toThrow(InvalidConfigurationException::class, 'Size must be greater than 0');
    expect(fn () => $config->setSize(-1))->toThrow(InvalidConfigurationException::class, 'Size must be greater than 0');
});

test('it sets valid margins and throws on boundaries', function (): void {
    $config = new Config;

    $config->setMargin(0);

    expect($config->getMargin())->toBe(0);

    expect(fn () => $config->setMargin(-1))->toThrow(InvalidConfigurationException::class, 'Margin cannot be negative. Got: -1');
});

test('it resolves format from string or enum', function (): void {
    $config = new Config;

    $config->setFormat(Format::EPS);

    expect($config->getFormat())->toBe(Format::EPS);

    $config->setFormat('webp');
    expect($config->getFormat())->toBe(Format::WEBP);

    $config->setFormat('SVG');
    expect($config->getFormat())->toBe(Format::SVG);

    expect(fn () => $config->setFormat('invalid_format'))->toThrow(InvalidConfigurationException::class, 'Format must be one of the following values: '.implode(', ', Format::toArray()).'. Got: invalid_format');
});

test('it sets error correction level from string or enum', function (): void {
    $config = new Config;

    $config->setErrorCorrectionLevel(ErrorCorrectionLevel::H);

    expect($config->getErrorCorrectionLevel())->toBe(ErrorCorrectionLevel::H);

    $config->setErrorCorrectionLevel('l');
    expect($config->getErrorCorrectionLevel())->toBe(ErrorCorrectionLevel::L);

    expect(fn () => $config->setErrorCorrectionLevel('invalid'))->toThrow(InvalidConfigurationException::class, 'Error correction level must be one of the following values: '.implode(', ', ErrorCorrectionLevel::toArray()));
});

test('it sets style from string or enum', function (): void {
    $config = new Config;

    $config->setupStyle(Style::DOT, 0.5);

    expect($config->getStyle())->toBe(Style::DOT);
    expect($config->getStyleSize())->toBe(0.5);

    $config->setupStyle('Round', 0.1);
    expect($config->getStyle())->toBe(Style::ROUND);
    expect($config->getStyleSize())->toBe(0.1);

    expect(fn () => $config->setupStyle('invalid', 0.1))->toThrow(InvalidConfigurationException::class, 'Style must be one of the following values: '.implode(', ', Style::toArray()).'. Got: invalid');
});

test('it sets style size and validates percentages', function (): void {
    $config = new Config;

    $config->setupStyle(Style::DOT, 0.1);
    $config->setupStyle(Style::DOT, 1.0);

    expect($config->getStyle())->toBe(Style::DOT);
    expect($config->getStyleSize())->toBe(1.0);
    expect(fn () => $config->setupStyle(Style::DOT, 0.0))->toThrow(InvalidConfigurationException::class, 'Style size must be between 0 and 1');
    expect(fn () => $config->setupStyle(Style::DOT, 1.1))->toThrow(InvalidConfigurationException::class, 'Style size must be between 0 and 1');
});

test('it sets eye style from string or enum', function (): void {
    $config = new Config;

    $config->setEyeStyle(EyeStyle::SQUARE);

    expect($config->getEyeStyle())->toBe(EyeStyle::SQUARE);

    $config->setEyeStyle('circle');
    expect($config->getEyeStyle())->toBe(EyeStyle::CIRCLE);

    expect(fn () => $config->setEyeStyle('invalid'))->toThrow(InvalidConfigurationException::class, 'Eye style must be one of the following values: '.implode(', ', EyeStyle::toArray()).'. Got: invalid');
});

test('it sets internal eye style from string or enum', function (): void {
    $config = new Config;

    $config->setInternalEyeStyle(EyeStyle::SQUARE);

    expect($config->getInternalEyeStyle())->toBe(EyeStyle::SQUARE);

    $config->setInternalEyeStyle('circle');
    expect($config->getInternalEyeStyle())->toBe(EyeStyle::CIRCLE);

    expect(fn () => $config->setInternalEyeStyle('invalid'))->toThrow(InvalidConfigurationException::class, 'Eye style must be one of the following values: '.implode(', ', EyeStyle::toArray()).'. Got: invalid');
});

test('it sets encoding', function (): void {
    $config = new Config;

    $config->setEncoding('ISO-8859-1');

    expect($config->getEncoding())->toBe('ISO-8859-1');

    $config->setEncoding('utf-8');
    expect($config->getEncoding())->toBe('UTF-8');
});

test('it validates RGB boundaries across all color methods', function (): void {
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

    expect(fn () => $config->setupColor(-1, 0, 0))->toThrow(InvalidConfigurationException::class, 'Red must be between 0 and 255.');
    expect(fn () => $config->setupColor(256, 0, 0))->toThrow(InvalidConfigurationException::class, 'Red must be between 0 and 255.');
});

test('it handles grayscale configuration', function (): void {
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

    expect(fn () => $config->setGrayscale(-1))->toThrow(InvalidConfigurationException::class, 'Gray value must be between 0 and 100. Got: -1');
    expect(fn () => $config->setGrayscale(101))->toThrow(InvalidConfigurationException::class, 'Gray value must be between 0 and 100. Got: 101');
    expect(fn () => $config->setGrayscale(50, 101))->toThrow(InvalidConfigurationException::class, 'Gray value must be between 0 and 100. Got: 101');
    expect(fn () => $config->setGrayscale(50, -1))->toThrow(InvalidConfigurationException::class, 'Gray value must be between 0 and 100. Got: -1');
});

test('it handles eye color configuration and validates eye numbers and colors', function (): void {
    $config = new Config;

    $config->setupEyeColor(0, Rgb::fromArray([255, 0, 0]), Rgb::fromArray([0, 255, 0]));
    $config->setupEyeColor(2, Rgb::fromArray([0, 0, 255]));

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

    expect(fn () => $config->setupEyeColor(-1, Rgb::fromArray([0, 0, 0]), Rgb::fromArray([0, 0, 0])))->toThrow(InvalidConfigurationException::class, 'Eye number must be 0, 1, or 2');
    expect(fn () => $config->setupEyeColor(3, Rgb::fromArray([0, 0, 0]), Rgb::fromArray([0, 0, 0])))->toThrow(InvalidConfigurationException::class, 'Eye number must be 0, 1, or 2');
    expect(fn () => $config->setupEyeColor(0, Rgb::fromArray([-1, 0, 0]), Rgb::fromArray([0, 0, 0])))->toThrow(InvalidConfigurationException::class, 'Red must be between 0 and 255.');
    expect(fn () => $config->setupEyeColor(0, Rgb::fromArray([256, 0, 0]), Rgb::fromArray([0, 0, 0])))->toThrow(InvalidConfigurationException::class, 'Red must be between 0 and 255.');
    expect(fn () => $config->setupEyeColor(0, Rgb::fromArray([0, -1, 0]), Rgb::fromArray([0, 0, 0])))->toThrow(InvalidConfigurationException::class, 'Green must be between 0 and 255.');
    expect(fn () => $config->setupEyeColor(0, Rgb::fromArray([0, 256, 0]), Rgb::fromArray([0, 0, 0])))->toThrow(InvalidConfigurationException::class, 'Green must be between 0 and 255.');
    expect(fn () => $config->setupEyeColor(0, Rgb::fromArray([0, 0, -1]), Rgb::fromArray([0, 0, 0])))->toThrow(InvalidConfigurationException::class, 'Blue must be between 0 and 255.');
    expect(fn () => $config->setupEyeColor(0, Rgb::fromArray([0, 0, 256]), Rgb::fromArray([0, 0, 0])))->toThrow(InvalidConfigurationException::class, 'Blue must be between 0 and 255.');
});

test('it configures gradients from strings and enums', function (): void {
    $config = new Config;

    $config->setupGradient(Rgb::fromArray([255, 0, 0]), Rgb::fromArray([0, 0, 255]), 'Diagonal');

    expect($config->getGradient())->toBeInstanceOf(Gradient::class);

    $config->setupGradient(Rgb::fromArray([255, 0, 0]), Rgb::fromArray([0, 0, 255]), GradientType::RADIAL);
    expect($config->getGradient())->toBeInstanceOf(Gradient::class);

    expect(fn () => $config->setupGradient(Rgb::fromArray([0, 0, 0]), Rgb::fromArray([0, 0, 0]), 'invalid'))->toThrow(InvalidConfigurationException::class, 'Gradient type must be one of the following values: '.implode(', ', GradientType::toArray()).'. Got: invalid');
});

test('it throws exception when gradient colors are invalid', function (): void {
    $config = new Config;

    expect(fn () => $config->setupGradient(Rgb::fromArray([-1, 0, 0]), Rgb::fromArray([0, 0, 0]), 'diagonal'))->toThrow(InvalidConfigurationException::class, 'Red must be between 0 and 255.');
    expect(fn () => $config->setupGradient(Rgb::fromArray([256, 0, 0]), Rgb::fromArray([0, 0, 0]), 'diagonal'))->toThrow(InvalidConfigurationException::class, 'Red must be between 0 and 255.');
    expect(fn () => $config->setupGradient(Rgb::fromArray([0, -1, 0]), Rgb::fromArray([0, 0, 0]), 'diagonal'))->toThrow(InvalidConfigurationException::class, 'Green must be between 0 and 255.');
    expect(fn () => $config->setupGradient(Rgb::fromArray([0, 256, 0]), Rgb::fromArray([0, 0, 0]), 'diagonal'))->toThrow(InvalidConfigurationException::class, 'Green must be between 0 and 255.');
    expect(fn () => $config->setupGradient(Rgb::fromArray([0, 0, -1]), Rgb::fromArray([0, 0, 0]), 'diagonal'))->toThrow(InvalidConfigurationException::class, 'Blue must be between 0 and 255.');
    expect(fn () => $config->setupGradient(Rgb::fromArray([0, 0, 256]), Rgb::fromArray([0, 0, 0]), 'diagonal'))->toThrow(InvalidConfigurationException::class, 'Blue must be between 0 and 255.');
    expect(fn () => $config->setupGradient(Rgb::fromArray([0, 0, 0]), Rgb::fromArray([-1, 0, 0]), 'diagonal'))->toThrow(InvalidConfigurationException::class, 'Red must be between 0 and 255.');
    expect(fn () => $config->setupGradient(Rgb::fromArray([0, 0, 0]), Rgb::fromArray([256, 0, 0]), 'diagonal'))->toThrow(InvalidConfigurationException::class, 'Red must be between 0 and 255.');
    expect(fn () => $config->setupGradient(Rgb::fromArray([0, 0, 0]), Rgb::fromArray([0, -1, 0]), 'diagonal'))->toThrow(InvalidConfigurationException::class, 'Green must be between 0 and 255.');
    expect(fn () => $config->setupGradient(Rgb::fromArray([0, 0, 0]), Rgb::fromArray([0, 256, 0]), 'diagonal'))->toThrow(InvalidConfigurationException::class, 'Green must be between 0 and 255.');
    expect(fn () => $config->setupGradient(Rgb::fromArray([0, 0, 0]), Rgb::fromArray([0, 0, -1]), 'diagonal'))->toThrow(InvalidConfigurationException::class, 'Blue must be between 0 and 255.');
    expect(fn () => $config->setupGradient(Rgb::fromArray([0, 0, 0]), Rgb::fromArray([0, 0, 256]), 'diagonal'))->toThrow(InvalidConfigurationException::class, 'Blue must be between 0 and 255.');
});

test('it sets up image merge from absolute file path', function (): void {
    $config = new Config;

    $config->setupMergePath(__DIR__.'/../../Support/Fixtures/images/linkxtr.png');
    $config->setImagePercentage(0.1);

    expect($config->getImageMerge())->toBe(file_get_contents(__DIR__.'/../../Support/Fixtures/images/linkxtr.png'))
        ->and($config->getImagePercentage())->toBe(0.1);

    expect(fn () => $config->setImagePercentage(0.0))->toThrow(InvalidConfigurationException::class, 'Image merge percentage must be between 0 and 1 (exclusive)');
    expect(fn () => $config->setImagePercentage(1.0))->toThrow(InvalidConfigurationException::class, 'Image merge percentage must be between 0 and 1 (exclusive)');
});

test('it sets up image merge from relative file path', function (): void {
    $config = new Config;

    $config->setupMergePath('Support/Fixtures/images/linkxtr.png');
    $config->setImagePercentage(0.1);

    expect($config->getImageMerge())->toBe(file_get_contents(__DIR__.'/../../Support/Fixtures/images/linkxtr.png'))
        ->and($config->getImagePercentage())->toBe(0.1);
});

test('it throws exception when path does not exist', function (): void {
    $config = new Config;

    expect(fn () => $config->setupMergePath('non_existent_path'))->toThrow(InvalidConfigurationException::class, 'Image file does not exist or is not readable: non_existent_path');
    expect(fn () => $config->setupMergePath('/non_existent_path'))->toThrow(InvalidConfigurationException::class, 'Image file does not exist or is not readable: /non_existent_path');
});

test('it throws exception when path is a directory', function (): void {
    $config = new Config;

    expect(fn () => $config->setupMergePath(__DIR__))
        ->toThrow(
            InvalidConfigurationException::class,
            'Image file does not exist or is not readable: '.__DIR__
        );
});

test('it throws exception when path is not readable', function (): void {
    $config = new Config;
    try {
        $filePath = __DIR__.'/../../Support/Fixtures/restricted.png';
        file_put_contents($filePath, 'restricted');
        chmod($filePath, 000);

        $resolvedPath = realpath($filePath);

        expect(fn () => $config->setupMergePath($filePath))->toThrow(InvalidConfigurationException::class, 'Image file does not exist or is not readable: '.$resolvedPath);
    } finally {
        chmod($filePath, 0777);
        unlink($filePath);
    }
});

test('it throws exception when file_get_contents returns false', function (): void {
    $config = new Config;

    global $mockFileGetContents;
    $mockFileGetContents = false;

    $path = __DIR__.'/../../Support/Fixtures/images/linkxtr.png';

    $resolvedPath = realpath($path);

    expect(fn () => $config->setupMergePath($path))->toThrow(InvalidConfigurationException::class, 'Failed to read image file: '.$resolvedPath);
})->after(function (): void {
    global $mockFileGetContents;
    $mockFileGetContents = null;
});

test('it sets up string image merges and validates percentages', function (): void {
    $config = new Config;

    $config->setupMergeString('image_data');
    $config->setImagePercentage(0.99);

    expect($config->getImageMerge())->toBe('image_data')
        ->and($config->getImagePercentage())->toBe(0.99);
});

test('it sets up color model and converts existing colors', function (): void {
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
        ->and($config->getColorValue()->gray)->toBe(100)
        ->and($config->getBackgroundColorValue())->toBeInstanceOf(Gray::class)
        ->and($config->getBackgroundColorValue()->gray)->toBe(0);
});

test('it handles c4 parameter fallback and override in setupColor across all color models', function (): void {
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

test('it handles c4 parameter fallback and override in setupBackgroundColor across all color models', function (): void {
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

test('it mathematically blocks directory traversal attacks on relative merge paths', function (): void {
    $config = new Config;

    $actualBase = realpath(__DIR__.'/../../');
    $parentDir = dirname($actualBase);

    $tempFile = tempnam($parentDir, 'traversal_');
    file_put_contents($tempFile, 'data');

    $originalBasePath = app()->basePath();
    app()->setBasePath($actualBase);

    try {
        $relativePath = '../../'.basename($parentDir).'/'.basename($tempFile);

        expect(fn () => $config->setupMergePath($relativePath))
            ->toThrow(InvalidConfigurationException::class, 'Image file path must be inside the application base path.');
    } finally {
        app()->setBasePath($originalBasePath);
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
    }
});

test('it allows valid absolute paths outside the application root', function (): void {
    $config = new Config;

    $tempFile = tempnam(sys_get_temp_dir(), 'safe_absolute_');
    file_put_contents($tempFile, 'safe data');

    $config->setupMergePath($tempFile);

    expect(invade($config)->imageMerge)->toBe('safe data');

    unlink($tempFile);
});

test('it prevents directory traversal into sibling directories that share the same prefix', function (): void {
    $config = new Config;

    $actualBase = realpath(__DIR__.'/../../');

    $siblingDir = $actualBase.'-secret-sibling';
    if (! is_dir($siblingDir)) {
        mkdir($siblingDir);
    }

    $filePath = $siblingDir.'/restricted.png';
    file_put_contents($filePath, 'secret-data');

    $originalBasePath = app()->basePath();
    app()->setBasePath($actualBase);

    try {
        $siblingFolderName = basename($siblingDir);
        $maliciousRelativePath = '../'.$siblingFolderName.'/restricted.png';

        expect(fn () => $config->setupMergePath($maliciousRelativePath))
            ->toThrow(
                InvalidConfigurationException::class,
                'Image file path must be inside the application base path.'
            );
    } finally {
        // Cleanup
        app()->setBasePath($originalBasePath);
        unlink($filePath);
        rmdir($siblingDir);
    }
});

it('ignores the size configuration if it is not an integer', function (): void {
    $config = Config::fromArray([
        'size' => '300',
    ]);

    $defaultConfig = new Config;

    expect($config->getSize())->toBe($defaultConfig->getSize());
});

<?php

declare(strict_types=1);

use Linkxtr\QrCode\Console\Commands\GenerateQrCodeCommand;
use Linkxtr\QrCode\Contracts\QrCodeExceptionInterface;
use Linkxtr\QrCode\Enums\ErrorCorrectionLevel;
use Linkxtr\QrCode\Exceptions\Concerns\HasHelperMessage;
use Linkxtr\QrCode\Facades\QrCode;

covers(GenerateQrCodeCommand::class);

beforeEach(function (): void {
    $this->fakeGenerator = new class
    {
        public array $calls = [];

        public function __call(string $name, array $arguments): self
        {
            $this->calls[$name] = $arguments;

            return $this;
        }

        public function generate(string $data, ?string $output = null): string
        {
            $this->calls['generate'] = [$data, $output];

            return '<svg>fake-qr-code</svg>';
        }
    };

    QrCode::swap($this->fakeGenerator);
});

test('it can generate a qr code purely through cli arguments and options', function (): void {
    $this->artisan('qr:generate', [
        'data' => 'https://example.com',
        '--output' => 'public/qr.png',
        '--format' => 'png',
        '--size' => '500',
        '--color' => '255,0,0',
        '--backgroundColor' => '0,0,0,50',
        '--margin' => '2',
        '--errorCorrection' => 'H',
    ])->assertSuccessful()
        ->expectsOutputToContain('✨ QR Code successfully generated and saved to: public/qr.png');

    expect($this->fakeGenerator->calls['size'][0])->toBe(500);
    expect($this->fakeGenerator->calls['format'][0])->toBe('png');
    expect($this->fakeGenerator->calls['margin'][0])->toBe(2);
    expect($this->fakeGenerator->calls['errorCorrection'][0])->toBe(ErrorCorrectionLevel::H);
    expect($this->fakeGenerator->calls['color'])->toBe([255, 0, 0, null]);
    expect($this->fakeGenerator->calls['backgroundColor'])->toBe([0, 0, 0, 50]);
    expect($this->fakeGenerator->calls['generate'])->toBe(['https://example.com', 'public/qr.png']);
});

test('it falls back to interactive prompts if no arguments are provided', function (): void {
    $this->artisan('qr:generate')
        ->expectsQuestion('What data/payload should be encoded in the QR code?', 'https://interactive.com')
        ->expectsQuestion('Where should the QR code be saved?', '') // Empty string forces console output
        // Skips format selection natively because output is null!
        ->expectsConfirmation('Do you want to configure advanced options (size, colors, margin)?', 'yes')
        ->expectsQuestion('Size in pixels', '300')
        ->expectsQuestion('Foreground color (RGB or RGBA comma-separated)', '10,20,30')
        ->expectsQuestion('Background color (RGB or RGBA comma-separated)', '40,50,60,70')
        ->expectsQuestion('Margin', '1')
        ->expectsChoice('Error correction level', 'Q', ['L', 'M', 'Q', 'H'])
        ->assertSuccessful()
        ->expectsOutput('<svg>fake-qr-code</svg>');

    expect($this->fakeGenerator->calls['size'][0])->toBe(300);
    expect($this->fakeGenerator->calls['color'])->toBe([10, 20, 30, null]);
    expect($this->fakeGenerator->calls['backgroundColor'])->toBe([40, 50, 60, 70]);
    expect($this->fakeGenerator->calls['margin'][0])->toBe(1);
    expect($this->fakeGenerator->calls['errorCorrection'][0])->toBe(ErrorCorrectionLevel::Q);
    expect($this->fakeGenerator->calls['generate'])->toBe(['https://interactive.com', null]);
});

test('it correctly skips advanced interactive prompts if the user declines', function (): void {
    $this->artisan('qr:generate')
        ->expectsQuestion('What data/payload should be encoded in the QR code?', 'https://test.com')
        ->expectsQuestion('Where should the QR code be saved?', 'test.svg')
        ->expectsChoice('What output format do you want?', 'svg', ['svg', 'png', 'webp', 'eps'])
        ->expectsConfirmation('Do you want to configure advanced options (size, colors, margin)?', 'no')
        ->assertSuccessful();

    // Proves it used the strict defaults
    expect($this->fakeGenerator->calls['size'][0])->toBe(400);
    expect($this->fakeGenerator->calls['color'])->toBe([0, 0, 0, null]);
});

test('it fails explicitly if size boundary is violated to kill validation mutants', function (): void {
    $this->artisan('qr:generate', ['data' => 'test', '--size' => '0'])
        ->assertFailed()
        ->expectsOutputToContain('Size must be a positive integer.');

    $this->artisan('qr:generate', ['data' => 'test', '--size' => 'invalid'])
        ->assertFailed()
        ->expectsOutputToContain('Size must be a positive integer.');
});

test('it fails explicitly if margin boundary is violated to kill validation mutants', function (): void {
    $this->artisan('qr:generate', ['data' => 'test', '--margin' => '-1'])
        ->assertFailed()
        ->expectsOutputToContain('Margin must be a positive integer or zero.');

    $this->artisan('qr:generate', ['data' => 'test', '--margin' => 'invalid'])
        ->assertFailed()
        ->expectsOutputToContain('Margin must be a positive integer or zero.');
});

test('it fails explicitly if error correction is invalid', function (): void {
    $this->artisan('qr:generate', ['data' => 'test', '--errorCorrection' => 'Z'])
        ->assertFailed()
        ->expectsOutputToContain('Invalid error correction level. Please use L, M, Q, or H.');
});

test('it strictly validates color format length to kill array length mutants', function (): void {
    // Length 2
    $this->artisan('qr:generate', ['data' => 'test', '--color' => '255,0'])
        ->assertFailed()
        ->expectsOutputToContain('Invalid format. Please use RGB or RGBA comma-separated values');

    // Length 5
    $this->artisan('qr:generate', ['data' => 'test', '--color' => '255,0,0,100,50'])
        ->assertFailed()
        ->expectsOutputToContain('Invalid format. Please use RGB or RGBA comma-separated values');
});

test('it strictly validates numeric color constraints to kill string cast mutants', function (): void {
    // Non numeric
    $this->artisan('qr:generate', ['data' => 'test', '--color' => '255,A,0'])
        ->assertFailed()
        ->expectsOutputToContain('All color values must be numeric.');
});

test('it enforces strict RGB boundary checks to kill integer boundary mutants', function (): void {
    // Less than 0
    $this->artisan('qr:generate', ['data' => 'test', '--color' => '-1,0,0'])
        ->assertFailed()
        ->expectsOutputToContain('RGB values must be between 0 and 255.');

    // Greater than 255
    $this->artisan('qr:generate', ['data' => 'test', '--color' => '255,256,0'])
        ->assertFailed()
        ->expectsOutputToContain('RGB values must be between 0 and 255.');
});

test('it enforces strict Alpha boundary checks to kill integer boundary mutants', function (): void {
    // Less than 0
    $this->artisan('qr:generate', ['data' => 'test', '--color' => '0,0,0,-1'])
        ->assertFailed()
        ->expectsOutputToContain('Alpha value must be between 0 and 100.');

    // Greater than 100
    $this->artisan('qr:generate', ['data' => 'test', '--color' => '0,0,0,101'])
        ->assertFailed()
        ->expectsOutputToContain('Alpha value must be between 0 and 100.');
});

test('it gracefully catches Generator exceptions and returns failure', function (): void {

    final class FakeGeneratorException extends Exception implements QrCodeExceptionInterface
    {
        use HasHelperMessage;
    }

    $crashingGenerator = new class
    {
        public function __call(string $name, array $arguments): self
        {
            return $this;
        }

        public function generate(): string
        {
            throw new FakeGeneratorException('Simulated crash');
        }
    };
    QrCode::swap($crashingGenerator);

    $this->artisan('qr:generate', ['data' => 'test'])
        ->assertFailed()
        ->expectsOutputToContain('Failed to generate QR Code: Simulated crash');
});

test('it successfully normalizes lowercase error correction levels to kill strtoupper mutants', function (): void {
    $this->artisan('qr:generate', [
        'data' => 'test',
        '--errorCorrection' => 'h', // Lowercase 'h' forces strtoupper to execute
    ])->assertSuccessful();

    expect($this->fakeGenerator->calls['errorCorrection'][0])->toBe(ErrorCorrectionLevel::H);
});

test('it validates exact integer boundaries for size and margin to kill increment mutants', function (): void {
    $this->artisan('qr:generate', [
        'data' => 'test',
        '--size' => '1',
        '--margin' => '0',
    ])->assertSuccessful();

    expect($this->fakeGenerator->calls['size'][0])->toBe(1);
    expect($this->fakeGenerator->calls['margin'][0])->toBe(0);
});

test('it strictly rejects floating point sizes and margins to kill integer casting mutants', function (): void {
    $this->artisan('qr:generate', [
        'data' => 'test',
        '--size' => '1.5',
    ])->assertFailed()
        ->expectsOutputToContain('Size must be a positive integer.');

    $this->artisan('qr:generate', [
        'data' => 'test',
        '--margin' => '0.5',
    ])->assertFailed()
        ->expectsOutputToContain('Margin must be a positive integer or zero.');
});

test('it strictly validates the blue channel boundary to kill index mutants', function (): void {
    $this->artisan('qr:generate', ['data' => 'test', '--color' => '0,0,256'])
        ->assertFailed()
        ->expectsOutputToContain('RGB values must be between 0 and 255.');
});

test('it enforces exact alpha channel boundaries to kill boolean boundary mutants', function (): void {
    $this->artisan('qr:generate', ['data' => 'test', '--color' => '0,0,0,0'])
        ->assertSuccessful();

    $this->artisan('qr:generate', ['data' => 'test', '--color' => '0,0,0,100'])
        ->assertSuccessful();
});

test('it skips advanced confirmation prompt and enters advanced mode if ANY single advanced option is passed', function (string $option, string $value): void {
    $command = $this->artisan('qr:generate', [
        $option => $value,
    ]);

    $command->expectsQuestion('What data/payload should be encoded in the QR code?', 'test')
        ->expectsQuestion('Where should the QR code be saved?', '');

    if ($option !== '--size') {
        $command->expectsQuestion('Size in pixels', '400');
    }

    if ($option !== '--color') {
        $command->expectsQuestion('Foreground color (RGB or RGBA comma-separated)', '0,0,0');
    }

    if ($option !== '--backgroundColor') {
        $command->expectsQuestion('Background color (RGB or RGBA comma-separated)', '255,255,255');
    }

    if ($option !== '--margin') {
        $command->expectsQuestion('Margin', '4');
    }

    if ($option !== '--errorCorrection') {
        $command->expectsChoice('Error correction level', 'M', ['L', 'M', 'Q', 'H']);
    }

    $command->assertSuccessful();
})->with([
    ['--size', '500'],
    ['--color', '255,0,0'],
    ['--backgroundColor', '0,0,0'],
    ['--margin', '2'],
    ['--errorCorrection', 'H'],
]);

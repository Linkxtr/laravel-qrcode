<?php

declare(strict_types=1);

use Linkxtr\QrCode\Console\Commands\GenerateQrCodeCommand;
use Linkxtr\QrCode\Enums\ErrorCorrectionLevel;
use Linkxtr\QrCode\Facades\QrCode;

covers(GenerateQrCodeCommand::class);

beforeEach(function () {
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

test('it can generate a qr code purely through cli arguments and options', function () {
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

test('it falls back to interactive prompts if no arguments are provided', function () {
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

test('it correctly skips advanced interactive prompts if the user declines', function () {
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

test('it fails explicitly if size boundary is violated to kill validation mutants', function () {
    $this->artisan('qr:generate', ['data' => 'test', '--size' => '0'])
        ->assertFailed()
        ->expectsOutputToContain('Size must be a positive integer.');

    $this->artisan('qr:generate', ['data' => 'test', '--size' => 'invalid'])
        ->assertFailed()
        ->expectsOutputToContain('Size must be a positive integer.');
});

test('it fails explicitly if margin boundary is violated to kill validation mutants', function () {
    $this->artisan('qr:generate', ['data' => 'test', '--margin' => '-1'])
        ->assertFailed()
        ->expectsOutputToContain('Margin must be a positive integer or zero.');

    $this->artisan('qr:generate', ['data' => 'test', '--margin' => 'invalid'])
        ->assertFailed()
        ->expectsOutputToContain('Margin must be a positive integer or zero.');
});

test('it fails explicitly if error correction is invalid', function () {
    $this->artisan('qr:generate', ['data' => 'test', '--errorCorrection' => 'Z'])
        ->assertFailed()
        ->expectsOutputToContain('Invalid error correction level. Please use L, M, Q, or H.');
});

test('it strictly validates color format length to kill array length mutants', function () {
    // Length 2
    $this->artisan('qr:generate', ['data' => 'test', '--color' => '255,0'])
        ->assertFailed()
        ->expectsOutputToContain('Invalid format. Please use RGB or RGBA comma-separated values');

    // Length 5
    $this->artisan('qr:generate', ['data' => 'test', '--color' => '255,0,0,100,50'])
        ->assertFailed()
        ->expectsOutputToContain('Invalid format. Please use RGB or RGBA comma-separated values');
});

test('it strictly validates numeric color constraints to kill string cast mutants', function () {
    // Non numeric
    $this->artisan('qr:generate', ['data' => 'test', '--color' => '255,A,0'])
        ->assertFailed()
        ->expectsOutputToContain('All color values must be numeric.');
});

test('it enforces strict RGB boundary checks to kill integer boundary mutants', function () {
    // Less than 0
    $this->artisan('qr:generate', ['data' => 'test', '--color' => '-1,0,0'])
        ->assertFailed()
        ->expectsOutputToContain('RGB values must be between 0 and 255.');

    // Greater than 255
    $this->artisan('qr:generate', ['data' => 'test', '--color' => '255,256,0'])
        ->assertFailed()
        ->expectsOutputToContain('RGB values must be between 0 and 255.');
});

test('it enforces strict Alpha boundary checks to kill integer boundary mutants', function () {
    // Less than 0
    $this->artisan('qr:generate', ['data' => 'test', '--color' => '0,0,0,-1'])
        ->assertFailed()
        ->expectsOutputToContain('Alpha value must be between 0 and 100.');

    // Greater than 100
    $this->artisan('qr:generate', ['data' => 'test', '--color' => '0,0,0,101'])
        ->assertFailed()
        ->expectsOutputToContain('Alpha value must be between 0 and 100.');
});

test('it gracefully catches Generator exceptions and returns failure', function () {
    $crashingGenerator = new class
    {
        public function __call(string $name, array $arguments): self
        {
            return $this;
        }

        public function generate(string $data, ?string $output = null): string
        {
            throw new Exception('Simulated crash');
        }
    };
    QrCode::swap($crashingGenerator);

    $this->artisan('qr:generate', ['data' => 'test'])
        ->assertFailed()
        ->expectsOutputToContain('Failed to generate QR Code: Simulated crash');
});

test('it skips advanced interactive prompts if ANY advanced option is passed in CLI to kill Boolean logic mutants', function () {
    // By passing just ONE advanced option (--size), it should immediately skip the confirm prompt.
    // If Infection mutates the logic to require ALL options, this test will hit the unexpected confirm prompt and crash!
    $this->artisan('qr:generate', [
        '--size' => '500',
    ])
        ->expectsQuestion('What data/payload should be encoded in the QR code?', 'test')
        ->expectsQuestion('Where should the QR code be saved?', '')
    // We removed expectsChoice for format because answering '' for output naturally skips it!
        ->assertSuccessful();

    expect($this->fakeGenerator->calls['size'][0])->toBe(500);
});

test('it successfully normalizes lowercase error correction levels to kill strtoupper mutants', function () {
    $this->artisan('qr:generate', [
        'data' => 'test',
        '--errorCorrection' => 'h', // Lowercase 'h' forces strtoupper to execute
    ])->assertSuccessful();

    expect($this->fakeGenerator->calls['errorCorrection'][0])->toBe(ErrorCorrectionLevel::H);
});

test('it validates exact integer boundaries for size and margin to kill increment mutants', function () {
    // 1 is the absolute minimum for size (> 0)
    // 0 is the absolute minimum for margin (>= 0)
    $this->artisan('qr:generate', [
        'data' => 'test',
        '--size' => '1',
        '--margin' => '0',
    ])->assertSuccessful();

    expect($this->fakeGenerator->calls['size'][0])->toBe(1);
    expect($this->fakeGenerator->calls['margin'][0])->toBe(0);
});

test('it strictly rejects floating point sizes and margins to kill integer casting mutants', function () {
    $this->artisan('qr:generate', [
        'data' => 'test',
        '--size' => '1.5', // Without filter_var, (int) '1.5' > 0 evaluates to true and bypasses validation!
    ])->assertFailed()
        ->expectsOutputToContain('Size must be a positive integer.');

    $this->artisan('qr:generate', [
        'data' => 'test',
        '--margin' => '0.5',
    ])->assertFailed()
        ->expectsOutputToContain('Margin must be a positive integer or zero.');
});

test('it skips advanced interactive prompts if ANY single advanced option is passed in CLI', function (string $option, string $value) {
    // This dynamically tests every single item in the array_filter!
    // Kills all RemoveArrayItem mutants on Line 79.
    $this->artisan('qr:generate', [
        $option => $value,
    ])
        ->expectsQuestion('What data/payload should be encoded in the QR code?', 'test')
        ->expectsQuestion('Where should the QR code be saved?', '')
        ->assertSuccessful();
})->with([
    ['--size', '500'],
    ['--color', '255,0,0'],
    ['--backgroundColor', '0,0,0'],
    ['--margin', '2'],
    ['--errorCorrection', 'H'],
]);

test('it strictly validates the blue channel boundary to kill index mutants', function () {
    // Kills the `$index < 3` to `< 2` mutant!
    // If the loop stops checking at index 2, the blue channel (256) will slip through and crash the test.
    $this->artisan('qr:generate', ['data' => 'test', '--color' => '0,0,256'])
        ->assertFailed()
        ->expectsOutputToContain('RGB values must be between 0 and 255.');
});

test('it enforces exact alpha channel boundaries to kill boolean boundary mutants', function () {
    // Test exactly 0 (Kills < 1 and <= 0 mutants)
    $this->artisan('qr:generate', ['data' => 'test', '--color' => '0,0,0,0'])
        ->assertSuccessful();

    // Test exactly 100 (Kills > 99 and >= 100 mutants)
    $this->artisan('qr:generate', ['data' => 'test', '--color' => '0,0,0,100'])
        ->assertSuccessful();
});

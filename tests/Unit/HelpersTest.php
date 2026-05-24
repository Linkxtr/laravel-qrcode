<?php

declare(strict_types=1);

use Linkxtr\QrCode\Enums\Format;
use Linkxtr\QrCode\Facades\QrCode;
use Linkxtr\QrCode\Generator;
use Linkxtr\QrCode\Support\QrCodeResult;

test('qrcode helper resolves the generator from the container when no argument is provided', function (): void {
    $generator = qrcode();

    expect($generator)->toBeInstanceOf(Generator::class);
});

test('qrcode helper delegates directly to the facade when text is provided to kill parameter mutants', function (): void {
    $fakeGenerator = new class
    {
        public ?string $receivedData = null;

        public function generate(string $data): QrCodeResult
        {
            $this->receivedData = $data;

            return new QrCodeResult('<svg>helper-test</svg>', Format::SVG);
        }
    };

    QrCode::swap($fakeGenerator);

    $QrCodeResult = qrcode('https://linkxtr.com');

    expect($QrCodeResult)->toBeInstanceOf(QrCodeResult::class)
        ->and((string) $QrCodeResult)->toBe('<svg>helper-test</svg>');
    expect($fakeGenerator->receivedData)->toBe('https://linkxtr.com');
});

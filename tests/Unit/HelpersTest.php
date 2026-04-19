<?php

declare(strict_types=1);

use Illuminate\Support\HtmlString;
use Linkxtr\QrCode\Facades\QrCode;
use Linkxtr\QrCode\Generator;

test('qrcode helper resolves the generator from the container when no argument is provided', function () {
    $generator = qrcode();

    expect($generator)->toBeInstanceOf(Generator::class);
});

test('qrcode helper delegates directly to the facade when text is provided to kill parameter mutants', function () {
    $fakeGenerator = new class
    {
        public ?string $receivedData = null;

        public function generate(string $data): HtmlString
        {
            $this->receivedData = $data;

            return new HtmlString('<svg>helper-test</svg>');
        }
    };

    QrCode::swap($fakeGenerator);

    $result = qrcode('https://linkxtr.com');

    expect($result)->toBeInstanceOf(HtmlString::class)
        ->and((string) $result)->toBe('<svg>helper-test</svg>');
    expect($fakeGenerator->receivedData)->toBe('https://linkxtr.com');
});

<?php

declare(strict_types=1);

use Illuminate\Support\HtmlString;
use Linkxtr\QrCode\Generator;

it('can register and call custom macros', function () {
    Generator::macro('spotify', function (string $uri) {
        return $this->generate('spotify:track:'.$uri);
    });

    $generator = new Generator;
    $result = $generator->spotify('4uLU6hMCjMI75M1A2tKUQC');

    expect($result)->toBeInstanceOf(HtmlString::class);
    // Generating an SVG with default settings should output the data
    expect((string) $result)->toContain('<svg', '</svg>');
})->after(function () {
    Generator::flushMacros();
});

it('wraps string returns from macro in HtmlString', function () {
    Generator::macro('returnString', function () {
        return '<svg>custom string</svg>';
    });

    $generator = new Generator;
    $result = $generator->returnString();

    expect($result)->toBeInstanceOf(HtmlString::class);
    expect((string) $result)->toBe('<svg>custom string</svg>');
})->after(function () {
    Generator::flushMacros();
});

it('wraps Stringable object returns from macro in HtmlString', function () {
    Generator::macro('returnStringable', function () {
        return new class implements Stringable
        {
            public function __toString(): string
            {
                return '<svg>custom stringable</svg>';
            }
        };
    });

    $generator = new Generator;
    $result = $generator->returnStringable();

    expect($result)->toBeInstanceOf(HtmlString::class);
    expect((string) $result)->toBe('<svg>custom stringable</svg>');
})->after(function () {
    Generator::flushMacros();
});

it('wraps unsupported type returns from macro in an empty HtmlString', function () {
    Generator::macro('returnArray', function () {
        return ['an' => 'array'];
    });

    $generator = new Generator;
    $result = $generator->returnArray();

    expect($result)->toBeInstanceOf(HtmlString::class);
    expect((string) $result)->toBe('');
})->after(function () {
    Generator::flushMacros();
});

it('still delegates to data types if macro is not registered', function () {
    $generator = new Generator;
    $result = $generator->Email('test@example.com');

    expect($result)->toBeInstanceOf(HtmlString::class);
    expect((string) $result)->toContain('<svg');
});

it('throws bad method exception for completely non-existent methods', function () {
    $generator = new Generator;
    $generator->nonExistentMacro('test');
})->throws(BadMethodCallException::class);

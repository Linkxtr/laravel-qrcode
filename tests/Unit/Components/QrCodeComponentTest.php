<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Log;
use Illuminate\View\ComponentAttributeBag;
use Linkxtr\QrCode\Components\QrCodeComponent;
use Linkxtr\QrCode\Enums\GradientType;
use Linkxtr\QrCode\Exceptions\GenerationException;
use Linkxtr\QrCode\Exceptions\InvalidConfigurationException;
use Linkxtr\QrCode\Facades\QrCode;
use Linkxtr\QrCode\Generator;
use Linkxtr\QrCode\ValueObjects\Colors\Rgb;

use function Linkxtr\QrCode\DTOs\base_path;

covers(QrCodeComponent::class);

beforeEach(function (): void {
    QrCode::swap(new Generator([]));
});

test('it throws exceptions for invalid formats', function (): void {
    $component1 = new QrCodeComponent(data: 'test', format: 'invalid');
    expect(fn (): Closure => $component1->render())->toThrow(InvalidConfigurationException::class, 'Format must be one of the following values: svg, png, webp. Got: invalid');

    $component2 = new QrCodeComponent(data: 'test', format: 'eps');
    expect(fn (): Closure => $component2->render())->toThrow(InvalidConfigurationException::class, 'Format must be one of the following values: svg, png, webp. Got: eps');
});

test('it throws exception for path traversal attempts in merge', function (): void {
    $component = new QrCodeComponent(data: 'test', merge: '../malicious.png');
    expect(fn (): Closure => $component->render())->toThrow(InvalidConfigurationException::class, 'Image file does not exist or is not readable: '.base_path('../malicious.png'));
});

test('it generates default svg with injected title and accessibility attributes', function (): void {
    $component = new QrCodeComponent(data: 'https://linkxtr.com');

    expect($component->margin)->toBe(0);

    $closure = $component->render();
    $html = $closure(['attributes' => new ComponentAttributeBag(['class' => 'qr-class'])]);

    expect($html)
        ->toStartWith('<?xml')
        ->toContain('<svg')
        ->toContain('width="100"')
        ->toContain('height="100"')
        ->toContain('class="qr-class"')
        ->toContain('role="img"')
        ->toContain('aria-label="QR Code"')
        ->toContain('<title>QR Code</title>');
});

test('it respects and parses hex colors correctly', function (): void {
    $component = new QrCodeComponent(
        data: 'test',
        color: '#FF0000',
        backgroundColor: '#00FF00'
    );

    $closure = $component->render();
    $html = $closure(['attributes' => new ComponentAttributeBag([])]);

    expect($html)
        ->toContain('fill="#ff0000"')
        ->toContain('fill="#00ff00"');
});

test('it ignores incorrectly formatted hex colors safely', function (): void {
    $component = new QrCodeComponent(
        data: 'test',
        color: '#ZZZZZZ'
    );

    $closure = $component->render();
    $html = $closure(['attributes' => new ComponentAttributeBag([])]);

    expect($html)->toContain('fill="#000000"');
});

test('it generates base64 image tag for png format', function (): void {
    $component = new QrCodeComponent(
        data: 'test',
        format: 'png'
    );

    $closure = $component->render();
    $html = $closure(['attributes' => new ComponentAttributeBag(['class' => 'img-class'])]);

    expect($html)
        ->toStartWith('<img')
        ->toContain('class="img-class"')
        ->toContain('alt="QR Code"')
        ->toContain('src="data:image/png;base64,');
});

test('it generates base64 image tag for webp format using a mocked facade', function (): void {
    $fakeGenerator = new class
    {
        public array $calls = [];

        public function __call(string $name, array $arguments): self
        {
            $this->calls[$name] = $arguments;

            return $this;
        }

        public function generate(): string
        {
            return '<svg></svg>';
        }
    };

    QrCode::swap($fakeGenerator);

    $component = new QrCodeComponent(
        data: 'test',
        format: 'webp'
    );

    $closure = $component->render();
    $html = $closure(['attributes' => new ComponentAttributeBag(['class' => 'img-class'])]);

    $expectedBase64 = base64_encode('<svg></svg>');

    expect($html)
        ->toStartWith('<img')
        ->toContain('class="img-class"')
        ->toContain('alt="QR Code"')
        ->toContain('src="data:image/webp;base64,'.$expectedBase64.'"');
});

test('it strictly maps all 6 distinct gradient colors', function (): void {
    $fakeGenerator = new class
    {
        public array $calls = [];

        public function __call(string $name, array $arguments): self
        {
            $this->calls[$name] = $arguments;

            return $this;
        }

        public function generate(): string
        {
            return '<svg></svg>';
        }
    };

    QrCode::swap($fakeGenerator);

    $component = new QrCodeComponent(
        data: 'https://example.com',
        gradient: '#010203, #040506',
        gradientType: 'diagonal'
    );

    $component->render()(['attributes' => new ComponentAttributeBag([])]);

    expect($fakeGenerator->calls)->toHaveKey('gradient')
        ->and($fakeGenerator->calls['gradient'][0])->toBe([1, 2, 3, 100])
        ->and($fakeGenerator->calls['gradient'][1])->toBe([4, 5, 6, 100])
        ->and($fakeGenerator->calls['gradient'][2])->toBe('diagonal');

    $fakeGenerator->calls = [];

    $componentDefaultType = new QrCodeComponent(
        data: 'https://example.com',
        gradient: '#010203, #040506'
    );

    $componentDefaultType->render()(['attributes' => new ComponentAttributeBag([])]);

    expect($fakeGenerator->calls['gradient'][2])->toBe(GradientType::VERTICAL);
});

test('it resolves multi color attributes correctly', function (): void {
    $component = new QrCodeComponent(
        data: 'test',
        eyeColor0: '#FF0000, #00FF00',
        eyeColor1: '255,0,0|0,255,0',
    );
    $closure = $component->render();
    $html = $closure(['attributes' => new ComponentAttributeBag([])]);

    expect($html)->toBeString()->not->toBeEmpty();
});

test('it handles string merges', function (): void {
    $component = new QrCodeComponent(
        data: 'test',
        mergeString: file_get_contents(__DIR__.'/../../Support/Fixtures/images/linkxtr.png'),
        mergePercentage: 0.3
    );

    $closure = $component->render();
    $html = $closure(['attributes' => new ComponentAttributeBag([])]);

    expect($html)->toBeString()->not->toBeEmpty();
});

test('it handles visual and data modifiers', function (): void {
    $component = new QrCodeComponent(
        data: 'test',
        style: 'dot',
        errorCorrection: 'H',
        encoding: 'UTF-8',
        eye: 'circle',
        eyeColor2: '#FF0000|#00FF00'
    );

    $closure = $component->render();
    $html = $closure(['attributes' => new ComponentAttributeBag([])]);

    expect($html)->toBeString()->not->toBeEmpty();
});

test('it handles merge with valid path and string absolute boolean', function (): void {
    $component = new QrCodeComponent(
        data: 'test',
        merge: realpath(__DIR__.'/../../Support/Fixtures/images/linkxtr.png'),
    );

    $closure = $component->render();
    $html = $closure(['attributes' => new ComponentAttributeBag([])]);

    expect($html)->toBeString()->not->toBeEmpty();
});

test('it handles 4-part rgb csv colors with alpha', function (): void {
    $component = new QrCodeComponent(
        data: 'test',
        color: '255, 0, 100, 50',
        backgroundColor: '0, 255, 50, 70'
    );

    $closure = $component->render();
    $html = $closure(['attributes' => new ComponentAttributeBag([])]);

    expect($html)->toBeString()->not->toBeEmpty()
        ->toContain('fill="#ff0064"')
        ->toContain('fill="#00ff32"')
        ->toContain('fill-opacity="0.5"')
        ->toContain('fill-opacity="0.7"');
});

test('it ignores invalid csv color formats', function (): void {
    $component = new QrCodeComponent(
        data: 'test',
        color: '255, 0',
        backgroundColor: 'invalidstring'
    );

    $closure = $component->render();
    $html = $closure(['attributes' => new ComponentAttributeBag([])]);

    expect($html)->toContain('fill="#000000"');
});

test('it handles the style attribute', function (): void {
    $component = new QrCodeComponent(
        data: 'test',
        style: 'dot',
        eye: 'circle',
        eyeColor0: '#FF0000|#00FF00',
        eyeColor1: '#FF0000|#00FF00',
        eyeColor2: '#FF0000|#00FF00',
    );

    $closure = $component->render();
    $html = $closure(['attributes' => new ComponentAttributeBag([])]);

    expect($html)->toBeString()->not->toBeEmpty();
});

test('it dynamically applies styles and modifies the SVG output', function (): void {
    $defaultComponent = new QrCodeComponent(data: 'https://example.com');
    $defaultHtml = $defaultComponent->render()(['attributes' => new ComponentAttributeBag([])]);

    $styleComponent = new QrCodeComponent(data: 'https://example.com', style: 'round');
    $styleHtml = $styleComponent->render()(['attributes' => new ComponentAttributeBag([])]);
    expect($styleHtml)->not->toBe($defaultHtml, 'The style method was removed or failed to modify the output.');

    $ecComponent = new QrCodeComponent(data: 'https://example.com', errorCorrection: 'H');
    $ecHtml = $ecComponent->render()(['attributes' => new ComponentAttributeBag([])]);
    expect($ecHtml)->not->toBe($defaultHtml, 'The errorCorrection method was removed or failed to modify the output.');

    $eyeComponent = new QrCodeComponent(data: 'https://example.com', eye: 'circle');
    $eyeHtml = $eyeComponent->render()(['attributes' => new ComponentAttributeBag([])]);
    expect($eyeHtml)->not->toBe($defaultHtml, 'The eye method was removed or failed to modify the output.');
});

test('it dynamically applies encoding and modifies the SVG output', function (): void {
    $data = 'Café';

    $defaultHtml = (new QrCodeComponent(data: $data))->render()(['attributes' => new ComponentAttributeBag([])]);

    $encodingHtml = (new QrCodeComponent(data: $data, encoding: 'ISO-8859-1'))->render()(['attributes' => new ComponentAttributeBag([])]);

    expect($encodingHtml)->not->toBe($defaultHtml, 'The encoding method was removed or failed to modify the output.');
});

test('it applies custom colors correctly to the SVG output', function (): void {
    $component = new QrCodeComponent(
        data: 'https://example.com',
        color: '#FF0000',
        backgroundColor: '#00FF00'
    );

    $html = $component->render()(['attributes' => new ComponentAttributeBag([])]);

    expect($html)
        ->toContain('#ff0000')
        ->toContain('#00ff00');
});

test('it applies custom eye colors correctly to the SVG output', function (): void {
    $component = new QrCodeComponent(
        data: 'https://example.com',
        eyeColor0: '#0000FF',
        eyeColor1: '#FF00FF',
        eyeColor2: '#00FF00'
    );

    $html = $component->render()(['attributes' => new ComponentAttributeBag([])]);

    expect($html)
        ->toContain('#0000ff')
        ->toContain('#ff00ff')
        ->toContain('#00ff00');
});

test('it dynamically delegates config methods to the generator via facade', function (): void {
    $fakeGenerator = new class
    {
        public array $calls = [];

        public function __call(string $name, array $arguments): self
        {
            $this->calls[$name] = $arguments;

            return $this;
        }

        public function generate(): string
        {
            return '<svg></svg>';
        }
    };

    QrCode::swap($fakeGenerator);

    $component = new QrCodeComponent(
        data: 'https://example.com',
        style: 'round',
        errorCorrection: 'H',
        encoding: 'ISO-8859-1',
        eye: 'circle',
        merge: '/path/to/logo.png',
        mergePercentage: 0.3
    );

    $closure = $component->render();
    $closure(['attributes' => new ComponentAttributeBag([])]);

    expect($fakeGenerator->calls)->toHaveKey('errorCorrection')
        ->and($fakeGenerator->calls['errorCorrection'][0])->toBe('H', 'The errorCorrection method was removed!');

    expect($fakeGenerator->calls)->toHaveKey('eye')
        ->and($fakeGenerator->calls['eye'][0])->toBe('circle', 'The eye method was removed!');

    expect($fakeGenerator->calls)->toHaveKey('style')
        ->and($fakeGenerator->calls['style'][0])->toBe('round', 'The style method was removed!');

    expect($fakeGenerator->calls)->toHaveKey('encoding')
        ->and($fakeGenerator->calls['encoding'][0])->toBe('ISO-8859-1', 'The encoding method was removed!');

    expect($fakeGenerator->calls)->toHaveKey('merge')
        ->and($fakeGenerator->calls['merge'][0])->toBe('/path/to/logo.png', 'The merge method was removed!')
        ->and($fakeGenerator->calls['merge'][1])->toBe(0.3);

    $componentMergeString = new QrCodeComponent(
        data: 'https://example.com',
        mergeString: '<svg>logo</svg>',
        mergePercentage: 0.4
    );

    $componentMergeString->render()(['attributes' => new ComponentAttributeBag([])]);

    expect($fakeGenerator->calls)->toHaveKey('mergeString')
        ->and($fakeGenerator->calls['mergeString'][0])->toBe('<svg>logo</svg>', 'The mergeString method was removed!')
        ->and($fakeGenerator->calls['mergeString'][1])->toBe(0.4);
});

test('it handles translation escaping and strictly limits svg injection', function (): void {
    app()->instance('translator', new class
    {
        public function get($key)
        {
            return $key === 'QR Code' ? 'Mocked Title' : $key;
        }

        public function getFromJson($key)
        {
            return $this->get($key);
        }

        public function choice($key)
        {
            return $key;
        }

        public function getLocale(): string
        {
            return 'en';
        }
    });

    $fakeGenerator = new class
    {
        public function __call(string $name, array $arguments)
        {
            return $this;
        }

        public function generate(): string
        {
            return '<svg id="1"></svg><svg id="2"></svg>';
        }
    };
    QrCode::swap($fakeGenerator);

    $component = new QrCodeComponent(data: 'test');
    $html = $component->render()(['attributes' => new ComponentAttributeBag(['class' => 'test-class'])]);

    expect($html)->toStartWith('<svg ')
        ->toContain('class="test-class"');
    expect($html)->toMatch('/<title[^>]*>Mocked Title<\/title>/');
    expect($html)->toMatch('/<svg[^>]*id="1"[^>]*><title[^>]*>Mocked Title<\/title><\/svg>/');
    expect($html)->toContain('<svg id="2"></svg>');
    expect($html)->not->toMatch('/<svg[^>]*id="2"[^>]*><title/');
});

test('it precisely formats the img tag for non-svg formats', function (): void {
    $fakeGenerator = new class
    {
        public function __call(string $name, array $arguments)
        {
            return $this;
        }

        public function generate(): string
        {
            return 'image_binary_data';
        }
    };
    QrCode::swap($fakeGenerator);

    $component = new QrCodeComponent(data: 'test', format: 'png');
    $html = $component->render()(['attributes' => new ComponentAttributeBag(['class' => 'img-class'])]);

    $base64 = base64_encode('image_binary_data');

    expect($html)->toBe('<img alt="QR Code" class="img-class" src="data:image/png;base64,'.$base64.'" />');
});

test('it safely resolves colors with spaces, 3-parameter csv, and explicit alphas', function (): void {
    $fakeGenerator = new class
    {
        public array $calls = [];

        public function __call(string $name, array $arguments): self
        {
            $this->calls[$name] = $arguments;

            return $this;
        }

        public function generate(): string
        {
            return '<svg></svg>';
        }
    };
    QrCode::swap($fakeGenerator);

    $component1 = new QrCodeComponent(data: 'test', color: '  #FF0000  ');
    $component1->render()(['attributes' => new ComponentAttributeBag([])]);

    expect($fakeGenerator->calls)->toHaveKey('color')
        ->and($fakeGenerator->calls['color'])->toBe([255, 0, 0, 100]);

    $fakeGenerator->calls = [];

    $component2 = new QrCodeComponent(data: 'test', backgroundColor: '0, 255, 0');
    $component2->render()(['attributes' => new ComponentAttributeBag([])]);

    expect($fakeGenerator->calls)->toHaveKey('backgroundColor')
        ->and($fakeGenerator->calls['backgroundColor'])->toBe([0, 255, 0, 100]);

    $fakeGenerator->calls = [];

    $component3 = new QrCodeComponent(data: 'test', color: '0, 0, 255, 100');
    $component3->render()(['attributes' => new ComponentAttributeBag([])]);

    expect($fakeGenerator->calls)->toHaveKey('color')
        ->and($fakeGenerator->calls['color'])->toBe([0, 0, 255, 100]);
});

test('it aggressively resolves multi-color strings across different delimiters', function (): void {
    $fakeGenerator = new class
    {
        public array $calls = [];

        public function __call(string $name, array $arguments): self
        {
            $this->calls[$name] = $arguments;

            return $this;
        }

        public function generate(): string
        {
            return '<svg></svg>';
        }
    };
    QrCode::swap($fakeGenerator);

    (new QrCodeComponent(data: 'test', eyeColor0: '#010203;#040506'))
        ->render()(['attributes' => new ComponentAttributeBag([])]);

    expect($fakeGenerator->calls['eyeColor'][0])->toBe(0);
    expect($fakeGenerator->calls['eyeColor'][1])->toBe([1, 2, 3, 100]);
    expect($fakeGenerator->calls['eyeColor'][2])->toBe([4, 5, 6, 100]);

    $fakeGenerator->calls = [];

    (new QrCodeComponent(data: 'test', eyeColor1: '#010203, #040506'))
        ->render()(['attributes' => new ComponentAttributeBag([])]);

    expect($fakeGenerator->calls['eyeColor'][0])->toBe(1);
    expect($fakeGenerator->calls['eyeColor'][1])->toBe([1, 2, 3, 100]);
    expect($fakeGenerator->calls['eyeColor'][2])->toBe([4, 5, 6, 100]);

    $fakeGenerator->calls = [];

    (new QrCodeComponent(data: 'test', eyeColor2: '#010203,#040506'))
        ->render()(['attributes' => new ComponentAttributeBag([])]);

    expect($fakeGenerator->calls['eyeColor'][0])->toBe(2);
    expect($fakeGenerator->calls['eyeColor'][1])->toBe([1, 2, 3, 100]);
    expect($fakeGenerator->calls['eyeColor'][2])->toBe([4, 5, 6, 100]);
});

test('it duplicates single multi-colors and safely slices excess colors', function (): void {
    $fakeGenerator = new class
    {
        public array $calls = [];

        public function __call(string $name, array $arguments): self
        {
            $this->calls[$name] = $arguments;

            return $this;
        }

        public function generate(): string
        {
            return '<svg></svg>';
        }
    };
    QrCode::swap($fakeGenerator);

    (new QrCodeComponent(data: 'test', gradient: '#010203'))
        ->render()(['attributes' => new ComponentAttributeBag([])]);

    expect($fakeGenerator->calls['gradient'])->toBe([[1, 2, 3, 100], [1, 2, 3, 100], GradientType::VERTICAL]);

    $fakeGenerator->calls = [];

    (new QrCodeComponent(data: 'test', gradient: '#010203|#040506|#070809'))
        ->render()(['attributes' => new ComponentAttributeBag([])]);

    expect($fakeGenerator->calls['gradient'])->toBe([[1, 2, 3, 100], [4, 5, 6, 100], GradientType::VERTICAL]);
});

test('it returns null early if no valid colors could be parsed', function (): void {
    $fakeGenerator = new class
    {
        public array $calls = [];

        public function __call(string $name, array $arguments): self
        {
            $this->calls[$name] = $arguments;

            return $this;
        }

        public function generate(): string
        {
            return '<svg></svg>';
        }
    };
    QrCode::swap($fakeGenerator);

    $component = new QrCodeComponent(
        data: 'test',
        eyeColor0: '#ZZZ',
        gradient: 'invalid_color_1|invalid_color_2'
    );

    $component->render()(['attributes' => new ComponentAttributeBag([])]);

    expect($fakeGenerator->calls)->not->toHaveKey('gradient');
    expect($fakeGenerator->calls)->not->toHaveKey('eyeColor');
});

test('it strictly validates CSV color tokens to prevent silent type coercion', function (): void {
    $component = new QrCodeComponent('test');

    $method = fn ($color) => invade($component)->resolveColor($color);

    expect($method('255, 128, 0'))->toBeInstanceOf(Rgb::class)
        ->and($method('255, 128, 0, 50'))->toBeInstanceOf(Rgb::class);

    expect($method('255, nope, 0'))->toBeNull()
        ->and($method('255, 128, 0, nope'))->toBeNull();
    expect($method('-1, 128, 0'))->toBeNull();
});

test('it enforces RGB and Alpha upper bounds', function (): void {
    $component = new QrCodeComponent('test');
    $method = fn ($color) => invade($component)->resolveColor($color);

    expect($method('256, 128, 0'))->toBeNull()
        ->and($method('255, 256, 0'))->toBeNull()
        ->and($method('255, 128, 256'))->toBeNull();
    expect($method('255, 128, 0, 101'))->toBeNull();
});

test('it correctly maps inner red color for eyeColor2 to prevent index mutations', function (): void {
    $fakeGenerator = new class
    {
        public array $calls = [];

        public function __call(string $name, array $arguments): self
        {
            $this->calls[$name] = $arguments;

            return $this;
        }

        public function generate(): string
        {
            return '<svg></svg>';
        }
    };
    QrCode::swap($fakeGenerator);

    (new QrCodeComponent(data: 'test', eyeColor2: '#010203,#040506'))
        ->render()(['attributes' => new ComponentAttributeBag([])]);

    expect($fakeGenerator->calls['eyeColor'][0])->toBe(2);
    expect($fakeGenerator->calls['eyeColor'][1])->toBe([1, 2, 3, 100]);
    expect($fakeGenerator->calls['eyeColor'][2])->toBe([4, 5, 6, 100]);
});

it('logs a warning when an invalid color is provided', function (): void {
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn ($message): bool => str_contains((string) $message, 'QrCodeComponent: Unrecognized color format'));

    $component = new QrCodeComponent(
        data: 'Hello',
        color: 'notacolor'
    );

    $component->render();
});

it('logs a warning when an invalid background color is provided', function (): void {
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn ($message): bool => str_contains((string) $message, 'QrCodeComponent: Unrecognized color format'));

    $component = new QrCodeComponent(
        data: 'Hello',
        backgroundColor: 'invalid-bg'
    );

    $component->render();
});

test('it throws exception for malformed svg from generator', function (): void {
    $fakeGenerator = new class
    {
        public function __call(string $name, array $args)
        {
            return $this;
        }

        public function generate(): string
        {
            return 'Not an SVG at all';
        }
    };

    QrCode::swap($fakeGenerator);

    $component = new QrCodeComponent(data: 'test');
    $closure = $component->render();

    $closure(['attributes' => new ComponentAttributeBag([])]);
})->throws(GenerationException::class);

test('it throws exception when loadXML fails due to malformed XML', function (): void {
    $fakeGenerator = new class
    {
        public function __call(string $name, array $args)
        {
            return $this;
        }

        public function generate(): string
        {
            return '<svg><unclosed-tag></svg>';
        }
    };

    QrCode::swap($fakeGenerator);

    $component = new QrCodeComponent(data: 'test');
    $closure = $component->render();

    $closure(['attributes' => new ComponentAttributeBag([])]);
})->throws(GenerationException::class);

test('it does not inject a title if the svg already contains one', function (): void {
    $fakeGenerator = new class
    {
        public function __call(string $name, array $args)
        {
            return $this;
        }

        public function generate(): string
        {
            return '<svg><title>Existing Title</title><g></g></svg>';
        }
    };

    QrCode::swap($fakeGenerator);

    $component = new QrCodeComponent(data: 'test');
    $closure = $component->render();

    $html = $closure(['attributes' => new ComponentAttributeBag([])]);

    expect($html)->toContain('<title>Existing Title</title>');
    expect($html)->not->toContain('<title>QR Code</title>');
});

test('it does not inject a duplicate title tag if the svg already contains one', function (): void {
    $originalSvg = '<svg xmlns="http://www.w3.org/2000/svg"><title>Existing</title><path d="M0 0"/></svg>';

    $component = new QrCodeComponent(data: 'test');
    $result = invade($component)->prepareSvg($originalSvg, new ComponentAttributeBag(['title' => 'New Title']));

    expect(substr_count($result, '<title>'))->toBe(1)
        ->and($result)->toContain('<title>Existing</title>');
});

test('it securely escapes html characters in the svg title', function (): void {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg"></svg>';
    $maliciousTitle = 'My <QR> Code & Link';

    $component = new QrCodeComponent(data: 'test');
    $result = invade($component)->prepareSvg($svg, new ComponentAttributeBag(['title' => $maliciousTitle]));

    expect($result)->toContain('My &lt;QR&gt; Code &amp; Link')
        ->and($result)->not->toContain('<QR>');
});

test('it inserts the title tag as the very first child of the svg element', function (): void {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg"><g id="qr-data"></g></svg>';

    $component = new QrCodeComponent(data: 'test');
    $result = invade($component)->prepareSvg($svg, new ComponentAttributeBag(['title' => 'Access']));

    $titlePos = strpos($result, '<title>Access</title>');
    $gPos = strpos($result, '<g id="qr-data">');

    expect($titlePos)->toBeLessThan($gPos);
});

test('it accepts scalar and stringable attributes and casts them strictly', function (): void {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg"></svg>';

    $stringableObject = new class implements Stringable
    {
        public function __toString(): string
        {
            return 'custom-value';
        }
    };

    $attributes = [
        'data-int' => 123,
        'data-obj' => $stringableObject,
        'data-null' => null,
    ];

    $component = new QrCodeComponent(data: 'test');
    $result = invade($component)->prepareSvg($svg, new ComponentAttributeBag($attributes));

    expect($result)->toContain('data-int="123"')
        ->and($result)->toContain('data-obj="custom-value"');
});

test('it securely escapes html characters and prepends title as the first child', function (): void {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg"><g id="qr-data"></g></svg>';
    $component = new QrCodeComponent(data: 'test');
    $result = invade($component)->prepareSvg($svg, new ComponentAttributeBag(['title' => 'Malicious < & > Tag']));

    expect($result)->toContain('Malicious &lt; &amp; &gt; Tag')
        ->and($result)->not->toContain('< & >');

    $titlePos = strpos($result, '<title>');
    $gPos = strpos($result, '<g id="qr-data">');
    expect($titlePos)->toBeLessThan($gPos);
});

test('it strictly filters attribute types and prevents casting exceptions', function (): void {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg"></svg>';
    $component = new QrCodeComponent(data: 'test');

    $attributes = [
        'data-valid' => 'standard',
        'data-invalid' => ['nested' => 'array'],
        'data-empty' => null,
    ];

    $result = invade($component)->prepareSvg($svg, new ComponentAttributeBag($attributes));

    expect($result)->toContain('data-valid="standard"')
        ->and($result)->not->toContain('data-invalid');
});

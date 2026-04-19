<?php

declare(strict_types=1);

use Illuminate\View\ComponentAttributeBag;
use Linkxtr\QrCode\Components\QrCodeComponent;
use Linkxtr\QrCode\Facades\QrCode;
use Linkxtr\QrCode\Generator;

covers(QrCodeComponent::class);

beforeEach(function () {
    QrCode::swap(new Generator([]));
});

test('it throws exceptions for invalid formats', function () {
    $component1 = new QrCodeComponent(data: 'test', format: 'invalid');
    expect(fn () => $component1->render())->toThrow(InvalidArgumentException::class, 'Format "invalid" is not supported in the Blade component. Supported HTML embed formats are: svg, png, webp.');

    $component2 = new QrCodeComponent(data: 'test', format: 'eps');
    expect(fn () => $component2->render())->toThrow(InvalidArgumentException::class, 'Format "eps" is not supported in the Blade component. Supported HTML embed formats are: svg, png, webp.');
});

test('it throws exception for path traversal attempts in merge', function () {
    $component = new QrCodeComponent(data: 'test', merge: '../malicious.png');
    expect(fn () => $component->render())->toThrow(InvalidArgumentException::class, 'Invalid merge path, path traversal is not allowed.');
});

test('it generates default svg with injected title and accessibility attributes', function () {
    $component = new QrCodeComponent(data: 'https://linkxtr.com');

    $closure = $component->render();
    $html = $closure(['attributes' => new ComponentAttributeBag(['class' => 'qr-class'])]);

    expect($html)
        ->toStartWith('<?xml')
        ->toContain('<svg')
        ->toContain('class="qr-class"')
        ->toContain('role="img"')
        ->toContain('aria-label="QR Code"')
        ->toContain('<title>QR Code</title>');
});

test('it respects and parses hex colors correctly', function () {
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

test('it ignores incorrectly formatted hex colors safely', function () {
    $component = new QrCodeComponent(
        data: 'test',
        color: '#ZZZZZZ'
    );

    $closure = $component->render();
    $html = $closure(['attributes' => new ComponentAttributeBag([])]);

    expect($html)->toContain('fill="#000000"');
});

test('it generates base64 image tag for raster formats', function () {
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

    $component2 = new QrCodeComponent(
        data: 'test',
        format: 'webp'
    );

    $closure2 = $component2->render();
    $html2 = $closure2(['attributes' => new ComponentAttributeBag(['class' => 'img-class'])]);

    expect($html2)
        ->toStartWith('<img')
        ->toContain('class="img-class"')
        ->toContain('alt="QR Code"')
        ->toContain('src="data:image/webp;base64,');
});

test('it strictly maps all 6 distinct gradient colors to kill array index swap mutants', function () {
    $fakeGenerator = new class
    {
        public array $calls = [];

        public function __call(string $name, array $arguments): self
        {
            $this->calls[$name] = $arguments;

            return $this;
        }

        public function generate(string $data): string
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
        ->and($fakeGenerator->calls['gradient'][0])->toBe(1, 'Start Red swapped!')
        ->and($fakeGenerator->calls['gradient'][1])->toBe(2, 'Start Green swapped!')
        ->and($fakeGenerator->calls['gradient'][2])->toBe(3, 'Start Blue swapped!')
        ->and($fakeGenerator->calls['gradient'][3])->toBe(4, 'End Red swapped!')
        ->and($fakeGenerator->calls['gradient'][4])->toBe(5, 'End Green swapped!')
        ->and($fakeGenerator->calls['gradient'][5])->toBe(6, 'End Blue swapped!')
        ->and($fakeGenerator->calls['gradient'][6])->toBe('diagonal');

    $fakeGenerator->calls = [];

    $componentDefaultType = new QrCodeComponent(
        data: 'https://example.com',
        gradient: '#010203, #040506'
    );

    $componentDefaultType->render()(['attributes' => new ComponentAttributeBag([])]);

    expect($fakeGenerator->calls['gradient'][6])->toBe('vertical');
});

test('it resolves multi color attributes correctly', function () {
    $component = new QrCodeComponent(
        data: 'test',
        eyeColor0: '#FF0000, #00FF00',
        eyeColor1: '255,0,0|0,255,0',
    );
    $closure = $component->render();
    $html = $closure(['attributes' => new ComponentAttributeBag([])]);

    expect($html)->toBeString()->not->toBeEmpty();
});

test('it handles string merges', function () {
    $component = new QrCodeComponent(
        data: 'test',
        mergeString: file_get_contents(__DIR__.'/../../Support/Fixtures/images/linkxtr.png'),
        mergePercentage: 0.3
    );

    $closure = $component->render();
    $html = $closure(['attributes' => new ComponentAttributeBag([])]);

    expect($html)->toBeString()->not->toBeEmpty();
});

test('it handles visual and data modifiers correctly', function () {
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

test('it handles merge with valid path and string absolute boolean', function () {
    $component = new QrCodeComponent(
        data: 'test',
        merge: realpath(__DIR__.'/../../Support/Fixtures/images/linkxtr.png'),
        mergeAbsolute: 'true'
    );

    $closure = $component->render();
    $html = $closure(['attributes' => new ComponentAttributeBag([])]);

    expect($html)->toBeString()->not->toBeEmpty();
});

test('it handles 4-part rgb csv colors with alpha', function () {
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

test('it ignores invalid csv color formats safely', function () {
    $component = new QrCodeComponent(
        data: 'test',
        color: '255, 0',
        backgroundColor: 'invalidstring'
    );

    $closure = $component->render();
    $html = $closure(['attributes' => new ComponentAttributeBag([])]);

    expect($html)->toContain('fill="#000000"');
});

test('it handles the style attribute correctly', function () {
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

test('it dynamically applies styles and modifies the SVG output to kill RemoveMethodCall mutants', function () {
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

test('it dynamically applies encoding and modifies the SVG output', function () {
    $data = 'Café';

    $defaultHtml = (new QrCodeComponent(data: $data))->render()(['attributes' => new ComponentAttributeBag([])]);

    $encodingHtml = (new QrCodeComponent(data: $data, encoding: 'ISO-8859-1'))->render()(['attributes' => new ComponentAttributeBag([])]);

    expect($encodingHtml)->not->toBe($defaultHtml, 'The encoding method was removed or failed to modify the output.');
});

test('it applies custom colors correctly to the SVG output', function () {
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

test('it applies custom eye colors correctly to the SVG output', function () {
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

test('it dynamically delegates config methods to the generator via facade to kill RemoveMethodCall mutants', function () {
    $fakeGenerator = new class
    {
        public array $calls = [];

        public function __call(string $name, array $arguments): self
        {
            $this->calls[$name] = $arguments;

            return $this;
        }

        public function generate(string $data): string
        {
            return '<svg></svg>';
        }
    };

    QrCode::swap($fakeGenerator);

    $component = new QrCodeComponent(
        data: 'https://example.com',
        errorCorrection: 'H',
        eye: 'circle',
        style: 'round',
        encoding: 'ISO-8859-1',
        merge: '/path/to/logo.png',
        mergePercentage: 0.3,
        mergeAbsolute: true
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
        ->and($fakeGenerator->calls['merge'][1])->toBe(0.3)
        ->and($fakeGenerator->calls['merge'][2])->toBeTrue();

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

test('it handles translation escaping and strictly limits svg injection to kill ternary and regex mutants', function () {
    app()->instance('translator', new class
    {
        public function get($key, array $replace = [], $locale = null)
        {
            return $key === 'QR Code' ? 'Mocked Title' : $key;
        }

        public function getFromJson($key, array $replace = [], $locale = null)
        {
            return $this->get($key);
        }

        public function choice($key, $number, array $replace = [], $locale = null)
        {
            return $key;
        }

        public function getLocale()
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

        public function generate(string $data): string
        {
            return '<svg id="1"></svg><svg id="2"></svg>';
        }
    };
    QrCode::swap($fakeGenerator);

    $component = new QrCodeComponent(data: 'test');
    $html = $component->render()(['attributes' => new ComponentAttributeBag(['class' => 'test-class'])]);

    expect($html)->toStartWith('<svg ')
        ->toContain('class="test-class"');
    expect($html)->toContain('<title>Mocked Title</title>');
    expect($html)->toContain('id="1"><title>Mocked Title</title></svg><svg id="2"></svg>');
    expect($html)->not->toContain('<svg id="2"><title>');
});

test('it precisely formats the img tag for non-svg formats to kill concat mutants', function () {
    $fakeGenerator = new class
    {
        public function __call(string $name, array $arguments)
        {
            return $this;
        }

        public function generate(string $data): string
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

test('it safely resolves colors with spaces, 3-parameter csv, and explicit alphas to kill resolution mutants', function () {
    $fakeGenerator = new class
    {
        public array $calls = [];

        public function __call(string $name, array $arguments): self
        {
            $this->calls[$name] = $arguments;

            return $this;
        }

        public function generate(string $data): string
        {
            return '<svg></svg>';
        }
    };
    QrCode::swap($fakeGenerator);

    $component1 = new QrCodeComponent(data: 'test', color: '  #FF0000  ');
    $component1->render()(['attributes' => new ComponentAttributeBag([])]);

    expect($fakeGenerator->calls)->toHaveKey('color')
        ->and($fakeGenerator->calls['color'])->toBe([255, 0, 0, null]);

    $fakeGenerator->calls = [];

    $component2 = new QrCodeComponent(data: 'test', backgroundColor: '0, 255, 0');
    $component2->render()(['attributes' => new ComponentAttributeBag([])]);

    expect($fakeGenerator->calls)->toHaveKey('backgroundColor')
        ->and($fakeGenerator->calls['backgroundColor'])->toBe([0, 255, 0, null]);

    $fakeGenerator->calls = [];

    $component3 = new QrCodeComponent(data: 'test', color: '0, 0, 255, 100');
    $component3->render()(['attributes' => new ComponentAttributeBag([])]);

    expect($fakeGenerator->calls)->toHaveKey('color')
        ->and($fakeGenerator->calls['color'])->toBe([0, 0, 255, null]);
});

test('it aggressively resolves multi-color strings across different delimiters to kill str_replace mutants', function () {
    $fakeGenerator = new class
    {
        public array $calls = [];

        public function __call(string $name, array $arguments): self
        {
            $this->calls[$name] = $arguments;

            return $this;
        }

        public function generate(string $data): string
        {
            return '<svg></svg>';
        }
    };
    QrCode::swap($fakeGenerator);

    (new QrCodeComponent(data: 'test', eyeColor0: '#010203;#040506'))
        ->render()(['attributes' => new ComponentAttributeBag([])]);

    expect($fakeGenerator->calls['eyeColor'][0])->toBe(0);
    expect($fakeGenerator->calls['eyeColor'][1])->toBe(1);
    expect($fakeGenerator->calls['eyeColor'][2])->toBe(2);
    expect($fakeGenerator->calls['eyeColor'][3])->toBe(3);
    expect($fakeGenerator->calls['eyeColor'][4])->toBe(4);
    expect($fakeGenerator->calls['eyeColor'][5])->toBe(5);
    expect($fakeGenerator->calls['eyeColor'][6])->toBe(6);

    $fakeGenerator->calls = [];

    (new QrCodeComponent(data: 'test', eyeColor1: '#010203, #040506'))
        ->render()(['attributes' => new ComponentAttributeBag([])]);

    expect($fakeGenerator->calls['eyeColor'][1])->toBe(1);
    expect($fakeGenerator->calls['eyeColor'][2])->toBe(2);
    expect($fakeGenerator->calls['eyeColor'][3])->toBe(3);
    expect($fakeGenerator->calls['eyeColor'][4])->toBe(4);
    expect($fakeGenerator->calls['eyeColor'][5])->toBe(5);
    expect($fakeGenerator->calls['eyeColor'][6])->toBe(6);

    $fakeGenerator->calls = [];

    (new QrCodeComponent(data: 'test', eyeColor2: '#010203,#040506'))
        ->render()(['attributes' => new ComponentAttributeBag([])]);

    expect($fakeGenerator->calls['eyeColor'][2])->toBe(2);
    expect($fakeGenerator->calls['eyeColor'][3])->toBe(3);
    expect($fakeGenerator->calls['eyeColor'][4])->toBe(4);
    expect($fakeGenerator->calls['eyeColor'][5])->toBe(5);
    expect($fakeGenerator->calls['eyeColor'][6])->toBe(6);
});

test('it duplicates single multi-colors and safely slices excess colors to kill logic mutants', function () {
    $fakeGenerator = new class
    {
        public array $calls = [];

        public function __call(string $name, array $arguments): self
        {
            $this->calls[$name] = $arguments;

            return $this;
        }

        public function generate(string $data): string
        {
            return '<svg></svg>';
        }
    };
    QrCode::swap($fakeGenerator);

    (new QrCodeComponent(data: 'test', gradient: '#010203'))
        ->render()(['attributes' => new ComponentAttributeBag([])]);

    expect($fakeGenerator->calls['gradient'])->toBe([1, 2, 3, 1, 2, 3, 'vertical']);

    $fakeGenerator->calls = [];

    (new QrCodeComponent(data: 'test', gradient: '#010203|#040506|#070809'))
        ->render()(['attributes' => new ComponentAttributeBag([])]);

    expect($fakeGenerator->calls['gradient'])->toBe([1, 2, 3, 4, 5, 6, 'vertical']);
});

test('it returns null early if no valid colors could be parsed to cover the empty array return', function () {
    $fakeGenerator = new class
    {
        public array $calls = [];

        public function __call(string $name, array $arguments): self
        {
            $this->calls[$name] = $arguments;

            return $this;
        }

        public function generate(string $data): string
        {
            return '<svg></svg>';
        }
    };
    QrCode::swap($fakeGenerator);

    $component = new QrCodeComponent(
        data: 'test',
        gradient: 'invalid_color_1|invalid_color_2',
        eyeColor0: '#ZZZ'
    );

    $component->render()(['attributes' => new ComponentAttributeBag([])]);

    expect($fakeGenerator->calls)->not->toHaveKey('gradient');
    expect($fakeGenerator->calls)->not->toHaveKey('eyeColor0');
});

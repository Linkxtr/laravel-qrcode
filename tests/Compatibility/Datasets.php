<?php

dataset('supported_methods', [
    'Size' => ['size', 250],
    'Color' => ['color', [0, 0, 0]],
    'Background' => ['backgroundColor', [255, 255, 255]],
    'Margin' => ['margin', 1],
    'Encoding' => ['encoding', 'UTF-8'],
    'Style' => ['style', 'dot'],
    'Eye style' => ['eye', 'square'],
    'Format' => ['format', 'svg'],
]);

dataset('content', [
    'Text' => 'Simple text',
    'URL' => 'https://example.com',
    'Numbers' => '1234567890',
    'Special Characters' => 'Special chars: !@#$%^&*()',
    'Multibyte' => 'Multibyte: ñáéíóú',
    'JSON' => 'JSON: {"name":"test","value":123}',
]);

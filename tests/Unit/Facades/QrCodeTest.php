<?php

declare(strict_types=1);

use Linkxtr\QrCode\Facades\QrCode;
use Linkxtr\QrCode\Generator;

covers(QrCode::class);

test('the facade resolves the generator instance from the container', function (): void {
    $instance = QrCode::getFacadeRoot();

    expect($instance)->toBeInstanceOf(Generator::class);
});

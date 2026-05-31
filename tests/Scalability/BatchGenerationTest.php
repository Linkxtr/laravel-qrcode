<?php

declare(strict_types=1);

use Linkxtr\QrCode\Facades\QrCode;

it('can generate multiple qr codes in a loop without state leakage', function (): void {
    $qr1 = (string) QrCode::size(500)->color(255, 0, 0)->generate('Payload A');
    $qr2 = (string) QrCode::generate('Payload B');
    expect($qr1)->not->toBe($qr2);
    expect($qr1)->toContain('width="500"');
    expect($qr2)->toContain('width="400"');
});

it('can handle bulk generation without memory exhaustion', function (): void {
    $results = [];

    for ($i = 0; $i < 50; ++$i) {
        $results[] = (string) QrCode::format('svg')
            ->errorCorrection('L')
            ->generate('Batch Item '.$i);
    }

    expect($results)->toHaveCount(50);
    $uniqueResults = array_unique($results);
    expect($uniqueResults)->toHaveCount(50);
});

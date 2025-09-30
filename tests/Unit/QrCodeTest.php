<?php

declare(strict_types=1);

use Linkxtr\QrCode\Facades\QrCode;

it('can be initialized', function () {
    expect(QrCode::class)->toBeClass();
});

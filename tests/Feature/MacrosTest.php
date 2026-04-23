<?php

declare(strict_types=1);

use Illuminate\Support\HtmlString;
use Linkxtr\QrCode\Facades\QrCode;
use Linkxtr\QrCode\Generator;

afterEach(function () {
    Generator::flushMacros();
});

test('developers can register and execute custom macros on the QrCode facade', function () {
    QrCode::macro('myCustomTicket', function (string $eventId, int $userId) {
        $payload = "TICKET|{$eventId}|{$userId}";

        return $this->size(300)->margin(2)->generate($payload);
    });

    $result = QrCode::myCustomTicket('EVT-999', 12345);

    expect($result)->toBeInstanceOf(HtmlString::class);
    expect((string) $result)->toContain('<svg')
        ->toContain('width="300"');
});

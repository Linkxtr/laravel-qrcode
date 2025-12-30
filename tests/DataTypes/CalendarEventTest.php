<?php

use Illuminate\Support\HtmlString;
use Linkxtr\QrCode\DataTypes\CalendarEvent;
use Linkxtr\QrCode\Generator;

test('it should generate a valid calendar event QR code', function () {
    $qrCode = new Generator;
    $event = [
        'summary' => 'Team Meeting',
        'description' => 'Weekly team sync',
        'location' => 'Conference Room A',
        'start' => '2024-06-01 10:00:00',
        'end' => '2024-06-01 11:00:00',
    ];

    // Using PascalCase to match class name as per current Generator implementation
    $generated = $qrCode->CalendarEvent($event);

    expect($generated)->toBeInstanceOf(HtmlString::class);
});

test('CalendarEvent class generates correct string', function () {
    $calendarEvent = new CalendarEvent;
    $calendarEvent->create([[
        'summary' => 'Team Meeting',
        'description' => 'Weekly team sync',
        'location' => 'Conference Room A',
        'start' => '2024-06-01 10:00:00',
        'end' => '2024-06-01 11:00:00',
    ]]);

    $string = (string) $calendarEvent;

    expect($string)->toContain('BEGIN:VEVENT');
    expect($string)->toContain('SUMMARY:Team Meeting');
    expect($string)->toContain('DESCRIPTION:Weekly team sync');
    expect($string)->toContain('LOCATION:Conference Room A');
    expect($string)->toContain('DTSTART:20240601T100000Z');
    expect($string)->toContain('DTEND:20240601T110000Z');
    expect($string)->toContain('END:VEVENT');
});

test('CalendarEvent class escapes special characters', function () {
    $calendarEvent = new CalendarEvent;
    $calendarEvent->create([[
        'summary' => 'Team, Meeting; (Internal) \\',
        'description' => "Line 1\nLine 2",
        'location' => 'Room A, B',
        'start' => '2024-06-01 10:00:00',
        'end' => '2024-06-01 11:00:00',
    ]]);

    $string = (string) $calendarEvent;

    expect($string)->toContain('SUMMARY:Team\, Meeting\; (Internal) \\\\');
    expect($string)->toContain('DESCRIPTION:Line 1\nLine 2');
    expect($string)->toContain('LOCATION:Room A\, B');
});

test('CalendarEvent class generates correct string with minimal data', function () {
    $calendarEvent = new CalendarEvent;
    $calendarEvent->create([[
        'summary' => 'Team Meeting',
        'start' => '2024-06-01 10:00:00',
        'end' => '2024-06-01 11:00:00',
    ]]);

    $string = (string) $calendarEvent;

    expect($string)->toContain('BEGIN:VEVENT');
    expect($string)->toContain('SUMMARY:Team Meeting');
    expect($string)->not->toContain('DESCRIPTION:');
    expect($string)->not->toContain('LOCATION:');
    expect($string)->toContain('DTSTART:20240601T100000Z');
    expect($string)->toContain('DTEND:20240601T110000Z');
    expect($string)->toContain('END:VEVENT');
});

test('it throws exception when attributes are not an array', function () {
    $qrCode = new Generator;
    $qrCode->CalendarEvent('invalid');
})->throws(InvalidArgumentException::class, 'Invalid CalendarEvent arguments.');

test('it throws exception when summary is missing', function () {
    $qrCode = new Generator;
    $qrCode->CalendarEvent([
        'start' => '2024-06-01 10:00:00',
        'end' => '2024-06-01 11:00:00',
    ]);
})->throws(InvalidArgumentException::class, 'Summary is required and must be a string.');

test('it throws exception when summary is empty', function () {
    $qrCode = new Generator;
    $qrCode->CalendarEvent([
        'summary' => '',
        'start' => '2024-06-01 10:00:00',
        'end' => '2024-06-01 11:00:00',
    ]);
})->throws(InvalidArgumentException::class, 'Summary is required and must be a string.');

test('it throws exception when start date is missing', function () {
    $qrCode = new Generator;
    $qrCode->CalendarEvent([
        'summary' => 'Meeting',
        'end' => '2024-06-01 11:00:00',
    ]);
})->throws(InvalidArgumentException::class, 'Start date is required.');

test('it throws exception when end date is missing', function () {
    $qrCode = new Generator;
    $qrCode->CalendarEvent([
        'summary' => 'Meeting',
        'start' => '2024-06-01 10:00:00',
    ]);
})->throws(InvalidArgumentException::class, 'End date is required.');

test('it throws exception when start date is invalid', function () {
    $qrCode = new Generator;
    $qrCode->CalendarEvent([
        'summary' => 'Meeting',
        'start' => new stdClass,
        'end' => '2024-06-01 11:00:00',
    ]);
})->throws(InvalidArgumentException::class, 'Date must be a string, numeric or DateTimeInterface.');

test('it throws exception when end date is invalid', function () {
    $qrCode = new Generator;
    $qrCode->CalendarEvent([
        'summary' => 'Meeting',
        'start' => '2024-06-01 10:00:00',
        'end' => new stdClass,
    ]);
})->throws(InvalidArgumentException::class, 'Date must be a string, numeric or DateTimeInterface.');

test('it ignores non-string description and location', function () {
    $calendarEvent = new CalendarEvent;
    $calendarEvent->create([[
        'summary' => 'Meeting',
        'start' => '2024-06-01 10:00:00',
        'end' => '2024-06-01 11:00:00',
        'description' => 123,
        'location' => ['array'],
    ]]);

    $string = (string) $calendarEvent;

    expect($string)->not->toContain('DESCRIPTION:');
    expect($string)->not->toContain('LOCATION:');
});

test('it accepts DateTimeInterface and numeric dates', function () {
    $calendarEvent = new CalendarEvent;
    $now = new DateTime;
    $tomorrow = time() + 86400;

    $calendarEvent->create([[
        'summary' => 'Meeting',
        'start' => $now,
        'end' => $tomorrow,
    ]]);

    $string = (string) $calendarEvent;

    expect($string)->toContain('DTSTART:'.(clone $now)->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis\Z'));
    expect($string)->toContain('DTEND:'.(new DateTime('@'.$tomorrow))->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis\Z'));
});

test('it throws exception when end date is before start date', function () {
    $qrCode = new Generator;
    $qrCode->CalendarEvent([
        'summary' => 'Meeting',
        'start' => '2024-06-01 12:00:00',
        'end' => '2024-06-01 11:00:00',
    ]);
})->throws(InvalidArgumentException::class, 'End date must be after start date.');

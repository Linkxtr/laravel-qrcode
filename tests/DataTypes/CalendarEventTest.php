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
    expect($string)->toContain('DTSTART:20240601T100000');
    expect($string)->toContain('DTEND:20240601T110000');
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
    expect($string)->toContain('DTSTART:20240601T100000');
    expect($string)->toContain('DTEND:20240601T110000');
    expect($string)->toContain('END:VEVENT');
});

test('it throws exception when required fields are missing', function () {
    $qrCode = new Generator;
    // Missing start
    $qrCode->CalendarEvent([
        'summary' => 'Team Meeting',
        'end' => '2024-06-01 11:00:00',
    ]);
})->throws(InvalidArgumentException::class);

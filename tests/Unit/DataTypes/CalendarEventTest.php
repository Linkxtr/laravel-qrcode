<?php

declare(strict_types=1);

use Carbon\Carbon;
use Linkxtr\QrCode\DataTypes\CalendarEvent;

covers(CalendarEvent::class);

test('it throws exception if rendered before creation', function () {
    $event = new CalendarEvent;
    expect(fn () => (string) $event)
        ->toThrow(LogicException::class, 'CalendarEvent must be initialized via create() before rendering.');
});

test('it throws exception if arguments are not an array', function () {
    $event = new CalendarEvent;
    expect(fn () => $event->create(['not-an-array']))
        ->toThrow(InvalidArgumentException::class, 'Invalid CalendarEvent arguments.');
});

test('it throws exception if summary is missing, invalid type, or empty', function () {
    $event = new CalendarEvent;

    expect(fn () => $event->create([[]]))
        ->toThrow(InvalidArgumentException::class, 'Summary is required and must be a string.');

    expect(fn () => $event->create([['summary' => 123]]))
        ->toThrow(InvalidArgumentException::class, 'Summary is required and must be a string.');

    expect(fn () => $event->create([['summary' => '']]))
        ->toThrow(InvalidArgumentException::class, 'Summary is required and must be a string.');
});

test('it throws exception if start date is missing', function () {
    $event = new CalendarEvent;
    expect(fn () => $event->create([['summary' => 'Meeting']]))
        ->toThrow(InvalidArgumentException::class, 'Start date is required.');
});

test('it throws exception if end date is missing', function () {
    $event = new CalendarEvent;
    expect(fn () => $event->create([['summary' => 'Meeting', 'start' => '2023-01-01']]))
        ->toThrow(InvalidArgumentException::class, 'End date is required.');
});

test('it strictly enforces end date must be after start date', function () {
    $event = new CalendarEvent;

    expect(fn () => $event->create([
        ['summary' => 'Meeting', 'start' => '2023-01-01 12:00:00', 'end' => '2023-01-01 12:00:00'],
    ]))->toThrow(InvalidArgumentException::class, 'End date must be after start date.');

    expect(fn () => $event->create([
        ['summary' => 'Meeting', 'start' => '2023-01-01 12:00:00', 'end' => '2023-01-01 11:59:59'],
    ]))->toThrow(InvalidArgumentException::class, 'End date must be after start date.');
});

test('it successfully parses numeric timestamps and DateTimeInterfaces', function () {
    $event = new CalendarEvent;
    $dateTime = new DateTime('2023-01-01 14:00:00');

    $event->create([
        ['summary' => 'Meeting', 'start' => 1672574400, 'end' => $dateTime],
    ]);

    expect(invade($event)->start->timestamp)->toBe(1672574400)
        ->and(invade($event)->end->format('Y-m-d H:i:s'))->toBe('2023-01-01 14:00:00');
});

test('it throws exception for invalid date types', function () {
    $event = new CalendarEvent;
    expect(fn () => $event->create([
        ['summary' => 'Meeting', 'start' => ['invalid-type'], 'end' => '2023-01-01'],
    ]))->toThrow(InvalidArgumentException::class, 'Date must be a string, numeric or DateTimeInterface.');
});

test('it generates full formatted calendar string and strictly preserves timezones', function () {
    Carbon::setTestNow(Carbon::parse('2023-10-15 08:30:00', 'UTC'));

    $event = new CalendarEvent;
    $event->create([[
        'summary' => 'Main Meeting',
        'start' => '2023-12-01 12:00:00+02:00',
        'end' => '2023-12-01 14:00:00+02:00',
        'description' => 'Optional Desc',
        'location' => 'Optional Loc',
    ]]);

    $uid = invade($event)->uid;

    $expectedString = implode("\r\n", [
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'PRODID:-//Linkxtr//LaravelQrCode//EN',
        'BEGIN:VEVENT',
        'UID:'.$uid,
        'DTSTAMP:20231015T083000Z',
        'SUMMARY:Main Meeting',
        'DESCRIPTION:Optional Desc',
        'LOCATION:Optional Loc',
        'DTSTART:20231201T100000Z',
        'DTEND:20231201T120000Z',
        'END:VEVENT',
        'END:VCALENDAR',
        '',
    ]);

    expect((string) $event)->toBe($expectedString);

    expect(invade($event)->start->getTimezone()->getName())->toBe('+02:00');
});

test('it ignores optional parameters if they are not strings', function () {
    $event = new CalendarEvent;
    $event->create([[
        'summary' => 'Meeting',
        'start' => '2023-12-01 12:00:00',
        'end' => '2023-12-01 14:00:00',
        'description' => 12345,
        'location' => null,
    ]]);

    $result = (string) $event;
    expect($result)->not->toContain('DESCRIPTION:')
        ->and($result)->not->toContain('LOCATION:');
});

test('it rigorously escapes special characters in strings to kill formatting mutants', function () {
    $event = new CalendarEvent;

    $chaosString = "Line1\\Line2,Line3;Line4\r\nLine5\nLine6\rLine7";

    $event->create([[
        'summary' => $chaosString,
        'start' => '2023-12-01 12:00:00',
        'end' => '2023-12-01 14:00:00',
    ]]);

    $result = (string) $event;

    $expectedSummary = 'SUMMARY:Line1\\\\Line2\,Line3\;Line4\nLine5\nLine6\nLine7';

    expect($result)->toContain($expectedSummary);
});

test('it generates a valid structured UID to kill concatenation and entropy mutants', function () {
    $event = new CalendarEvent;
    $event->create([[
        'summary' => 'Meeting',
        'start' => '2023-12-01 12:00:00',
        'end' => '2023-12-01 14:00:00',
    ]]);

    $uid = invade($event)->uid;

    expect($uid)->toEndWith('@linkxtr-qrcode')
        ->and($uid)->not->toStartWith('@linkxtr-qrcode');
    $uniquePart = str_replace('@linkxtr-qrcode', '', $uid);

    expect(strlen($uniquePart))->toBe(23);
});

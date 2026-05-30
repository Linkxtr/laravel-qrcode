<?php

declare(strict_types=1);

use Carbon\Carbon;
use Linkxtr\QrCode\DataTypes\CalendarEvent;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidCalendarEventArgumentException;

covers(CalendarEvent::class);

afterEach(fn () => Carbon::setTestNow());

test('it throws exception if summary is missing, invalid type, or empty', function (): void {
    expect(fn (): CalendarEvent => new CalendarEvent(summary: '', start: '2023-01-01', end: '2023-01-02'))
        ->toThrow(InvalidCalendarEventArgumentException::class, 'The summary must be a non-empty string.');

    expect(fn (): CalendarEvent => new CalendarEvent(start: '2023-01-01', end: '2023-01-02'))
        ->toThrow(TypeError::class);
});

test('it throws exception if start date is missing', function (): void {
    expect(fn (): CalendarEvent => new CalendarEvent(summary: 'Meeting', end: '2023-01-02'))
        ->toThrow(TypeError::class);
});

test('it throws exception if end date is missing', function (): void {
    expect(fn (): CalendarEvent => new CalendarEvent(summary: 'Meeting', start: '2023-01-01'))
        ->toThrow(TypeError::class);
});

test('it strictly enforces end date must be after start date', function (): void {
    expect(fn (): CalendarEvent => new CalendarEvent(
        summary: 'Meeting', start: '2023-01-01 12:00:00', end: '2023-01-01 12:00:00'
    ))->toThrow(InvalidCalendarEventArgumentException::class, 'The end date must be after the start date.');

    expect(fn (): CalendarEvent => new CalendarEvent(
        summary: 'Meeting', start: '2023-01-01 12:00:00', end: '2023-01-01 11:59:59'
    ))->toThrow(InvalidCalendarEventArgumentException::class, 'The end date must be after the start date.');
});

test('it successfully parses numeric timestamps and DateTimeInterfaces', function (): void {
    $dateTime = Carbon::parse('2023-01-01 14:00:00');

    $event = new CalendarEvent(
        summary: 'Meeting', start: 1672574400, end: $dateTime
    );

    expect(invade($event)->startParsed->timestamp)->toBe(1672574400)
        ->and(invade($event)->endParsed->format('Y-m-d H:i:s'))->toBe('2023-01-01 14:00:00');
});

test('it throws exception for invalid date types', function (): void {
    expect(fn (): CalendarEvent => new CalendarEvent(
        summary: 'Meeting', start: ['invalid-type'], end: '2023-01-01'
    ))->toThrow(TypeError::class);
    expect(fn (): CalendarEvent => new CalendarEvent(
        summary: 'Meeting', start: 'invalid-type', end: '2023-01-01'
    ))->toThrow(InvalidCalendarEventArgumentException::class);
});

test('it generates full formatted calendar string and strictly preserves timezones', function (): void {
    Carbon::setTestNow(Carbon::parse('2023-10-15 08:30:00', 'UTC'));

    $event = new CalendarEvent(
        summary: 'Main Meeting',
        start: '2023-12-01 12:00:00+02:00',
        end: '2023-12-01 14:00:00+02:00',
        description: 'Optional Desc',
        location: 'Optional Loc'
    );

    $uid = invade($event)->uidValue;

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

    expect(invade($event)->startParsed->getTimezone()->getName())->toBe('+02:00');
});

test('it rigorously escapes special characters in strings', function (): void {
    $chaosString = "Line1\\Line2,Line3;Line4\r\nLine5\nLine6\rLine7";

    $event = new CalendarEvent(
        summary: $chaosString,
        start: '2023-12-01 12:00:00',
        end: '2023-12-01 14:00:00'
    );

    $result = (string) $event;

    $expectedSummary = 'SUMMARY:Line1\\\\Line2\,Line3\;Line4\nLine5\nLine6\nLine7';

    expect($result)->toContain($expectedSummary);
});

test('it generates a valid structured UID', function (): void {
    $event = new CalendarEvent(
        summary: 'Meeting',
        start: '2023-12-01 12:00:00',
        end: '2023-12-01 14:00:00'
    );

    $uid = invade($event)->uidValue;

    expect($uid)->toEndWith('@linkxtr-qrcode')
        ->and($uid)->not->toStartWith('@linkxtr-qrcode');
    $uniquePart = str_replace('@linkxtr-qrcode', '', $uid);

    expect(strlen($uniquePart))->toBe(40);
});

test('it generates deterministic UIDs for identical events', function (): void {
    $event1 = new CalendarEvent(
        summary: 'Team Sync',
        start: '2024-01-01 10:00:00',
        end: '2024-01-01 11:00:00'
    );

    $event2 = new CalendarEvent(
        summary: 'Team Sync',
        start: '2024-01-01 10:00:00',
        end: '2024-01-01 11:00:00'
    );

    expect(invade($event1)->uidValue)->toBe(invade($event2)->uidValue);
});

test('it allows custom UIDs to be passed', function (): void {
    $event = new CalendarEvent(
        summary: 'Meeting',
        start: '2023-12-01 12:00:00',
        end: '2023-12-01 14:00:00',
        uid: 'custom-uuid-12345'
    );

    expect(invade($event)->uidValue)->toBe('custom-uuid-12345');
});

test('the generated UID changes if any core event detail changes', function (): void {
    $baseEvent = new CalendarEvent(
        summary: 'Core Meeting',
        start: '2024-01-01 10:00:00',
        end: '2024-01-01 11:00:00',
        description: 'Core Description',
        location: 'Core Location'
    );

    $baseUid = invade($baseEvent)->uidValue;

    $diffSummary = new CalendarEvent(
        summary: 'Different Meeting',
        start: '2024-01-01 10:00:00',
        end: '2024-01-01 11:00:00',
        description: 'Core Description',
        location: 'Core Location'
    );

    expect(invade($diffSummary)->uidValue)->not->toBe($baseUid);

    $diffStart = new CalendarEvent(
        summary: 'Core Meeting',
        start: '2024-01-01 09:00:00',
        end: '2024-01-01 11:00:00',
        description: 'Core Description',
        location: 'Core Location'
    );

    expect(invade($diffStart)->uidValue)->not->toBe($baseUid);

    $diffEnd = new CalendarEvent(
        summary: 'Core Meeting',
        start: '2024-01-01 10:00:00',
        end: '2024-01-01 12:00:00',
        description: 'Core Description',
        location: 'Core Location'
    );

    expect(invade($diffEnd)->uidValue)->not->toBe($baseUid);

    $diffDesc = new CalendarEvent(
        summary: 'Core Meeting',
        start: '2024-01-01 10:00:00',
        end: '2024-01-01 11:00:00',
        description: 'Different Description',
        location: 'Core Location'
    );

    expect(invade($diffDesc)->uidValue)->not->toBe($baseUid);

    $diffLoc = new CalendarEvent(
        summary: 'Core Meeting',
        start: '2024-01-01 10:00:00',
        end: '2024-01-01 11:00:00',
        description: 'Core Description',
        location: 'Different Location'
    );

    expect(invade($diffLoc)->uidValue)->not->toBe($baseUid);
});

test('it ignores an empty string custom UID and falls back to generated hash', function (): void {
    $event = new CalendarEvent(
        summary: 'Meeting',
        start: '2023-12-01 12:00:00',
        end: '2023-12-01 14:00:00',
        uid: ''
    );

    $uid = invade($event)->uidValue;

    expect($uid)->not->toBe('')
        ->and($uid)->toEndWith('@linkxtr-qrcode')
        ->and(strlen(str_replace('@linkxtr-qrcode', '', $uid)))->toBe(40);
});

test('the generated UID uses the exact concatenation order of summary, start, and end', function (): void {
    $event = new CalendarEvent(
        summary: 'ExactOrderTest',
        start: '2024-01-01 10:00:00',
        end: '2024-01-01 11:00:00',
        description: 'Core Description',
        location: 'Core Location'
    );

    $invaded = invade($event);

    $expectedStringToHash = 'ExactOrderTest'.$invaded->startParsed->timestamp.$invaded->endParsed->timestamp.$invaded->description.$invaded->location;

    $expectedUid = sha1($expectedStringToHash).'@linkxtr-qrcode';
    expect($invaded->uidValue)->toBe($expectedUid);
});

it('ignores empty description and location', function (): void {
    $event = new CalendarEvent(
        summary: 'Meeting',
        start: '2023-12-01 12:00:00',
        end: '2023-12-01 14:00:00',
        description: '',
        location: '',
    );

    expect((string) $event)->not->toContain('DESCRIPTION:')
        ->and((string) $event)->not->toContain('LOCATION:');
});

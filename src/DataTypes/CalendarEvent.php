<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use Carbon\Carbon;
use DateTimeInterface;
use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\DataTypeInterface;
use LogicException;

final class CalendarEvent implements DataTypeInterface
{
    private string $summary;

    private ?string $description = null;

    private ?string $location = null;

    private Carbon $start;

    private Carbon $end;

    private string $uid = '';

    public function __toString(): string
    {
        if ($this->uid === '') {
            throw new LogicException('CalendarEvent must be initialized via create() before rendering.');
        }

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Linkxtr//LaravelQrCode//EN',
            'BEGIN:VEVENT',
            'UID:'.$this->uid,
            'DTSTAMP:'.Carbon::now()->utc()->format('Ymd\THis\Z'),
            'SUMMARY:'.$this->formatProperty($this->summary),
        ];

        if ($this->description !== null) {
            $lines[] = 'DESCRIPTION:'.$this->formatProperty($this->description);
        }

        if ($this->location !== null) {
            $lines[] = 'LOCATION:'.$this->formatProperty($this->location);
        }

        $lines[] = 'DTSTART:'.$this->start->copy()->utc()->format('Ymd\THis\Z');
        $lines[] = 'DTEND:'.$this->end->copy()->utc()->format('Ymd\THis\Z');
        $lines[] = 'END:VEVENT';
        $lines[] = 'END:VCALENDAR';
        $lines[] = ''; // Required to generate the final trailing \r\n

        return implode("\r\n", $lines);
    }

    /**
     * @param  array<int, mixed>  $arguments
     */
    public function create(array $arguments): void
    {
        $attributes = $arguments[0] ?? [];

        if (! is_array($attributes)) {
            throw new InvalidArgumentException('Invalid CalendarEvent arguments.');
        }

        if (! isset($attributes['summary']) || ! is_string($attributes['summary']) || $attributes['summary'] === '') {
            throw new InvalidArgumentException('Summary is required and must be a string.');
        }

        if (! isset($attributes['start'])) {
            throw new InvalidArgumentException('Start date is required.');
        }

        $start = $this->parseDate($attributes['start']);

        if (! isset($attributes['end'])) {
            throw new InvalidArgumentException('End date is required.');
        }

        $end = $this->parseDate($attributes['end']);

        if ($end <= $start) {
            throw new InvalidArgumentException('End date must be after start date.');
        }

        $this->uid = uniqid('', true).'@linkxtr-qrcode';
        $this->summary = $attributes['summary'];
        $this->start = $start;
        $this->end = $end;

        $this->description = (isset($attributes['description']) && is_string($attributes['description']))
            ? $attributes['description']
            : null;

        $this->location = (isset($attributes['location']) && is_string($attributes['location']))
            ? $attributes['location']
            : null;
    }

    private function parseDate(mixed $date): Carbon
    {
        if (is_string($date) || is_numeric($date) || $date instanceof DateTimeInterface) {
            return Carbon::parse($date);
        }

        throw new InvalidArgumentException('Date must be a string, numeric or DateTimeInterface.');
    }

    private function formatProperty(string $value): string
    {
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace(';', '\;', $value);
        $value = str_replace(',', '\,', $value);

        return str_replace(["\r\n", "\r", "\n"], '\\n', $value);
    }
}

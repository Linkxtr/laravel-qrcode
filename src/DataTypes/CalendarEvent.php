<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use Carbon\Carbon;
use DateTimeInterface;
use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\Exceptions\InvalidCalenderEventArgumentException;
use Linkxtr\QrCode\Exceptions\UninitializedDataTypeException;

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
            throw UninitializedDataTypeException::forType('Calendar event');
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
            throw InvalidCalenderEventArgumentException::invalidArgument('Invalid CalendarEvent arguments.');
        }

        if (! isset($attributes['summary'])) {
            throw InvalidCalenderEventArgumentException::missingArguments('Summary is required.');
        }

        if (! is_string($attributes['summary']) || $attributes['summary'] === '') {
            throw InvalidCalenderEventArgumentException::invalidSummary(gettype($attributes['summary']));
        }

        if (! isset($attributes['start'])) {
            throw InvalidCalenderEventArgumentException::missingArguments('Start date is required.');
        }

        $start = $this->parseDate($attributes['start']);

        if (! isset($attributes['end'])) {
            throw InvalidCalenderEventArgumentException::missingArguments('End date is required.');
        }

        $end = $this->parseDate($attributes['end']);

        if ($end <= $start) {
            throw InvalidCalenderEventArgumentException::endDateMustBeAfterStartDate();
        }

        $this->summary = $attributes['summary'];
        $this->start = $start;
        $this->end = $end;

        $this->description = (isset($attributes['description']) && is_string($attributes['description']))
            ? $attributes['description']
            : null;

        $this->location = (isset($attributes['location']) && is_string($attributes['location']))
            ? $attributes['location']
            : null;

        $this->uid = (isset($attributes['uid']) && is_string($attributes['uid']) && $attributes['uid'] !== '')
            ? $attributes['uid']
            : sha1($this->summary.$this->start->timestamp.$this->end->timestamp.$this->description.$this->location).'@linkxtr-qrcode';
    }

    private function parseDate(mixed $date): Carbon
    {
        if (is_string($date) || is_numeric($date) || $date instanceof DateTimeInterface) {
            return Carbon::parse($date);
        }

        throw InvalidCalenderEventArgumentException::invalidDate(gettype($date));
    }

    private function formatProperty(string $value): string
    {
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace(';', '\;', $value);
        $value = str_replace(',', '\,', $value);

        return str_replace(["\r\n", "\r", "\n"], '\\n', $value);
    }
}

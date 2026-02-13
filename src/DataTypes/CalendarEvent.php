<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use Carbon\Carbon;
use DateTimeInterface;
use InvalidArgumentException;
use Linkxtr\QrCode\Contracts\DataTypeInterface;

final class CalendarEvent implements DataTypeInterface
{
    private string $summary;

    private ?string $description = null;

    private ?string $location = null;

    private Carbon $start;

    private Carbon $end;

    public function __toString(): string
    {
        $event = "BEGIN:VCALENDAR\r\n";
        $event .= "VERSION:2.0\r\n";
        $event .= "PRODID:-//Linkxtr//LaravelQrCode//EN\r\n";
        $event .= "BEGIN:VEVENT\r\n";
        $event .= 'UID:'.uniqid('', true).'@linkxtr-qrcode'."\r\n";
        $event .= 'DTSTAMP:'.Carbon::now()->utc()->format('Ymd\THis\Z')."\r\n";
        $event .= 'SUMMARY:'.$this->formatProperty($this->summary)."\r\n";

        if ($this->description) {
            $event .= 'DESCRIPTION:'.$this->formatProperty($this->description)."\r\n";
        }

        if ($this->location) {
            $event .= 'LOCATION:'.$this->formatProperty($this->location)."\r\n";
        }

        $event .= 'DTSTART:'.$this->start->utc()->format('Ymd\THis\Z')."\r\n";
        $event .= 'DTEND:'.$this->end->utc()->format('Ymd\THis\Z')."\r\n";
        $event .= "END:VEVENT\r\n";

        return $event . "END:VCALENDAR\r\n";
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
        $this->summary = $attributes['summary'];

        if (! isset($attributes['start'])) {
            throw new InvalidArgumentException('Start date is required.');
        }
        $this->start = $this->parseDate($attributes['start']);

        if (! isset($attributes['end'])) {
            throw new InvalidArgumentException('End date is required.');
        }
        $this->end = $this->parseDate($attributes['end']);

        if ($this->end <= $this->start) {
            throw new InvalidArgumentException('End date must be after start date.');
        }

        if (isset($attributes['description']) && is_string($attributes['description'])) {
            $this->description = $attributes['description'];
        }

        if (isset($attributes['location']) && is_string($attributes['location'])) {
            $this->location = $attributes['location'];
        }
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

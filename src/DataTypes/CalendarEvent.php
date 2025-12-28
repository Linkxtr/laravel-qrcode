<?php

namespace Linkxtr\QrCode\DataTypes;

use Carbon\Carbon;
use DateTimeInterface;
use InvalidArgumentException;

class CalendarEvent implements DataTypeInterface
{
    protected string $summary;

    protected ?string $description = null;

    protected ?string $location = null;

    protected Carbon $start;

    protected Carbon $end;

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

        if (isset($attributes['description']) && is_string($attributes['description'])) {
            $this->description = $attributes['description'];
        }

        if (isset($attributes['location']) && is_string($attributes['location'])) {
            $this->location = $attributes['location'];
        }
    }

    protected function parseDate(mixed $date): Carbon
    {
        if (is_string($date) || is_numeric($date) || $date instanceof DateTimeInterface) {
            return Carbon::parse($date);
        }

        throw new InvalidArgumentException('Date must be a string, numeric or DateTimeInterface.');
    }

    public function __toString(): string
    {
        $event = "BEGIN:VEVENT\n";
        $event .= 'SUMMARY:'.$this->formatProperty($this->summary)."\n";

        if ($this->description) {
            $event .= 'DESCRIPTION:'.$this->formatProperty($this->description)."\n";
        }

        if ($this->location) {
            $event .= 'LOCATION:'.$this->formatProperty($this->location)."\n";
        }

        $event .= 'DTSTART:'.$this->start->utc()->format('Ymd\THis\Z')."\n";
        $event .= 'DTEND:'.$this->end->utc()->format('Ymd\THis\Z')."\n";
        $event .= 'END:VEVENT';

        return $event;
    }

    protected function formatProperty(string $value): string
    {
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace(';', '\;', $value);
        $value = str_replace(',', '\,', $value);
        $value = str_replace(["\r\n", "\r", "\n"], '\\n', $value);

        return $value;
    }
}

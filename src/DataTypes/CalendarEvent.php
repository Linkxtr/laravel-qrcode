<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\DataTypes;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use DateTimeInterface;
use Linkxtr\QrCode\Contracts\DataTypeInterface;
use Linkxtr\QrCode\Exceptions\DataTypes\InvalidCalendarEventArgumentException;

final readonly class CalendarEvent implements DataTypeInterface
{
    private Carbon $startParsed;

    private Carbon $endParsed;

    private string $uidValue;

    public function __construct(
        private string $summary,
        Carbon|DateTimeInterface|string|int $start,
        Carbon|DateTimeInterface|string|int $end,
        private ?string $description = null,
        private ?string $location = null,
        ?string $uid = null
    ) {
        if ($this->summary === '') {
            throw InvalidCalendarEventArgumentException::invalidSummary('string');
        }

        $this->startParsed = $this->parseDate($start);
        $this->endParsed = $this->parseDate($end);

        if ($this->endParsed <= $this->startParsed) {
            throw InvalidCalendarEventArgumentException::endDateMustBeAfterStartDate();
        }

        $this->uidValue = ($uid !== null && $uid !== '')
            ? $uid
            : sha1($this->summary.$this->startParsed->timestamp.$this->endParsed->timestamp.$this->description.$this->location).'@linkxtr-qrcode';
    }

    public function __toString(): string
    {
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Linkxtr//LaravelQrCode//EN',
            'BEGIN:VEVENT',
            'UID:'.$this->uidValue,
            'DTSTAMP:'.Carbon::now()->utc()->format('Ymd\THis\Z'),
            'SUMMARY:'.$this->formatProperty($this->summary),
        ];

        if ($this->description !== null && $this->description !== '') {
            $lines[] = 'DESCRIPTION:'.$this->formatProperty($this->description);
        }

        if ($this->location !== null && $this->location !== '') {
            $lines[] = 'LOCATION:'.$this->formatProperty($this->location);
        }

        $lines[] = 'DTSTART:'.$this->startParsed->copy()->utc()->format('Ymd\THis\Z');
        $lines[] = 'DTEND:'.$this->endParsed->copy()->utc()->format('Ymd\THis\Z');
        $lines[] = 'END:VEVENT';
        $lines[] = 'END:VCALENDAR';
        $lines[] = ''; // Required to generate the final trailing \r\n

        return implode("\r\n", $lines);
    }

    private function parseDate(Carbon|DateTimeInterface|string|int $date): Carbon
    {
        $carbonDate = null;

        try {
            $carbonDate = Carbon::parse($date);
        } catch (InvalidFormatException) {
            throw InvalidCalendarEventArgumentException::invalidDate(gettype($date));
        }

        return $carbonDate;
    }

    private function formatProperty(string $value): string
    {
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace(';', '\;', $value);
        $value = str_replace(',', '\,', $value);

        return str_replace(["\r\n", "\r", "\n"], '\\n', $value);
    }
}

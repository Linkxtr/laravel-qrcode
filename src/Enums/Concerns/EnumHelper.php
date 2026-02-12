<?php

namespace Linkxtr\QrCode\Enums\Concerns;

trait EnumHelper
{
    /**
     * Get all values of the enum.
     *
     * @return array<string>
     */
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}

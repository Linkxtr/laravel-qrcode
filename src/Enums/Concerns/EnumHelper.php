<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Enums\Concerns;

/**
 * Used to get all values of the enum. support only Backed Enums.
 */
trait EnumHelper
{
    /**
     * Get all values of the enum.
     *
     * @return array<string>
     */
    public static function toArray(): array
    {
        return array_map(fn (self $c): string => $c->value, self::cases());
    }
}

<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Enums\Concerns;

use BackedEnum;

trait EnumHelper
{
    /**
     * Get all values of the enum.
     *
     * @return array<string>
     */
    public static function toArray(): array
    {
        /** @phpstan-ignore-next-line */
        return array_map(fn ($c) => $c instanceof BackedEnum ? $c->value : $c->name, self::cases());
    }
}

<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Facades;

use Illuminate\Support\Facades\Facade;
use Linkxtr\QrCode\Generator;

/**
 * @mixin Generator
 *
 * @see Generator
 */
final class QrCode extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'qrcode';
    }
}

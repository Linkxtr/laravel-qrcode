<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\ValueObjects;

final readonly class ColorValue
{
    public function __construct(
        public int $c1,
        public int $c2,
        public int $c3,
        public ?int $c4 = null,
    ) {}
}

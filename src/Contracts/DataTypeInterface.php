<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Contracts;

interface DataTypeInterface
{
    public function __toString(): string;

    /** @param array<int, mixed> $arguments */
    public function create(array $arguments): void;
}

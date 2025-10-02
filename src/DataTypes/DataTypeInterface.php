<?php

namespace Linkxtr\QrCode\DataTypes;

interface DataTypeInterface
{
    /** @param array<int, mixed> $arguments */
    public function create(array $arguments): void;

    public function __toString(): string;
}

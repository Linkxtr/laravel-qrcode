<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Contracts;

interface MergerInterface
{
    public function merge(): string;
}

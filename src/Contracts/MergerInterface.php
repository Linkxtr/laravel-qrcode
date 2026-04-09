<?php

declare(strict_types=1);

namespace Linkxtr\QrCode\Contracts;

interface MergerInterface
{
    public function __construct(string $sourceImageContent, string $mergeImageContent, float $percentage);

    public function merge(): string;
}

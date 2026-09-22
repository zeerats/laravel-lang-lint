<?php

declare(strict_types=1);

namespace Zeerats\TranslationChecker\Scanning;

use Stringable;

final readonly class Location implements Stringable
{
    public function __construct(
        public string $path,
        public int $line,
    ) {}

    public function __toString(): string
    {
        return "{$this->path}:{$this->line}";
    }
}

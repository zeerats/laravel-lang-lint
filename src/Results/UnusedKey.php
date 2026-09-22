<?php

declare(strict_types=1);

namespace Zeerats\TranslationChecker\Results;

final readonly class UnusedKey
{
    /**
     * @param  list<string>  $locales  The locales whose language files define the key.
     */
    public function __construct(
        public string $group,
        public string $item,
        public array $locales,
    ) {}

    public function key(): string
    {
        return "{$this->group}.{$this->item}";
    }
}

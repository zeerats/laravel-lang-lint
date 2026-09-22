<?php

declare(strict_types=1);

namespace Zeerats\LangLint\Results;

use Zeerats\LangLint\Scanning\Location;

final readonly class MissingKey
{
    /**
     * @param  list<string>  $locales  The locales whose language files lack the key.
     * @param  list<Location>  $locations  The places the key is used.
     */
    public function __construct(
        public string $key,
        public array $locales,
        public array $locations,
    ) {}
}

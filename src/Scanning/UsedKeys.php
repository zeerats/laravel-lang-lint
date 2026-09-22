<?php

declare(strict_types=1);

namespace Zeerats\LangLint\Scanning;

final readonly class UsedKeys
{
    /**
     * @param  array<string, list<Location>>  $locations  The places each key is used, sorted by key.
     */
    public function __construct(private array $locations) {}

    /**
     * @return array<string, list<Location>>
     */
    public function all(): array
    {
        return $this->locations;
    }

    /**
     * Whether the key itself or one of its parents is used. A call such as
     * __('messages.types') returns the whole array, so it uses every key below it.
     */
    public function covers(string $key): bool
    {
        while (true) {
            if (isset($this->locations[$key])) {
                return true;
            }

            $separator = strrpos($key, '.');

            if ($separator === false) {
                return false;
            }

            $key = substr($key, 0, $separator);
        }
    }
}

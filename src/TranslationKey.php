<?php

declare(strict_types=1);

namespace Zeerats\LangLint;

/**
 * A key that Laravel resolves from a PHP language file, such as
 * "messages.welcome" or "admin/users.title".
 */
final readonly class TranslationKey
{
    private function __construct(
        public string $group,
        public string $item,
    ) {}

    /**
     * Returns null for keys that no PHP language file can satisfy: namespaced
     * keys belong to packages, and strings without a dot or with whitespace
     * are JSON translation strings.
     */
    public static function tryFrom(string $key): ?self
    {
        if (str_contains($key, '::') || preg_match('/\s/', $key) === 1) {
            return null;
        }

        $separator = strpos($key, '.');

        if ($separator === false || $separator === 0 || $separator === strlen($key) - 1) {
            return null;
        }

        return new self(substr($key, 0, $separator), substr($key, $separator + 1));
    }
}

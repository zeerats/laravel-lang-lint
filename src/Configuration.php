<?php

declare(strict_types=1);

namespace Zeerats\TranslationChecker;

use InvalidArgumentException;

final readonly class Configuration
{
    /**
     * @param  list<string>  $paths  Directories to scan, relative to the base path unless absolute.
     * @param  list<string>  $functions  Functions, static calls and Blade directives that take a translation key.
     * @param  list<string>  $locales  Locales to check; an empty list discovers every locale directory.
     * @param  list<string>  $ignoreLocales  Regular expressions matching locale directories to skip during discovery.
     * @param  list<string>  $assumeUsed  Regular expressions matching keys that always count as used.
     */
    public function __construct(
        public string $basePath,
        public string $langPath,
        public array $paths,
        public array $functions,
        public array $locales,
        public array $ignoreLocales,
        public array $assumeUsed,
    ) {
        if ($paths === []) {
            throw new InvalidArgumentException('At least one path to scan must be configured.');
        }

        if ($functions === []) {
            throw new InvalidArgumentException('At least one translation function must be configured.');
        }

        self::assertValidPatterns('ignore_locales', $ignoreLocales);
        self::assertValidPatterns('assume_used', $assumeUsed);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public static function fromArray(array $config, string $basePath, string $langPath): self
    {
        return new self(
            basePath: $basePath,
            langPath: $langPath,
            paths: self::strings($config, 'paths'),
            functions: self::strings($config, 'functions'),
            locales: self::strings($config, 'locales'),
            ignoreLocales: self::strings($config, 'ignore_locales'),
            assumeUsed: self::strings($config, 'assume_used'),
        );
    }

    /**
     * @param  list<string>  $paths
     */
    public function withPaths(array $paths): self
    {
        return new self(
            $this->basePath,
            $this->langPath,
            $paths,
            $this->functions,
            $this->locales,
            $this->ignoreLocales,
            $this->assumeUsed,
        );
    }

    /**
     * @param  list<string>  $locales
     */
    public function withLocales(array $locales): self
    {
        return new self(
            $this->basePath,
            $this->langPath,
            $this->paths,
            $this->functions,
            $locales,
            $this->ignoreLocales,
            $this->assumeUsed,
        );
    }

    /**
     * The absolute directories to scan.
     *
     * @return list<string>
     */
    public function scanPaths(): array
    {
        return array_map($this->absolutePath(...), $this->paths);
    }

    public function localePath(string $locale): string
    {
        return rtrim($this->langPath, '\\/') . DIRECTORY_SEPARATOR . $locale;
    }

    /**
     * The path relative to the base path, or the given path when it lies outside of it.
     */
    public function relativePath(string $path): string
    {
        $base = rtrim($this->basePath, '\\/') . DIRECTORY_SEPARATOR;

        return str_starts_with($path, $base) ? substr($path, strlen($base)) : $path;
    }

    public function isIgnoredLocale(string $locale): bool
    {
        return self::matchesAny($this->ignoreLocales, $locale);
    }

    public function isAssumedUsed(string $key): bool
    {
        return self::matchesAny($this->assumeUsed, $key);
    }

    private function absolutePath(string $path): string
    {
        if (self::isAbsolute($path)) {
            return $path;
        }

        return rtrim($this->basePath, '\\/') . DIRECTORY_SEPARATOR . $path;
    }

    private static function isAbsolute(string $path): bool
    {
        return str_starts_with($path, '/')
            || str_starts_with($path, '\\')
            || preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1;
    }

    /**
     * @param  list<string>  $patterns
     */
    private static function matchesAny(array $patterns, string $subject): bool
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $subject) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $config
     * @return list<string>
     */
    private static function strings(array $config, string $option): array
    {
        $values = $config[$option] ?? [];

        if (! is_array($values)) {
            throw new InvalidArgumentException("The [{$option}] option must be an array.");
        }

        $strings = [];

        foreach ($values as $value) {
            if (! is_string($value) || $value === '') {
                throw new InvalidArgumentException("The [{$option}] option may only contain non-empty strings.");
            }

            $strings[] = $value;
        }

        return $strings;
    }

    /**
     * @param  list<string>  $patterns
     */
    private static function assertValidPatterns(string $option, array $patterns): void
    {
        foreach ($patterns as $pattern) {
            if (! self::isValidPattern($pattern)) {
                throw new InvalidArgumentException("The [{$option}] option contains an invalid regular expression: {$pattern}");
            }
        }
    }

    private static function isValidPattern(string $pattern): bool
    {
        set_error_handler(static fn (): bool => true);

        try {
            return preg_match($pattern, '') !== false;
        } finally {
            restore_error_handler();
        }
    }
}

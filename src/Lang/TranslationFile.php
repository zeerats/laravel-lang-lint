<?php

declare(strict_types=1);

namespace Zeerats\LangLint\Lang;

use Illuminate\Support\Arr;
use UnexpectedValueException;

final readonly class TranslationFile
{
    /**
     * @param  array<array-key, mixed>  $lines
     */
    public function __construct(
        public string $locale,
        public string $group,
        public string $path,
        public array $lines,
    ) {}

    public static function load(string $locale, string $group, string $path): self
    {
        $lines = (static fn (): mixed => require $path)();

        if (! is_array($lines)) {
            throw new UnexpectedValueException("Translation file [{$path}] must return an array.");
        }

        return new self($locale, $group, $path, $lines);
    }

    /**
     * The key of every line relative to the group, such as "nested.deep".
     *
     * @return list<string>
     */
    public function items(): array
    {
        $items = [];

        foreach (array_keys(Arr::dot($this->lines)) as $item) {
            $items[] = (string) $item;
        }

        return $items;
    }

    /**
     * A copy without the given lines. Parents that a removal leaves empty are removed as well.
     *
     * @param  list<string>  $items
     */
    public function without(array $items): self
    {
        $lines = $this->lines;

        foreach ($items as $item) {
            Arr::forget($lines, $item);

            while (($separator = strrpos($item, '.')) !== false) {
                $item = substr($item, 0, $separator);

                if (Arr::get($lines, $item) !== []) {
                    break;
                }

                Arr::forget($lines, $item);
            }
        }

        return new self($this->locale, $this->group, $this->path, $lines);
    }

    /**
     * A copy with the keys of every nested array sorted. Lists keep their order.
     */
    public function sorted(): self
    {
        return new self($this->locale, $this->group, $this->path, self::sort($this->lines));
    }

    /**
     * @param  array<array-key, mixed>  $lines
     * @return array<array-key, mixed>
     */
    private static function sort(array $lines): array
    {
        foreach ($lines as $key => $value) {
            if (is_array($value)) {
                $lines[$key] = self::sort($value);
            }
        }

        if (! array_is_list($lines)) {
            ksort($lines, SORT_STRING);
        }

        return $lines;
    }
}

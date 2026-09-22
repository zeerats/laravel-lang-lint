<?php

declare(strict_types=1);

namespace Zeerats\LangLint;

use Illuminate\Support\Arr;
use RuntimeException;
use Zeerats\LangLint\Lang\LangDirectory;
use Zeerats\LangLint\Lang\PhpArrayWriter;
use Zeerats\LangLint\Lang\TranslationFile;
use Zeerats\LangLint\Results\MissingKey;
use Zeerats\LangLint\Results\UnusedKey;
use Zeerats\LangLint\Scanning\KeyExtractor;

final readonly class Linter
{
    private KeyExtractor $extractor;

    private LangDirectory $lang;

    private PhpArrayWriter $writer;

    public function __construct(private Configuration $configuration)
    {
        $this->extractor = new KeyExtractor($configuration);
        $this->lang = new LangDirectory($configuration);
        $this->writer = new PhpArrayWriter;
    }

    /**
     * @return list<string>
     */
    public function locales(): array
    {
        return $this->lang->locales();
    }

    /**
     * Keys used in the source code that at least one locale does not define.
     *
     * @return list<MissingKey>
     */
    public function missing(): array
    {
        $locales = $this->lang->locales();
        $files = [];

        foreach ($locales as $locale) {
            $files[$locale] = $this->lang->files($locale);
        }

        $missing = [];

        foreach ($this->extractor->extract()->all() as $key => $locations) {
            $translationKey = TranslationKey::tryFrom($key);

            if ($translationKey === null) {
                continue;
            }

            $absent = [];

            foreach ($locales as $locale) {
                $file = $files[$locale][$translationKey->group] ?? null;

                if ($file === null || ! Arr::has($file->lines, $translationKey->item)) {
                    $absent[] = $locale;
                }
            }

            if ($absent !== []) {
                $missing[] = new MissingKey($key, $absent, $locations);
            }
        }

        return $missing;
    }

    /**
     * Keys defined in the language files that the source code does not use.
     *
     * @return list<UnusedKey>
     */
    public function unused(): array
    {
        $used = $this->extractor->extract();

        /** @var array<string, array{string, string, list<string>}> $found */
        $found = [];

        foreach ($this->lang->locales() as $locale) {
            foreach ($this->lang->files($locale) as $file) {
                foreach ($file->items() as $item) {
                    $key = "{$file->group}.{$item}";

                    if ($used->covers($key) || $this->configuration->isAssumedUsed($key)) {
                        continue;
                    }

                    $found[$key] ??= [$file->group, $item, []];
                    $found[$key][2][] = $locale;
                }
            }
        }

        ksort($found, SORT_STRING);

        $unused = [];

        foreach ($found as [$group, $item, $locales]) {
            $unused[] = new UnusedKey($group, $item, $locales);
        }

        return $unused;
    }

    /**
     * Remove keys from the language files they are defined in.
     *
     * @param  list<UnusedKey>  $keys
     * @return list<string> The relative paths of the files that changed.
     */
    public function prune(array $keys): array
    {
        $changed = [];

        foreach (self::removals($keys) as $locale => $groups) {
            $files = $this->lang->files($locale);

            foreach ($groups as $group => $items) {
                if (isset($files[$group]) && $this->sync($files[$group]->without($items), write: true)) {
                    $changed[] = $this->configuration->relativePath($files[$group]->path);
                }
            }
        }

        return $changed;
    }

    /**
     * Rewrite every language file in the canonical style with sorted keys,
     * optionally without the unused keys.
     *
     * @return list<string> The relative paths of the files that changed, or that would change when not writing.
     */
    public function format(bool $prune = false, bool $write = true): array
    {
        $removals = $prune ? self::removals($this->unused()) : [];
        $changed = [];

        foreach ($this->lang->locales() as $locale) {
            foreach ($this->lang->files($locale) as $file) {
                $formatted = $file->without($removals[$locale][$file->group] ?? [])->sorted();

                if ($this->sync($formatted, $write)) {
                    $changed[] = $this->configuration->relativePath($file->path);
                }
            }
        }

        return $changed;
    }

    /**
     * @param  list<UnusedKey>  $keys
     * @return array<string, array<string, list<string>>> The items to remove, by locale and group.
     */
    private static function removals(array $keys): array
    {
        $removals = [];

        foreach ($keys as $key) {
            foreach ($key->locales as $locale) {
                $removals[$locale][$key->group][] = $key->item;
            }
        }

        return $removals;
    }

    /**
     * Whether the file differs from its canonical rendering, writing that rendering when requested.
     */
    private function sync(TranslationFile $file, bool $write): bool
    {
        $contents = $this->writer->render($file->lines);

        if (file_get_contents($file->path) === $contents) {
            return false;
        }

        if ($write && file_put_contents($file->path, $contents) === false) {
            throw new RuntimeException("Unable to write [{$file->path}].");
        }

        return true;
    }
}

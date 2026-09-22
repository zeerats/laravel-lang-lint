<?php

declare(strict_types=1);

namespace Zeerats\LangLint\Lang;

use InvalidArgumentException;
use Symfony\Component\Finder\Finder;
use Zeerats\LangLint\Configuration;

/**
 * The application's language directory: one subdirectory per locale holding
 * PHP files that each return the lines of a group.
 */
final readonly class LangDirectory
{
    public function __construct(private Configuration $configuration) {}

    /**
     * The configured locales, or every locale directory except vendor and the ignored ones.
     *
     * @return list<string>
     */
    public function locales(): array
    {
        $langPath = $this->configuration->langPath;

        if (! is_dir($langPath)) {
            throw new InvalidArgumentException("Language directory [{$langPath}] does not exist. Create a locale directory or publish the framework's language files with lang:publish.");
        }

        if ($this->configuration->locales !== []) {
            foreach ($this->configuration->locales as $locale) {
                if (! is_dir($this->configuration->localePath($locale))) {
                    throw new InvalidArgumentException("Locale [{$locale}] has no directory in [{$langPath}].");
                }
            }

            return $this->configuration->locales;
        }

        $locales = [];

        foreach (Finder::create()->directories()->in($langPath)->depth(0)->sortByName() as $directory) {
            $locale = $directory->getFilename();

            if ($locale !== 'vendor' && ! $this->configuration->isIgnoredLocale($locale)) {
                $locales[] = $locale;
            }
        }

        if ($locales === []) {
            throw new InvalidArgumentException("No locale directories found in [{$langPath}].");
        }

        return $locales;
    }

    /**
     * The language files of a locale keyed by group. Files in subdirectories
     * form groups such as "admin/users".
     *
     * @return array<string, TranslationFile>
     */
    public function files(string $locale): array
    {
        $files = [];

        foreach (Finder::create()->files()->name('*.php')->in($this->configuration->localePath($locale))->sortByName() as $file) {
            $group = str_replace('\\', '/', substr($file->getRelativePathname(), 0, -4));
            $files[$group] = TranslationFile::load($locale, $group, $file->getPathname());
        }

        return $files;
    }
}

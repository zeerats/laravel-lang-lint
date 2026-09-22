<?php

declare(strict_types=1);

namespace Zeerats\TranslationChecker\Console;

use Zeerats\TranslationChecker\Scanning\Location;

final class CheckCommand extends TranslationCommand
{
    protected $signature = 'lang:check
        {--path=* : Directory to scan instead of the configured paths, relative to the base path}
        {--locale=* : Locale to check instead of every discovered locale}
        {--show-locations : Show the source files and lines that use each missing key}';

    protected $description = 'Report translation keys used in the source code that are missing from the language files';

    protected function process(): int
    {
        $checker = $this->checker();
        $locales = $checker->locales();
        $missing = $checker->missing();

        if ($missing === []) {
            $this->components->info(sprintf(
                'Every translation key used in the source code exists in %s.',
                implode(', ', $locales),
            ));

            return self::SUCCESS;
        }

        $showLocations = (bool) $this->option('show-locations');
        $rows = [];

        foreach ($missing as $key) {
            $row = [$key->key, implode(', ', $key->locales)];

            if ($showLocations) {
                $row[] = implode("\n", array_map(
                    static fn (Location $location): string => (string) $location,
                    $key->locations,
                ));
            }

            $rows[] = $row;
        }

        $this->table($showLocations ? ['Key', 'Missing in', 'Used in'] : ['Key', 'Missing in'], $rows);
        $this->components->error(sprintf(
            'Found %s missing from at least one locale.',
            self::pluralize(count($missing), 'translation key'),
        ));

        return self::FAILURE;
    }
}

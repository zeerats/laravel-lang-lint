<?php

declare(strict_types=1);

namespace Zeerats\TranslationChecker\Console;

use Zeerats\TranslationChecker\Results\UnusedKey;

final class UnusedCommand extends TranslationCommand
{
    protected $signature = 'lang:unused
        {--path=* : Directory to scan instead of the configured paths, relative to the base path}
        {--locale=* : Locale to check instead of every discovered locale}
        {--prune : Remove the unused keys from the language files}';

    protected $description = 'Report translation keys in the language files that the source code does not use';

    protected function process(): int
    {
        $checker = $this->checker();
        $unused = $checker->unused();

        if ($unused === []) {
            $this->components->info('Every translation key in the language files is used in the source code.');

            return self::SUCCESS;
        }

        $this->table(
            ['Key', 'Defined in'],
            array_map(static fn (UnusedKey $key): array => [$key->key(), implode(', ', $key->locales)], $unused),
        );

        if (! $this->option('prune')) {
            $this->components->error(sprintf(
                'Found %s. Run again with --prune to remove them.',
                self::pluralize(count($unused), 'unused translation key'),
            ));

            return self::FAILURE;
        }

        $files = $checker->prune($unused);

        $this->components->info(sprintf(
            'Removed %s from %s.',
            self::pluralize(count($unused), 'unused translation key'),
            self::pluralize(count($files), 'language file'),
        ));
        $this->components->bulletList($files);

        return self::SUCCESS;
    }
}

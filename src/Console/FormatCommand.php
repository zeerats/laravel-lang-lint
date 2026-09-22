<?php

declare(strict_types=1);

namespace Zeerats\TranslationChecker\Console;

final class FormatCommand extends TranslationCommand
{
    protected $signature = 'lang:format
        {--path=* : Directory to scan for used keys when pruning, relative to the base path}
        {--locale=* : Locale to format instead of every discovered locale}
        {--prune : Remove unused translation keys as well}
        {--check : Report the files that would change without writing them}';

    protected $description = 'Rewrite the language files with sorted keys and a consistent style';

    protected function process(): int
    {
        $check = (bool) $this->option('check');

        $files = $this->checker()->format(prune: (bool) $this->option('prune'), write: ! $check);

        if ($files === []) {
            $this->components->info('Every language file is formatted.');

            return self::SUCCESS;
        }

        if ($check) {
            $this->components->error(sprintf('Found %s.', self::pluralize(count($files), 'unformatted language file')));
            $this->components->bulletList($files);

            return self::FAILURE;
        }

        $this->components->info(sprintf('Formatted %s.', self::pluralize(count($files), 'language file')));
        $this->components->bulletList($files);

        return self::SUCCESS;
    }
}

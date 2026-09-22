<?php

declare(strict_types=1);

namespace Zeerats\LangLint\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use InvalidArgumentException;
use UnexpectedValueException;
use Zeerats\LangLint\Configuration;
use Zeerats\LangLint\Linter;

abstract class LintCommand extends Command
{
    /**
     * Run the command, reporting invalid options, configuration and language files as a plain error.
     */
    final public function handle(): int
    {
        try {
            return $this->process();
        } catch (InvalidArgumentException|UnexpectedValueException $exception) {
            $this->components->error($exception->getMessage());

            return self::INVALID;
        }
    }

    abstract protected function process(): int;

    /**
     * A linter for the configured application, narrowed by the --path and --locale options.
     */
    protected function linter(): Linter
    {
        $configuration = $this->laravel->make(Configuration::class);

        $paths = $this->listOption('path');
        $locales = $this->listOption('locale');

        if ($paths !== []) {
            $configuration = $configuration->withPaths($paths);
        }

        if ($locales !== []) {
            $configuration = $configuration->withLocales($locales);
        }

        return new Linter($configuration);
    }

    protected static function pluralize(int $count, string $noun): string
    {
        return $count . ' ' . Str::plural($noun, $count);
    }

    /**
     * @return list<string>
     */
    private function listOption(string $name): array
    {
        $values = [];

        foreach ((array) $this->option($name) as $value) {
            if (is_string($value) && $value !== '') {
                $values[] = $value;
            }
        }

        return $values;
    }
}

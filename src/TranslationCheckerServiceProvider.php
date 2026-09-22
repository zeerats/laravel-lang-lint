<?php

declare(strict_types=1);

namespace Zeerats\TranslationChecker;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Zeerats\TranslationChecker\Console\CheckCommand;
use Zeerats\TranslationChecker\Console\FormatCommand;
use Zeerats\TranslationChecker\Console\UnusedCommand;

final class TranslationCheckerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/translation-checker.php', 'translation-checker');

        $this->app->bind(Configuration::class, static function (Application $app): Configuration {
            /** @var array<string, mixed> $config */
            $config = $app->make(Repository::class)->get('translation-checker', []);

            return Configuration::fromArray($config, $app->basePath(), $app->langPath());
        });
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/translation-checker.php' => $this->app->configPath('translation-checker.php'),
        ], ['translation-checker', 'translation-checker-config']);

        $this->commands([
            CheckCommand::class,
            UnusedCommand::class,
            FormatCommand::class,
        ]);
    }
}

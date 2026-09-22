<?php

declare(strict_types=1);

namespace Zeerats\LangLint;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Zeerats\LangLint\Console\CheckCommand;
use Zeerats\LangLint\Console\FormatCommand;
use Zeerats\LangLint\Console\UnusedCommand;

final class LangLintServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/lang-lint.php', 'lang-lint');

        $this->app->bind(Configuration::class, static function (Application $app): Configuration {
            /** @var array<string, mixed> $config */
            $config = $app->make(Repository::class)->get('lang-lint', []);

            return Configuration::fromArray($config, $app->basePath(), $app->langPath());
        });
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/lang-lint.php' => $this->app->configPath('lang-lint.php'),
        ], ['lang-lint', 'lang-lint-config']);

        $this->commands([
            CheckCommand::class,
            UnusedCommand::class,
            FormatCommand::class,
        ]);
    }
}

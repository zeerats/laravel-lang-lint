<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Zeerats\LangLint\Configuration;
use Zeerats\LangLint\LangLintServiceProvider;

it('merges the default configuration', function (): void {
    expect(config('lang-lint.paths'))->toBe(['app', 'resources/views'])
        ->and(config('lang-lint.functions'))->toContain('__', 'trans_choice', '@lang')
        ->and(config('lang-lint.assume_used'))->toContain('/^validation\./');
});

it('registers the commands', function (): void {
    expect(Artisan::all())->toHaveKeys(['lang:check', 'lang:unused', 'lang:format']);
});

it('publishes the configuration file', function (): void {
    $paths = ServiceProvider::pathsToPublish(LangLintServiceProvider::class, 'lang-lint-config');

    expect($paths)->toHaveCount(1)
        ->and(array_values($paths)[0])->toBe(config_path('lang-lint.php'));
});

it('resolves the configuration from the application', function (): void {
    $this->useFixture();
    config()->set('lang-lint.paths', ['app']);

    $configuration = app(Configuration::class);

    expect($configuration->basePath)->toBe(base_path())
        ->and($configuration->langPath)->toBe(lang_path())
        ->and($configuration->paths)->toBe(['app'])
        ->and($configuration->ignoreLocales)->toBe(['/^[a-z]{2}-[A-Z]{2}$/']);
});

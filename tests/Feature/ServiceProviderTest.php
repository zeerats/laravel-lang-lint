<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Zeerats\TranslationChecker\Configuration;
use Zeerats\TranslationChecker\TranslationCheckerServiceProvider;

it('merges the default configuration', function (): void {
    expect(config('translation-checker.paths'))->toBe(['app', 'resources/views'])
        ->and(config('translation-checker.functions'))->toContain('__', 'trans_choice', '@lang')
        ->and(config('translation-checker.assume_used'))->toContain('/^validation\./');
});

it('registers the commands', function (): void {
    expect(Artisan::all())->toHaveKeys(['lang:check', 'lang:unused', 'lang:format']);
});

it('publishes the configuration file', function (): void {
    $paths = ServiceProvider::pathsToPublish(TranslationCheckerServiceProvider::class, 'translation-checker-config');

    expect($paths)->toHaveCount(1)
        ->and(array_values($paths)[0])->toBe(config_path('translation-checker.php'));
});

it('resolves the configuration from the application', function (): void {
    $this->useFixture();
    config()->set('translation-checker.paths', ['app']);

    $configuration = app(Configuration::class);

    expect($configuration->basePath)->toBe(base_path())
        ->and($configuration->langPath)->toBe(lang_path())
        ->and($configuration->paths)->toBe(['app'])
        ->and($configuration->ignoreLocales)->toBe(['/^[a-z]{2}-[A-Z]{2}$/']);
});

<?php

declare(strict_types=1);

beforeEach(function (): void {
    $this->useFixture();
});

it('reports keys that at least one locale does not define', function (): void {
    $this->artisan('lang:check')
        ->expectsTable(['Key', 'Missing in'], [
            ['messages.missing_in_blade', 'de, en'],
            ['messages.missing_item', 'de, en'],
            ['messages.only_in_en', 'de'],
            ['missing.group', 'de, en'],
        ])
        ->expectsOutputToContain('Found 4 translation keys missing from at least one locale.')
        ->assertFailed();
});

it('shows where the missing keys are used', function (): void {
    $this->artisan('lang:check --show-locations')
        ->expectsTable(['Key', 'Missing in', 'Used in'], [
            ['messages.missing_in_blade', 'de, en', 'resources/views/example.blade.php:4'],
            ['messages.missing_item', 'de, en', 'app/Example.php:33'],
            ['messages.only_in_en', 'de', 'app/Example.php:29'],
            ['missing.group', 'de, en', 'app/Example.php:32'],
        ])
        ->assertFailed();
});

it('checks only the given locales', function (): void {
    $this->artisan('lang:check --locale=en')
        ->expectsTable(['Key', 'Missing in'], [
            ['messages.missing_in_blade', 'en'],
            ['messages.missing_item', 'en'],
            ['missing.group', 'en'],
        ])
        ->assertFailed();
});

it('scans only the given paths', function (): void {
    $this->artisan('lang:check --path=resources/views')
        ->expectsTable(['Key', 'Missing in'], [
            ['messages.missing_in_blade', 'de, en'],
        ])
        ->expectsOutputToContain('Found 1 translation key missing from at least one locale.')
        ->assertFailed();
});

it('discovers every locale that is not ignored', function (): void {
    config()->set('translation-checker.ignore_locales', []);

    $this->artisan('lang:check --path=resources/views')
        ->expectsTable(['Key', 'Missing in'], [
            ['messages.blade', 'de-AT'],
            ['messages.choice', 'de-AT'],
            ['messages.missing_in_blade', 'de, de-AT, en'],
        ])
        ->assertFailed();
});

it('passes when every used key exists in every locale', function (): void {
    $this->artisan('lang:check --path=app/Complete')
        ->expectsOutputToContain('Every translation key used in the source code exists in de, en.')
        ->assertSuccessful();
});

it('reports a locale without a directory', function (): void {
    $this->artisan('lang:check --locale=xx')
        ->expectsOutputToContain('Locale [xx] has no directory in')
        ->assertExitCode(2);
});

it('reports a path that is not a directory', function (): void {
    $this->artisan('lang:check --path=nowhere')
        ->expectsOutputToContain('is not a directory.')
        ->assertExitCode(2);
});

it('reports a missing language directory', function (): void {
    $this->useEmptyApplication(['app']);

    $this->artisan('lang:check')
        ->expectsOutputToContain('does not exist. Create a locale directory or publish')
        ->assertExitCode(2);
});

it('reports a language directory without locales', function (): void {
    $this->useEmptyApplication(['app', 'lang/vendor/package/en']);

    $this->artisan('lang:check --path=app')
        ->expectsOutputToContain('No locale directories found in')
        ->assertExitCode(2);
});

it('reports an invalid configuration', function (): void {
    config()->set('translation-checker.assume_used', ['(unclosed']);

    $this->artisan('lang:check')
        ->expectsOutputToContain('The [assume_used] option contains an invalid regular expression: (unclosed')
        ->assertExitCode(2);
});

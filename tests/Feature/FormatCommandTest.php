<?php

declare(strict_types=1);

use Zeerats\TranslationChecker\Tests\TestCase;

it('reports unformatted files with --check without writing them', function (): void {
    $path = $this->useFixtureCopy();

    $this->artisan('lang:format --check')
        ->expectsOutputToContain('Found 6 unformatted language files.')
        ->expectsOutputToContain('lang/en/admin/users.php')
        ->assertFailed();

    expect(file_get_contents($path . '/lang/en/messages.php'))
        ->toBe(file_get_contents(TestCase::fixturePath('lang/en/messages.php')));
});

it('rewrites the files with sorted keys in the canonical style', function (): void {
    $path = $this->useFixtureCopy();

    $this->artisan('lang:format')
        ->expectsOutputToContain('Formatted 6 language files.')
        ->assertSuccessful();

    expect(file_get_contents($path . '/lang/en/messages.php'))->toBe(<<<'FILE'
        <?php

        return [

            'blade' => 'Blade',
            'choice' => 'One|Many',
            'double' => 'Double quoted',
            'facade' => 'Facade',
            'list' => [
                'b',
                'a',
            ],
            'multiline' => 'Multiline',
            'nested' => [
                'deep' => 'Deep',
            ],
            'only_in_en' => 'Only in English',
            'quoted' => 'It\'s quoted',
            'types' => [
                'image' => 'Image',
                'video' => 'Video',
            ],
            'unused' => 'Unused',
            'unused_nested' => [
                'child' => 'Unused child',
            ],
            'welcome' => 'Welcome',

        ];

        FILE);

    expect(file_get_contents($path . '/lang/de-AT/messages.php'))
        ->toBe(file_get_contents(TestCase::fixturePath('lang/de-AT/messages.php')));

    $this->artisan('lang:format --check')
        ->expectsOutputToContain('Every language file is formatted.')
        ->assertSuccessful();
});

it('removes unused keys while formatting with --prune', function (): void {
    $path = $this->useFixtureCopy();

    $this->artisan('lang:format --prune')
        ->expectsOutputToContain('Formatted 6 language files.')
        ->assertSuccessful();

    expect(array_keys(require $path . '/lang/de/messages.php'))
        ->toBe(['blade', 'choice', 'double', 'facade', 'list', 'multiline', 'nested', 'quoted', 'types', 'welcome']);

    $this->artisan('lang:unused')->assertSuccessful();
});

it('formats only the given locales', function (): void {
    $path = $this->useFixtureCopy();

    $this->artisan('lang:format --locale=en')
        ->expectsOutputToContain('Formatted 3 language files.')
        ->assertSuccessful();

    expect(file_get_contents($path . '/lang/de/messages.php'))
        ->toBe(file_get_contents(TestCase::fixturePath('lang/de/messages.php')));
});

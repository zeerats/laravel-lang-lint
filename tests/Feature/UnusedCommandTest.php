<?php

declare(strict_types=1);

use Zeerats\LangLint\Tests\TestCase;

it('reports keys that the source code does not use', function (): void {
    $this->useFixture();

    $this->artisan('lang:unused')
        ->expectsTable(['Key', 'Defined in'], [
            ['messages.only_in_de', 'de'],
            ['messages.unused', 'de, en'],
            ['messages.unused_nested.child', 'de, en'],
        ])
        ->expectsOutputToContain('Found 3 unused translation keys. Run again with --prune to remove them.')
        ->assertFailed();
});

it('passes when every key is used', function (): void {
    $this->useFixture();

    $this->artisan('lang:unused --locale=en --path=app')
        ->expectsOutputToContain('Found 3 unused translation keys.')
        ->assertFailed();

    config()->set('lang-lint.assume_used', ['/^validation\./', '/^messages\.unused/']);

    $this->artisan('lang:unused --locale=en')
        ->expectsOutputToContain('Every translation key in the language files is used in the source code.')
        ->assertSuccessful();
});

it('removes the unused keys with --prune', function (): void {
    $path = $this->useFixtureCopy();

    $this->artisan('lang:unused --prune')
        ->expectsOutputToContain('Removed 3 unused translation keys from 2 language files.')
        ->expectsOutputToContain('lang/de/messages.php')
        ->assertSuccessful();

    expect(file_get_contents($path . '/lang/en/messages.php'))->toBe(<<<'FILE'
        <?php

        return [

            'welcome' => 'Welcome',
            'nested' => [
                'deep' => 'Deep',
            ],
            'types' => [
                'video' => 'Video',
                'image' => 'Image',
            ],
            'only_in_en' => 'Only in English',
            'quoted' => 'It\'s quoted',
            'blade' => 'Blade',
            'choice' => 'One|Many',
            'multiline' => 'Multiline',
            'double' => 'Double quoted',
            'facade' => 'Facade',
            'list' => [
                'b',
                'a',
            ],

        ];

        FILE);

    expect(require $path . '/lang/de/messages.php')
        ->not->toHaveKeys(['unused', 'unused_nested', 'only_in_de'])
        ->toHaveKey('welcome', 'Willkommen');

    expect(file_get_contents($path . '/lang/de/validation.php'))
        ->toBe(file_get_contents(TestCase::fixturePath('lang/de/validation.php')));

    $this->artisan('lang:unused')->assertSuccessful();
});

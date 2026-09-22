<?php

declare(strict_types=1);

use Zeerats\TranslationChecker\Configuration;
use Zeerats\TranslationChecker\Scanning\KeyExtractor;
use Zeerats\TranslationChecker\Tests\TestCase;

function extractor(array $paths, array $functions = ['__', 'trans', 'trans_choice', '@lang', '@choice', 'Lang::get', 'Lang::has', 'Lang::choice']): KeyExtractor
{
    return new KeyExtractor(new Configuration(
        basePath: TestCase::fixturePath(),
        langPath: TestCase::fixturePath('lang'),
        paths: $paths,
        functions: $functions,
        locales: [],
        ignoreLocales: [],
        assumeUsed: [],
    ));
}

it('extracts every literal key with the file and line that uses it', function (): void {
    $keys = extractor(['app'])->extract();

    expect(array_keys($keys->all()))->toBe([
        'A plain sentence.',
        'admin/users.title',
        'messages.choice',
        'messages.double',
        'messages.facade',
        'messages.list',
        'messages.missing_item',
        'messages.multiline',
        'messages.nested.deep',
        'messages.only_in_en',
        'messages.quoted',
        'messages.types',
        'messages.welcome',
        'missing.group',
        'validation.custom.email.required',
        'vendor::messages.welcome',
    ]);

    expect(array_map(strval(...), $keys->all()['messages.welcome']))->toBe(['app/Complete/Complete.php:11', 'app/Example.php:17'])
        ->and(array_map(strval(...), $keys->all()['messages.multiline']))->toBe(['app/Example.php:25'])
        ->and(array_map(strval(...), $keys->all()['messages.facade']))->toBe(['app/Example.php:37', 'app/Example.php:38', 'app/Example.php:39']);
});

it('extracts keys from Blade directives', function (): void {
    expect(array_keys(extractor(['resources/views'])->extract()->all()))->toBe([
        'messages.blade',
        'messages.choice',
        'messages.missing_in_blade',
        'messages.welcome',
    ]);
});

it('only recognises the configured functions', function (): void {
    expect(array_keys(extractor(['resources/views'], ['@lang'])->extract()->all()))->toBe(['messages.blade']);
});

it('unescapes string literals', function (): void {
    $path = sys_get_temp_dir() . '/translation-checker-' . bin2hex(random_bytes(6));
    mkdir($path);
    file_put_contents($path . '/escapes.php', <<<'PHP_SOURCE'
        <?php
        __('messages.it\'s');
        __("messages.say \"hi\"");
        __('messages.back\\slash');
        PHP_SOURCE);

    try {
        expect(array_keys(extractor([$path])->extract()->all()))->toBe([
            'messages.back\\slash',
            'messages.it\'s',
            'messages.say "hi"',
        ]);
    } finally {
        unlink($path . '/escapes.php');
        rmdir($path);
    }
});

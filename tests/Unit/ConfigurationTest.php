<?php

declare(strict_types=1);

use InvalidArgumentException;
use Zeerats\TranslationChecker\Configuration;

function configuration(array $overrides = []): Configuration
{
    return Configuration::fromArray($overrides + [
        'paths' => ['app'],
        'functions' => ['__'],
        'locales' => [],
        'ignore_locales' => [],
        'assume_used' => [],
    ], '/srv/app', '/srv/app/lang');
}

it('resolves scan paths against the base path', function (): void {
    expect(configuration(['paths' => ['app', 'resources/views', '/elsewhere', 'C:\\elsewhere']])->scanPaths())
        ->toBe(['/srv/app/app', '/srv/app/resources/views', '/elsewhere', 'C:\\elsewhere']);
});

it('resolves locale paths and relative paths', function (): void {
    $configuration = configuration();

    expect($configuration->localePath('de'))->toBe('/srv/app/lang/de')
        ->and($configuration->relativePath('/srv/app/app/Models/User.php'))->toBe('app/Models/User.php')
        ->and($configuration->relativePath('/elsewhere/file.php'))->toBe('/elsewhere/file.php');
});

it('matches ignored locales and assumed keys', function (): void {
    $configuration = configuration(['ignore_locales' => ['/^[a-z]{2}-[A-Z]{2}$/'], 'assume_used' => ['/^validation\./']]);

    expect($configuration->isIgnoredLocale('de-AT'))->toBeTrue()
        ->and($configuration->isIgnoredLocale('de'))->toBeFalse()
        ->and($configuration->isAssumedUsed('validation.required'))->toBeTrue()
        ->and($configuration->isAssumedUsed('messages.required'))->toBeFalse();
});

it('replaces paths and locales', function (): void {
    $configuration = configuration()->withPaths(['routes'])->withLocales(['en']);

    expect($configuration->paths)->toBe(['routes'])
        ->and($configuration->locales)->toBe(['en'])
        ->and($configuration->functions)->toBe(['__']);
});

it('requires at least one path', function (): void {
    configuration(['paths' => []]);
})->throws(InvalidArgumentException::class, 'At least one path');

it('requires at least one function', function (): void {
    configuration(['functions' => []]);
})->throws(InvalidArgumentException::class, 'At least one translation function');

it('rejects options that are not arrays', function (): void {
    configuration(['locales' => 'en']);
})->throws(InvalidArgumentException::class, 'The [locales] option must be an array.');

it('rejects values that are not strings', function (): void {
    configuration(['paths' => ['app', 1]]);
})->throws(InvalidArgumentException::class, 'The [paths] option may only contain non-empty strings.');

it('rejects invalid regular expressions', function (): void {
    configuration(['assume_used' => ['/valid/', '(unclosed']]);
})->throws(InvalidArgumentException::class, 'The [assume_used] option contains an invalid regular expression: (unclosed');

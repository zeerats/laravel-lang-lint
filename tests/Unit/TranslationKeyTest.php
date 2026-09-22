<?php

declare(strict_types=1);

use Zeerats\LangLint\TranslationKey;

it('parses keys that a PHP language file resolves', function (string $key, string $group, string $item): void {
    $translationKey = TranslationKey::tryFrom($key);

    expect($translationKey)->not->toBeNull()
        ->and($translationKey?->group)->toBe($group)
        ->and($translationKey?->item)->toBe($item);
})->with([
    ['messages.welcome', 'messages', 'welcome'],
    ['messages.nested.deep', 'messages', 'nested.deep'],
    ['admin/users.title', 'admin/users', 'title'],
    ['validation.custom.email.required', 'validation', 'custom.email.required'],
]);

it('rejects keys that no PHP language file resolves', function (string $key): void {
    expect(TranslationKey::tryFrom($key))->toBeNull();
})->with([
    'namespaced' => 'package::messages.welcome',
    'sentence' => 'A plain sentence.',
    'single word' => 'Welcome',
    'leading dot' => '.welcome',
    'trailing dot' => 'messages.',
    'whitespace' => 'messages.some words',
]);

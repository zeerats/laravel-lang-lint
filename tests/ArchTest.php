<?php

declare(strict_types=1);

arch()->preset()->php();

arch()->preset()->security();

arch('the package does not leak debugging helpers')
    ->expect(['dd', 'ddd', 'env', 'exit'])
    ->each->not->toBeUsed();

arch('the package declares strict types')
    ->expect('Zeerats\TranslationChecker')
    ->toUseStrictTypes();

arch('value objects are immutable')
    ->expect([
        'Zeerats\TranslationChecker\Configuration',
        'Zeerats\TranslationChecker\TranslationKey',
        'Zeerats\TranslationChecker\Results',
        'Zeerats\TranslationChecker\Scanning',
        'Zeerats\TranslationChecker\Lang',
    ])
    ->classes()
    ->toBeFinal()
    ->toBeReadonly();

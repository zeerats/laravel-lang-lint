<?php

declare(strict_types=1);

arch()->preset()->php();

arch()->preset()->security();

arch('the package does not leak debugging helpers')
    ->expect(['dd', 'ddd', 'env', 'exit'])
    ->each->not->toBeUsed();

arch('the package declares strict types')
    ->expect('Zeerats\LangLint')
    ->toUseStrictTypes();

arch('value objects are immutable')
    ->expect([
        'Zeerats\LangLint\Configuration',
        'Zeerats\LangLint\TranslationKey',
        'Zeerats\LangLint\Results',
        'Zeerats\LangLint\Scanning',
        'Zeerats\LangLint\Lang',
    ])
    ->classes()
    ->toBeFinal()
    ->toBeReadonly();

<?php

declare(strict_types=1);

use Zeerats\LangLint\Scanning\Location;
use Zeerats\LangLint\Scanning\UsedKeys;

it('covers a key when it or one of its parents is used', function (): void {
    $keys = new UsedKeys([
        'messages.types' => [new Location('app/Example.php', 3)],
        'messages.welcome' => [new Location('app/Example.php', 1), new Location('app/Other.php', 8)],
    ]);

    expect($keys->all())->toHaveCount(2)
        ->and($keys->covers('messages.welcome'))->toBeTrue()
        ->and($keys->covers('messages.types'))->toBeTrue()
        ->and($keys->covers('messages.types.image'))->toBeTrue()
        ->and($keys->covers('messages.types.image.small'))->toBeTrue()
        ->and($keys->covers('messages.welcome.extra'))->toBeTrue()
        ->and($keys->covers('messages.other'))->toBeFalse()
        ->and($keys->covers('messages'))->toBeFalse()
        ->and((string) $keys->all()['messages.welcome'][1])->toBe('app/Other.php:8');
});

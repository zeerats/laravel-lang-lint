<?php

declare(strict_types=1);

use UnexpectedValueException;
use Zeerats\TranslationChecker\Lang\PhpArrayWriter;

it('renders an empty file', function (): void {
    expect((new PhpArrayWriter)->render([]))->toBe("<?php\n\nreturn [];\n");
});

it('renders nested lines in the canonical style', function (): void {
    $lines = [
        'plain' => 'Plain',
        'quoted' => "It's \\ escaped",
        'multiline' => "First\nSecond",
        'nested' => ['deep' => ['deeper' => 'Deeper'], 'empty' => []],
        'list' => ['b', 'a'],
        'mixed' => [3 => 'Three', 'x' => 'X'],
        'count' => 7,
        'flag' => false,
        'nothing' => null,
    ];

    $rendered = (new PhpArrayWriter)->render($lines);

    expect($rendered)->toBe(<<<'FILE'
        <?php

        return [

            'plain' => 'Plain',
            'quoted' => 'It\'s \\ escaped',
            'multiline' => 'First
        Second',
            'nested' => [
                'deep' => [
                    'deeper' => 'Deeper',
                ],
                'empty' => [],
            ],
            'list' => [
                'b',
                'a',
            ],
            'mixed' => [
                3 => 'Three',
                'x' => 'X',
            ],
            'count' => 7,
            'flag' => false,
            'nothing' => null,

        ];

        FILE);

    $path = sys_get_temp_dir() . '/translation-checker-' . bin2hex(random_bytes(6)) . '.php';
    file_put_contents($path, $rendered);

    try {
        expect(require $path)->toBe($lines);
    } finally {
        unlink($path);
    }
});

it('rejects values that translation files cannot hold', function (): void {
    (new PhpArrayWriter)->render(['ratio' => 1.5]);
})->throws(UnexpectedValueException::class, 'float given');

<?php

declare(strict_types=1);

use UnexpectedValueException;
use Zeerats\LangLint\Lang\TranslationFile;
use Zeerats\LangLint\Tests\TestCase;

function translationFile(array $lines): TranslationFile
{
    return new TranslationFile('en', 'messages', '/srv/app/lang/en/messages.php', $lines);
}

it('loads a language file', function (): void {
    $file = TranslationFile::load('en', 'admin/users', TestCase::fixturePath('lang/en/admin/users.php'));

    expect($file->locale)->toBe('en')
        ->and($file->group)->toBe('admin/users')
        ->and($file->lines)->toBe(['title' => 'Users'])
        ->and($file->items())->toBe(['title']);
});

it('rejects a file that does not return an array', function (): void {
    $path = sys_get_temp_dir() . '/lang-lint-' . bin2hex(random_bytes(6)) . '.php';
    file_put_contents($path, '<?php return "text";');

    try {
        TranslationFile::load('en', 'broken', $path);
    } finally {
        unlink($path);
    }
})->throws(UnexpectedValueException::class, 'must return an array');

it('lists the items of nested lines', function (): void {
    expect(translationFile(['a' => 'A', 'b' => ['c' => 'C', 'd' => ['e' => 'E']], 'f' => [], 'g' => ['x', 'y']])->items())
        ->toBe(['a', 'b.c', 'b.d.e', 'f', 'g.0', 'g.1']);
});

it('removes items and the parents they leave empty', function (): void {
    $file = translationFile(['a' => 'A', 'b' => ['c' => 'C', 'd' => ['e' => 'E']], 'f' => ['g' => 'G']]);

    expect($file->without(['b.d.e', 'f.g'])->lines)->toBe(['a' => 'A', 'b' => ['c' => 'C']])
        ->and($file->without(['b.c', 'b.d.e'])->lines)->toBe(['a' => 'A', 'f' => ['g' => 'G']])
        ->and($file->without(['missing.item'])->lines)->toBe($file->lines)
        ->and($file->lines)->toHaveKey('b.d.e');
});

it('sorts nested keys and keeps lists in order', function (): void {
    $file = translationFile(['b' => ['z' => 'Z', 'a' => ['y' => 'Y', 'x' => 'X']], 'a' => ['b', 'a'], 'c' => 'C']);

    expect($file->sorted()->lines)->toBe(['a' => ['b', 'a'], 'b' => ['a' => ['x' => 'X', 'y' => 'Y'], 'z' => 'Z'], 'c' => 'C']);
});

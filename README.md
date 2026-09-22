# Laravel Lang Lint

[![Latest version on Packagist](https://img.shields.io/packagist/v/zeerats/laravel-lang-lint.svg)](https://packagist.org/packages/zeerats/laravel-lang-lint)
[![Tests](https://img.shields.io/github/actions/workflow/status/zeerats/laravel-lang-lint/tests.yml?branch=main&label=tests)](https://github.com/zeerats/laravel-lang-lint/actions/workflows/tests.yml)
[![License](https://img.shields.io/packagist/l/zeerats/laravel-lang-lint.svg)](LICENSE.md)

Artisan commands that keep PHP language files in sync with the code that uses them.

| Command | What it does | Exit status |
| --- | --- | --- |
| `lang:check` | Reports keys used in the source code that a language file does not define. | 1 when keys are missing |
| `lang:unused` | Reports keys in the language files that no code uses. `--prune` removes them. | 1 when keys are unused and not pruned |
| `lang:format` | Rewrites language files with sorted keys in one consistent style. `--check` only verifies. | 1 when `--check` finds unformatted files |

The commands never prompt. Files change only when you pass `--prune` or run `lang:format` without `--check`, so the commands are safe in git hooks, CI pipelines and any other non-interactive shell.

Invalid options or configuration, such as a locale without a directory, produce a one-line error and exit status 2.

## Requirements

- PHP 8.3 or newer
- Laravel 12 or 13

## Installation

```bash
composer require --dev zeerats/laravel-lang-lint
```

Publish the configuration file when the defaults do not fit your project:

```bash
php artisan vendor:publish --tag=lang-lint-config
```

## Usage

### Missing translations

```bash
php artisan lang:check
php artisan lang:check --show-locations
php artisan lang:check --locale=de --path=app/Http
```

```
+--------------------------+------------+
| Key                      | Missing in |
+--------------------------+------------+
| messages.only_in_en      | de         |
| projects.archive_confirm | de, fr     |
+--------------------------+------------+

   ERROR  Found 2 translation keys missing from at least one locale.
```

A key is missing when at least one locale does not define it. `--show-locations` adds a column with the files and lines that use each key.

### Unused translations

```bash
php artisan lang:unused
php artisan lang:unused --prune
```

Without `--prune` the command lists the unused keys with the locales that define them and exits with status 1. With `--prune` it removes the keys and rewrites the affected files in the canonical style described below. Parents that a removal leaves empty are removed as well.

### Formatting

```bash
php artisan lang:format
php artisan lang:format --check
php artisan lang:format --prune
```

`lang:format` rewrites every language file of the selected locales in one canonical style:

- keys are sorted recursively by byte value, lists keep their order
- four spaces of indentation, single-quoted strings and a trailing comma after every entry
- a blank line after `return [` and before `];`, matching the language files Laravel ships
- comments are dropped, because the file is rendered from its data

`--check` reports the files that would change and exits with status 1, which makes it a formatting gate for CI. `--prune` removes unused keys in the same pass.

### Options shared by all commands

| Option | Effect |
| --- | --- |
| `--path=app --path=routes` | Scan these directories instead of the configured `paths`. Relative to the base path. |
| `--locale=en --locale=de` | Work on these locales instead of every discovered one. |

## Configuration

`config/lang-lint.php`:

```php
return [
    // Directories scanned for translation calls, relative to the base path.
    'paths' => ['app', 'resources/views'],

    // Functions, static calls and Blade directives whose first argument is a key.
    'functions' => ['__', 'trans', 'trans_choice', '@lang', '@choice', 'Lang::get', 'Lang::has', 'Lang::choice'],

    // Locales to check. Empty: every directory in the language path except vendor.
    'locales' => [],

    // Regular expressions for locale directories to skip during discovery.
    'ignore_locales' => [],

    // Regular expressions for keys that count as used without a literal call.
    'assume_used' => ['/^auth\./', '/^pagination\./', '/^passwords\./', '/^validation\./'],
];
```

`assume_used` covers two situations: keys the framework reads itself, such as validation messages, and keys your code builds at runtime, such as `__("statuses.{$status}")`. Both are never reported as unused and never pruned. Keys that appear literally in the code are still checked for missing translations.

## How keys are detected

- Every `.php` file below the configured paths is scanned, so Blade templates are included.
- A call counts when its first argument is a single string literal, such as `__('messages.welcome')`, also when the call spans several lines.
- Interpolated keys such as `__("messages.{$type}")` and concatenated keys such as `__('messages.' . $type)` cannot be resolved statically and are skipped. Add a pattern to `assume_used` so their targets are kept.
- Namespaced keys such as `package::messages.welcome` belong to packages and are skipped.
- Strings without a dot or with whitespace are JSON translation strings and are skipped. The package checks PHP language files only.
- A key used as a prefix marks everything below it as used: `__('messages.types')` returns the whole array, so `messages.types.image` counts as used.
- Detection is textual. A call inside a comment counts as a usage, and a call made through a variable function name does not.

## Continuous integration

GitLab CI:

```yaml
translations:
  script:
    - php artisan lang:check
    - php artisan lang:format --check
```

GitHub Actions:

```yaml
- run: php artisan lang:check
- run: php artisan lang:format --check
```

A pre-commit hook can run the same two commands.

## Development

```bash
composer test
```

This runs PHPStan, Pint, Pest's type coverage and the test suite.

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

MIT, see [LICENSE.md](LICENSE.md).

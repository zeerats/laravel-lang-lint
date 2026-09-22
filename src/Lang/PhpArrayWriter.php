<?php

declare(strict_types=1);

namespace Zeerats\LangLint\Lang;

use UnexpectedValueException;

/**
 * Renders translation lines as a PHP file in one canonical style: four-space
 * indentation, single-quoted strings, a trailing comma after every entry and
 * lists without explicit keys.
 */
final readonly class PhpArrayWriter
{
    /**
     * @param  array<array-key, mixed>  $lines
     */
    public function render(array $lines): string
    {
        if ($lines === []) {
            return "<?php\n\nreturn [];\n";
        }

        return "<?php\n\nreturn [\n\n" . $this->entries($lines, 1) . "\n];\n";
    }

    /**
     * @param  array<array-key, mixed>  $array
     */
    private function entries(array $array, int $depth): string
    {
        $indent = str_repeat('    ', $depth);
        $list = array_is_list($array);
        $entries = '';

        foreach ($array as $key => $value) {
            $entries .= $indent . ($list ? '' : self::scalar($key) . ' => ') . $this->value($value, $depth) . ",\n";
        }

        return $entries;
    }

    private function value(mixed $value, int $depth): string
    {
        if (! is_array($value)) {
            return self::scalar($value);
        }

        if ($value === []) {
            return '[]';
        }

        return "[\n" . $this->entries($value, $depth + 1) . str_repeat('    ', $depth) . ']';
    }

    private static function scalar(mixed $value): string
    {
        return match (true) {
            is_string($value) => "'" . addcslashes($value, "\\'") . "'",
            is_int($value) => (string) $value,
            is_bool($value) => $value ? 'true' : 'false',
            $value === null => 'null',
            default => throw new UnexpectedValueException(
                'Translation files may only contain strings, integers, booleans, null and arrays, ' . get_debug_type($value) . ' given.',
            ),
        };
    }
}

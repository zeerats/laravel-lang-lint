<?php

declare(strict_types=1);

namespace Zeerats\TranslationChecker\Scanning;

use InvalidArgumentException;
use Symfony\Component\Finder\Finder;
use Zeerats\TranslationChecker\Configuration;

/**
 * Finds translation keys passed as string literals to the configured functions.
 */
final readonly class KeyExtractor
{
    public function __construct(private Configuration $configuration) {}

    public function extract(): UsedKeys
    {
        $pattern = $this->pattern();
        $locations = [];

        foreach ($this->sources() as $file) {
            $contents = $file->getContents();
            $path = $this->configuration->relativePath($file->getPathname());

            preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

            foreach ($matches as $match) {
                $key = self::literal($match['quote'][0], $match['key'][0]);

                if ($key === null) {
                    continue;
                }

                $line = substr_count(substr($contents, 0, $match[0][1]), "\n") + 1;
                $locations[$key][] = new Location($path, $line);
            }
        }

        ksort($locations, SORT_STRING);

        return new UsedKeys($locations);
    }

    private function sources(): Finder
    {
        $paths = $this->configuration->scanPaths();

        foreach ($paths as $path) {
            if (! is_dir($path)) {
                throw new InvalidArgumentException("Scan path [{$path}] is not a directory.");
            }
        }

        return Finder::create()->files()->name('*.php')->in($paths)->followLinks()->sortByName();
    }

    /**
     * Matches a configured function whose first argument is a complete string
     * literal, possibly spread over several lines. The function name must not
     * be part of a longer identifier, a variable, a method call or a static
     * call on another class.
     */
    private function pattern(): string
    {
        $functions = implode('|', array_map(
            static fn (string $function): string => preg_quote($function, '/'),
            $this->configuration->functions,
        ));

        return '/(?<![\w$:]|->)(?:' . $functions . ')\(\s*(?<quote>[\'"])(?<key>(?:\\\\.|(?!\k<quote>).)*+)\k<quote>\s*[,)]/s';
    }

    /**
     * The value of the string literal, or null when the literal is interpolated.
     */
    private static function literal(string $quote, string $raw): ?string
    {
        if ($quote === '"') {
            return str_contains($raw, '$') ? null : stripcslashes($raw);
        }

        return strtr($raw, ['\\\\' => '\\', "\\'" => "'"]);
    }
}

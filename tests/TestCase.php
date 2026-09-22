<?php

declare(strict_types=1);

namespace Zeerats\TranslationChecker\Tests;

use Illuminate\Filesystem\Filesystem;
use Orchestra\Testbench\TestCase as Orchestra;
use Zeerats\TranslationChecker\TranslationCheckerServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * @var list<string>
     */
    private array $temporaryDirectories = [];

    protected function getPackageProviders($app): array
    {
        return [
            TranslationCheckerServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('translation-checker.ignore_locales', ['/^[a-z]{2}-[A-Z]{2}$/']);
    }

    protected function tearDown(): void
    {
        $filesystem = new Filesystem;

        foreach ($this->temporaryDirectories as $directory) {
            $filesystem->deleteDirectory($directory);
        }

        parent::tearDown();
    }

    public static function fixturePath(string $path = ''): string
    {
        return __DIR__ . '/Fixtures/app' . ($path === '' ? '' : '/' . $path);
    }

    /**
     * Point the application at the fixture application. Nothing may write to it.
     */
    protected function useFixture(): string
    {
        return $this->useApplicationAt(self::fixturePath());
    }

    /**
     * Point the application at a disposable copy of the fixture application.
     */
    protected function useFixtureCopy(): string
    {
        $path = $this->temporaryDirectory();

        (new Filesystem)->copyDirectory(self::fixturePath(), $path);

        return $this->useApplicationAt($path);
    }

    /**
     * Point the application at an empty temporary directory holding only the given subdirectories.
     *
     * @param  list<string>  $directories
     */
    protected function useEmptyApplication(array $directories): string
    {
        $path = $this->temporaryDirectory();

        foreach ($directories as $directory) {
            (new Filesystem)->ensureDirectoryExists($path . '/' . $directory);
        }

        return $this->useApplicationAt($path);
    }

    /**
     * A fresh directory path that is deleted when the test ends.
     */
    private function temporaryDirectory(): string
    {
        $path = sys_get_temp_dir() . '/translation-checker-' . bin2hex(random_bytes(6));

        $this->temporaryDirectories[] = $path;

        return $path;
    }

    private function useApplicationAt(string $path): string
    {
        $this->app->setBasePath($path);
        $this->app->useLangPath($path . '/lang');

        return $path;
    }
}

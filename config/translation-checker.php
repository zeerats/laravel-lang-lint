<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    |
    | The directories that are scanned for translation calls, relative to the
    | base path of the application. Every PHP and Blade file below them is
    | read. The --path option of the commands overrides this list.
    |
    */

    'paths' => [
        'app',
        'resources/views',
    ],

    /*
    |--------------------------------------------------------------------------
    | Functions
    |--------------------------------------------------------------------------
    |
    | The functions, static calls and Blade directives whose first argument
    | is a translation key. Only calls that pass a plain string literal are
    | recognised; interpolated and concatenated keys cannot be resolved.
    |
    */

    'functions' => [
        '__',
        'trans',
        'trans_choice',
        '@lang',
        '@choice',
        'Lang::get',
        'Lang::has',
        'Lang::choice',
    ],

    /*
    |--------------------------------------------------------------------------
    | Locales
    |--------------------------------------------------------------------------
    |
    | The locales to check. Leave the list empty to check every directory in
    | the language path except vendor. The --locale option of the commands
    | overrides this list.
    |
    */

    'locales' => [],

    /*
    |--------------------------------------------------------------------------
    | Ignore Locales
    |--------------------------------------------------------------------------
    |
    | Regular expressions matching locale directories that are skipped when
    | the locales are discovered, for example regional variants that only
    | override a few lines of their base locale.
    |
    */

    'ignore_locales' => [
        // '/^[a-z]{2}-[A-Z]{2}$/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Assume Used
    |--------------------------------------------------------------------------
    |
    | Regular expressions matching keys that count as used although no call
    | passes them literally: keys the framework reads itself and keys built
    | from dynamic values. They are never reported as unused and never
    | pruned. Keys that are used literally are still checked for missing
    | translations.
    |
    */

    'assume_used' => [
        '/^auth\./',
        '/^pagination\./',
        '/^passwords\./',
        '/^validation\./',
    ],

];

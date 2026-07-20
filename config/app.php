<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Application Name
     |--------------------------------------------------------------------------
     |
     | This value is the name of your application, which will be used when the
     | framework needs to place the application's name in a notification or
     | other UI elements where an application name needs to be displayed.
     |
     */

    'name' => env('APP_NAME', 'Laravel'),

    /*
     |--------------------------------------------------------------------------
     | Application Environment
     |--------------------------------------------------------------------------
     |
     | This value determines the "environment" your application is currently
     | running in. This may determine how you prefer to configure various
     | services the application utilizes. Set this in your ".env" file.
     |
     */

    'env' => env('APP_ENV', 'production'),

    /*
     |--------------------------------------------------------------------------
     | Application Debug Mode
     |--------------------------------------------------------------------------
     |
     | When your application is in debug mode, detailed error messages with
     | stack traces will be shown on every error that occurs within your
     | application. If disabled, a simple generic error page is shown.
     |
     */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
     |--------------------------------------------------------------------------
     | Application URL
     |--------------------------------------------------------------------------
     |
     | This URL is used by the console to properly generate URLs when using
     | the Artisan command line tool. You should set this to the root of
     | the application so that it's available within Artisan commands.
     |
     */

    'url' => env('APP_URL', 'http://localhost'),

    /*
     |--------------------------------------------------------------------------
     | Application Timezone
     |--------------------------------------------------------------------------
     |
     | The business runs in Peru and every date in the schema is a local business
     | date, not an instant: legacy calls date_default_timezone_set('America/Lima')
     | at the top of each write, so the production rows are already Lima dates.
     | Leaving this on Laravel's UTC default meant now() and the hand-stamped
     | Carbon::now('America/Lima') disagreed for the five hours between 19:00 Lima
     | and midnight UTC, which is long enough to file a case under tomorrow and
     | to hide today's contacts from a report whose range was built in Lima.
     | Setting it here makes now() the one clock and removes the per-call-site
     | timezone argument that was the workaround.
     |
     */

    'timezone' => 'America/Lima',

    /*
     |--------------------------------------------------------------------------
     | Application Locale Configuration
     |--------------------------------------------------------------------------
     |
     | The application locale determines the default locale that will be used
     | by Laravel's translation / localization methods. This option can be
     | set to any locale for which you plan to have translation strings.
     |
     */

    // The app is only ever Spanish, so that is the default rather than a value
    // each environment has to remember to set.
    'locale' => env('APP_LOCALE', 'es'),

    // Deliberately not from the environment. The fallback is the safety net that
    // keeps an untranslated key from reaching a user, and pointing it at `es`
    // (which is what the .env used to do) disables it: the framework ships only
    // `en`, so every rule lang/es/validation.php does not cover rendered as the
    // raw "validation.<rule>" string on screen.
    'fallback_locale' => 'en',

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
     |--------------------------------------------------------------------------
     | Encryption Key
     |--------------------------------------------------------------------------
     |
     | This key is utilized by Laravel's encryption services and should be set
     | to a random, 32 character string to ensure that all encrypted values
     | are secure. You should do this prior to deploying the application.
     |
     */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', '')),
        ),
    ],

    /*
     |--------------------------------------------------------------------------
     | Maintenance Mode Driver
     |--------------------------------------------------------------------------
     |
     | These configuration options determine the driver used to determine and
     | manage Laravel's "maintenance mode" status. The "cache" driver will
     | allow maintenance mode to be controlled across multiple machines.
     |
     | Supported drivers: "file", "cache"
     |
     */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],
];

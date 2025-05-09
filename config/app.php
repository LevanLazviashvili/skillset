<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    | You can create a CMS page with route "/error" to set the contents
    | of this page. Otherwise a default error page is shown.
    |
    */

    'debug' => env('APP_DEBUG', false),

    'versioning'    => [
      'required_app_version_android'    => '1.37',
      'required_app_version_ios'    => '1.34'
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    */

    'name' => 'Skillset',

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'https://api.skillset.ge/'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    |
    | -------- STOP! --------
    | Before you change this value, consider carefully if that is actually
    | what you want to do. It is HIGHLY recommended that this is always set
    | to UTC (as your server & DB timezone should be as well) and instead you
    | use cms.backendTimezone to set the default timezone used in the backend
    | to display dates & times.
    |
    */

    'timezone' => 'Asia/Tbilisi',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    | WARNING: Avoid setting this to a locale that is not supported by the
    | backend yet, as this can cause issues in the backend.
    |
    | Currently supported backend locales are listed in
    | Backend\Models\Preference->getLocaleOptions())
    |
    */

    'locale' => 'ka',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'ka',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY', ''),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log settings for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Settings: "single", "daily", "syslog", "errorlog"
    |
    */

    'log' => 'single',

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => array_merge(include(base_path('modules/system/providers.php')), [

        // 'Illuminate\Html\HtmlServiceProvider', // Example

        'System\ServiceProvider',
    ]),

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => array_merge(include(base_path('modules/system/aliases.php')), [

        // 'Str' => 'Illuminate\Support\Str', // Example

    ]),

    'statuses' => [
        'active'    => 1,
        'unactive'  => 0
    ],

    'services' => [
        'max_sub_services_per_user' => 5
    ],

    'default_error_code' => 600,
    'default_lang'       => 'ka',

    'payments' => [
        'bog' => [
            'client_id'     => env('BOG_CLIENT_ID'),
            'secret_key'    => env('BOG_SECRET_KEY'),
            'auth_url'      => 'https://ipay.ge/opay/api/v1/oauth2/token',
            'order_url'     => 'https://ipay.ge/opay/api/v1/checkout/orders',
            'order_details' => 'https://ipay.ge/opay/api/v1/checkout/orders/%s'
        ]
    ],
    'firebase' => [
        'config_path' => env('FIREBASE_CONFIG_PATH', 'app/firebase.json'),
        'api_url' => env('FIREBASE_API_URL', ''),
        'batch_add_api_url' => env('FIREBASE_BATCH_ADD_API_URL', ''),
        'batch_remove_api_url' => env('FIREBASE_BATCH_REMOVE_API_URL', ''),
    ],
    'system_messages' => [
        'url'      => env('SYSTEM_MESSAGES_URL'),
        'username' => env('SYSTEM_USERNAME'),
        'password' => env('SYSTEM_PASSWORD')
    ],
    'admin_secret' => env('ADMIN_SECRET', '1RzTMzO0$fn9eVsICf9JHRvteWuSpzTuib'),

    'chat' => [
        'app_url' => env('CHAT_APP_URL')
    ]

];

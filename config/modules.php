<?php

use Nwidart\Modules\Activators\FileActivator;
use Nwidart\Modules\Providers\ConsoleServiceProvider;

return [
    'namespace' => 'Modules',
    'stubs' => [
        'enabled' => false,
        'path' => base_path('stubs/modules'),
        'files' => [
            'routes/web' => 'routes.php',
            'scaffold/config' => 'config/config.php',
            'composer' => 'composer.json',
        ],
        'replacements' => [
            'routes/web' => ['LOWER_NAME', 'STUDLY_NAME', 'PLURAL_LOWER_NAME', 'KEBAB_NAME', 'MODULE_NAMESPACE', 'CONTROLLER_NAMESPACE'],
            'routes/api' => ['LOWER_NAME', 'STUDLY_NAME', 'PLURAL_LOWER_NAME', 'KEBAB_NAME', 'MODULE_NAMESPACE', 'CONTROLLER_NAMESPACE'],
            'vite' => ['LOWER_NAME', 'STUDLY_NAME', 'KEBAB_NAME'],
            'json' => ['LOWER_NAME', 'STUDLY_NAME', 'KEBAB_NAME', 'MODULE_NAMESPACE', 'PROVIDER_NAMESPACE'],
            'views/index' => ['LOWER_NAME'],
            'views/master' => ['LOWER_NAME', 'STUDLY_NAME', 'KEBAB_NAME'],
            'scaffold/config' => ['STUDLY_NAME'],
            'composer' => [
                'LOWER_NAME',
                'STUDLY_NAME',
                'VENDOR',
                'AUTHOR_NAME',
                'AUTHOR_EMAIL',
                'MODULE_NAMESPACE',
                'PROVIDER_NAMESPACE',
                'APP_FOLDER_NAME',
            ],
        ],
        'gitkeep' => true,
    ],
    'paths' => [
        'modules' => base_path('modules'),
        'assets' => public_path('modules'),
        'migration' => base_path('database/migrations'),
        'app_folder' => 'app/',
        'generator' => [
            'admin' => ['path' => 'app/Admin', 'generate' => true],
            'actions' => ['path' => 'app/Actions', 'generate' => true],
            'event' => ['path' => 'app/Events', 'generate' => true],
            'event-provider' => ['path' => 'app/Providers', 'generate' => false],
            'helpers' => ['path' => 'app/Helpers', 'generate' => false],
            'interfaces' => ['path' => 'app/Interfaces', 'generate' => false],
            'model' => ['path' => 'app/Models', 'generate' => true],
            'notifications' => ['path' => 'app/Notifications', 'generate' => false],
            'provider' => ['path' => 'app/Providers', 'generate' => true],
            'route-provider' => ['path' => 'app/Providers', 'generate' => false],
            'services' => ['path' => 'app/Services', 'generate' => true],

            'config' => ['path' => 'config', 'generate' => true],

            'factory' => ['path' => 'database/factories', 'generate' => true],
            'migration' => ['path' => 'database/migrations', 'generate' => true],
            'seeder' => ['path' => 'database/seeders', 'generate' => true],

            // lang/
            'lang' => ['path' => 'lang', 'generate' => true],
            'assets' => ['path' => 'resources/assets', 'generate' => false],
            'component-view' => ['path' => 'resources/views/components', 'generate' => false],
            'views' => ['path' => 'resources/views', 'generate' => false],

            // routes/
            'routes' => ['path' => 'routes', 'generate' => false],

            // tests/
            'test-feature' => ['path' => 'tests/Feature', 'generate' => false],
            'test-unit' => ['path' => 'tests/Unit', 'generate' => false],
        ],
    ],
    'auto-discover' => [
        'migrations' => true,
        'translations' => false,

    ],
    'commands' => ConsoleServiceProvider::defaultCommands()
        ->merge([])->toArray(),
    'scan' => [
        'enabled' => false,
        'paths' => [
            base_path('vendor/*/*'),
        ],
    ],
    'composer' => [
        'vendor' => env('MODULE_VENDOR', 'smart-cms'),
        'author' => [
            'name' => env('MODULE_AUTHOR_NAME', 'support@s-cms.dev'),
            'email' => env('MODULE_AUTHOR_EMAIL', 'support@s-cms.dev'),
        ],
        'composer-output' => false,
    ],
    'register' => [
        'translations' => true,
        'files' => 'register',
    ],
    'activators' => [
        'file' => [
            'class' => FileActivator::class,
            'statuses-file' => base_path('modules.json'),
        ],
    ],
    'activator' => 'file',
];

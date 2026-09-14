<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Package Defaults
    |--------------------------------------------------------------------------
    |
    | Global options for generated CRUD resources, pagination, date formats,
    | and behavior across the generated Filament interface.
    |
    */
    'defaults' => [
        'per_page' => 25,
        'per_page_options' => [10, 25, 50, 100],
        'date_format' => 'Y-m-d',
        'datetime_format' => 'Y-m-d H:i:s',
        'time_format' => 'H:i:s',
        'calendar' => 'gregorian', // 'gregorian' or 'tarikh' (for Bangla calendar support)
        'enable_export' => true,
        'enable_bulk_delete' => true,
        'enable_global_search' => true,
        'enable_soft_deletes' => true,
        'activity_log_columns' => [
            'created_by' => 'created_by',
            'updated_by' => 'updated_by',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Database Columns & Tables
    |--------------------------------------------------------------------------
    */
    'excluded_columns' => [
        'id',
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ],

    'excluded_tables' => [
        'migrations',
        'failed_jobs',
        'password_reset_tokens',
        'password_resets',
        'personal_access_tokens',
        'sessions',
        'jobs',
        'job_batches',
        'cache',
        'cache_locks',
    ],

    /*
    |--------------------------------------------------------------------------
    | Adhikar RBAC Integration
    |--------------------------------------------------------------------------
    */
    'adhikar' => [
        'enabled' => true,
        'auto_register_permissions' => true,
        'permissions' => [
            'view_any' => 'view-any :model',
            'view' => 'view :model',
            'create' => 'create :model',
            'update' => 'update :model',
            'delete' => 'delete :model',
            'delete_any' => 'delete-any :model',
            'restore' => 'restore :model',
            'force_delete' => 'force-delete :model',
            'export' => 'export :model',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Visual Panel Features (In-Browser Studio)
    |--------------------------------------------------------------------------
    */
    'visual_panel' => [
        'enabled' => false,
        'navigation_group' => 'System Studio',
        'navigation_sort' => 99,
        'features' => [
            'module_builder' => true,
            'migration_builder' => true,
            'controller_generator' => true,
            'route_manager' => true,
            'data_forge' => true,
            'widget_studio' => true,
            'schema_viewer' => true,
        ],
        'auto_migrate' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Code Generation Paths & Namespaces
    |--------------------------------------------------------------------------
    */
    'paths' => [
        'models' => app_path('Models'),
        'resources' => app_path('Filament/Resources'),
        'widgets' => app_path('Filament/Widgets'),
        'controllers' => app_path('Http/Controllers'),
        'requests' => app_path('Http/Requests'),
        'api_resources' => app_path('Http/Resources'),
        'policies' => app_path('Policies'),
        'factories' => database_path('factories'),
        'seeders' => database_path('seeders'),
        'migrations' => database_path('migrations'),
        'tests' => base_path('tests/Feature'),
    ],

    'namespaces' => [
        'models' => 'App\\Models',
        'resources' => 'App\\Filament\\Resources',
        'widgets' => 'App\\Filament\\Widgets',
        'controllers' => 'App\\Http\\Controllers',
        'requests' => 'App\\Http\\Requests',
        'api_resources' => 'App\\Http\\Resources',
        'policies' => 'App\\Policies',
        'factories' => 'Database\\Factories',
        'seeders' => 'Database\\Seeders',
    ],
];

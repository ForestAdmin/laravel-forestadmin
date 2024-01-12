<?php

return [
    'debug'                => env('FOREST_DEBUG', true),
    'authSecret'           => env('FOREST_AUTH_SECRET'),
    'envSecret'            => env('FOREST_ENV_SECRET'),
    'forestServerUrl'      => env('FOREST_SERVER_URL', 'https://api.forestadmin.com'),
    'isProduction'         => env('FOREST_ENVIRONMENT', 'dev') === 'prod',
    'prefix'               => env('FOREST_PREFIX', 'forest'),
    'permissionExpiration' => env('FOREST_PERMISSIONS_EXPIRATION_IN_SECONDS', 300),
    'cacheDir'             => storage_path('framework/cache/data/forest'),
    'schemaPath'           => base_path() . '/.forestadmin-schema.json',
    'projectDir'           => base_path(),
];
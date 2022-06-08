<?php

return [
    'models_directory'    => ['app/Models'],
    'json_file_path'        => env('JSON_FILE_PATH', '.forestadmin-schema.json'),
    'send_apimap_automatic' => env('FOREST_SEND_APIMAP_AUTOMATIC', false),
    'api'                   => [
        'url'         => env('FOREST_URL', 'https://api.forestadmin.com'),
        'secret'      => env('FOREST_ENV_SECRET'),
        'auth-secret' => env('FOREST_AUTH_SECRET'),
    ],
    'included_models' => [],
    'excluded_models' => [],
];

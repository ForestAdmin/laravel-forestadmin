<?php

return [
    'models_directory' => env('MODEL_DIRECTORY', 'app/Models/'),
    'json_file_path'   => env('JSON_FILE_PATH', '.forestadmin-schema.json'),
    'route_prefix'     => env('FOREST_ROUTE_PREFIX', 'forest'),
    'api'   => [
        'url'              => env('FOREST_URL', 'https://api.forestadmin.com'),
        'secret'           => env('FOREST_ENV_SECRET'),
    ]
];

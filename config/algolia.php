<?php
return [
    'app_id' => env('ALGOLIA_APP_ID', ''),
    'api_key' => env('ALGOLIA_API_KEY', ''),
    'index_name' => env('ALGOLIA_INDEX_NAME', ''),
    'application_id_mcm' => env('ALGOLIA_APPLICATION_ID_MCM', ''),
    'admin_key_mcm' => env('ALGOLIA_ADMIN_KEY_MCM', ''),

    'prefixes' => [
        'url' => env('SEARCH_ROOT_URL'),
        'image' => env('SEARCH_IMAGE_URL')
    ],

    'rankings' => [
        'category_ranking' => [
            'Juice',
            '100% Juices',
            'Juice Drinks',
            'Refrigerated Juice',
            'Light Juice',
            'Spreads',
            'Sparkling Juices',
            'Snacks',
        ],

    ]
];
<?php
return [
    'backend' => [
        'frontName' => 'adminH721xtdU040OAhR'
    ],
    'crypt' => [
        'key' => 'b719c6e821f4e55409f073253bab52d3'
    ],
    'db' => [
        'table_prefix' => '',
        'connection' => [
            'default' => [
                'host' => 'localhost',
                'dbname' => 'drberg',
                'username' => 'rocklab123',
                'password' => 'FYgubm4lQi',
                'active' => '1',
                'model' => 'mysql4',
                'engine' => 'innodb',
                'initStatements' => 'SET NAMES utf8;',
                'driver_options' => [
                    1014 => false
                ]
            ],
            'indexer' => [
                'host' => 'localhost',
                'dbname' => 'drberg',
                'username' => 'rocklab123',
                'password' => 'FYgubm4lQi',
                'model' => 'mysql4',
                'engine' => 'innodb',
                'initStatements' => 'SET NAMES utf8;',
                'active' => '1',
                'persistent' => null
            ]
        ]
    ],
    'session' => [
        'save' => 'files'
    ],
    'resource' => [
        'default_setup' => [
            'connection' => 'default'
        ]
    ],
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => 'production',
    'cache_types' => [
        'config' => 1,
        'layout' => 1,
        'block_html' => 1,
        'collections' => 1,
        'reflection' => 1,
        'db_ddl' => 1,
        'eav' => 1,
        'customer_notification' => 1,
        'full_page' => 1,
        'config_integration' => 1,
        'config_integration_api' => 1,
        'translate' => 1,
        'config_webservice' => 1,
        'compiled_config' => 1,
        'wp_gtm_categories' => 1,
        'target_rule' => 1,
        'google_product' => 1,
        'amasty_shopby' => 1,
        'checkout' => 0
    ],
    'install' => [
        'date' => 'Thu, 26 Apr 2018 11:55:27 +0000'
    ],
    'system' => [
        'default' => [
            'dev' => [
                'debug' => [
                    'debug_logging' => '0'
                ]
            ]
        ]
    ],
    'downloadable_domains' => [
        'www.drberg.com'
    ],
    'queue' => [
        'consumers_wait_for_messages' => 1
    ],
    'cache' => [
        'frontend' => [
            'default' => [
                'id_prefix' => '40d_'
            ],
            'page_cache' => [
                'id_prefix' => '40d_'
            ]
        ]
    ],
    'lock' => [
        'provider' => 'db',
        'config' => [
            'prefix' => ''
        ]
    ],
    'db_logger' => [
        'output' => 'disabled',
        'log_everything' => 1,
        'query_time_threshold' => '0.001',
        'include_stacktrace' => 1
    ]
];

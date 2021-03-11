<?php

$container->loadFromExtension('framework', [
    'test' => true,
    'session' => [
        'storage_id' => isset($_SERVER['TESTING_WITH_SESSION']) ? 'session.storage.native' : 'session.storage.mock_file',
        'handler_id' => 'session.handler.native_file',
    ],
    'cache' => [
        'app' => 'cache.adapter.apcu',
        'pools' => [],
    ],
]);

<?php

$container->loadFromExtension('framework', [
    'test' => true,
    'session' => [
        'storage_id' => isset($_SERVER['TESTING_WITH_SESSION']) ? 'session.storage.native' : 'session.storage.mock_file',
        'handler_id' => 'session.handler.native_file',
    ],
    'cache' => [
        'app' => 'cache.adapter.filesystem',
        'pools' => [
            'rsi.citizens.cache' => ['adapter' => 'cache.adapter.filesystem'],
            'rsi.organizations.cache' => ['adapter' => 'cache.adapter.filesystem'],
            'rsi.organizations_members.cache' => ['adapter' => 'cache.adapter.filesystem'],
            'rsi.ships.cache' => ['adapter' => 'cache.adapter.filesystem'],
        ],
    ],
]);

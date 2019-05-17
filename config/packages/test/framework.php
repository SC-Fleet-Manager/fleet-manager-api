<?php

$container->loadFromExtension('framework', [
    'test' => true,
    'session' => [
        'storage_id' => isset($_SERVER['TESTING_WITH_SESSION']) ? 'session.storage.native' : 'session.storage.mock_file',
    ],
]);

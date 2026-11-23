<?php

declare(strict_types=1);

return [
    'db' => [
        'driver' => 'sqlite',
        'path' => __DIR__ . '/database.sqlite',
    ],
    'template_engine' => $_GET['engine'] ?? 'native',
];

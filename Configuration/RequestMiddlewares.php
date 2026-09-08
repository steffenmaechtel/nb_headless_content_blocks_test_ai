<?php

declare(strict_types=1);

return [
    'frontend' => [
        'nb-headless-content-blocks/schema-endpoint' => [
            'target' => \Netzbewegung\NbHeadlessContentBlocks\Middleware\SchemaEndpointMiddleware::class,
            'after' => [
                'typo3/cms-frontend/site',
            ],
            'before' => [
                'typo3/cms-frontend/base-redirect-resolver',
                'typo3/cms-frontend/page-resolver',
            ],
        ],
    ],
];

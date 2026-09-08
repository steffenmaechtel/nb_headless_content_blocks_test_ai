<?php

declare(strict_types=1);

use Netzbewegung\NbHeadlessContentBlocks\Middleware\JsonSchemaEndpoint;

/**
 * The JSON Schema endpoint runs before site resolution, so the schemas are
 * served independently of site bases and page routing. It passes the request
 * through unless it is enabled in the extension configuration.
 */
return [
    'frontend' => [
        'netzbewegung/nb-headless-content-blocks/json-schema' => [
            'target' => JsonSchemaEndpoint::class,
            'after' => [
                'typo3/cms-core/normalized-params-attribute',
            ],
            'before' => [
                'typo3/cms-frontend/site',
            ],
        ],
    ],
];

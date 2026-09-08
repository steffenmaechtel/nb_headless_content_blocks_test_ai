<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Schema;

/**
 * Decides whether the JSON Schema endpoint may serve a request.
 *
 * - A configured token is always required, in any application context
 *   (this also protects non-production environments that set one).
 * - Without a token the endpoint is public outside the production
 *   context and disabled inside it.
 */
final class SchemaApiAccess
{
    public static function isAllowed(bool $isProduction, string $configuredToken, string $providedToken): bool
    {
        if ($configuredToken !== '') {
            return hash_equals($configuredToken, $providedToken);
        }
        return !$isProduction;
    }
}

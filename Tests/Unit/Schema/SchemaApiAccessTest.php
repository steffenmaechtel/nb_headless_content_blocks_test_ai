<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\Schema;

use Netzbewegung\NbHeadlessContentBlocks\Schema\SchemaApiAccess;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class SchemaApiAccessTest extends UnitTestCase
{
    #[Test]
    public function isPublicOutsideProductionWithoutToken(): void
    {
        self::assertTrue(SchemaApiAccess::isAllowed(false, '', ''));
    }

    #[Test]
    public function isBlockedInProductionWithoutToken(): void
    {
        self::assertFalse(SchemaApiAccess::isAllowed(true, '', ''));
    }

    #[Test]
    public function configuredTokenIsRequiredInAnyContext(): void
    {
        self::assertFalse(SchemaApiAccess::isAllowed(false, 'secret', ''));
        self::assertFalse(SchemaApiAccess::isAllowed(false, 'secret', 'wrong'));
        self::assertTrue(SchemaApiAccess::isAllowed(false, 'secret', 'secret'));
        self::assertTrue(SchemaApiAccess::isAllowed(true, 'secret', 'secret'));
    }
}

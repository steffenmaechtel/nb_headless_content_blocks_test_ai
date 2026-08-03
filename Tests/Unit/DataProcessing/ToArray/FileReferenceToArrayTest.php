<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class FileReferenceToArrayTest extends UnitTestCase
{
    public function testFileReferenceIsConvertedToArray(): void
    {
        self::markTestSkipped('Needs ImageService mock for TYPO3 v14+');
    }

    public function testEmptyConversionReturnsId(): void
    {
        self::markTestSkipped('Needs ImageService mock for TYPO3 v14+');
    }

    public function testNullAltReturnsEmptyString(): void
    {
        self::markTestSkipped('Needs ImageService mock for TYPO3 v14+');
    }

    public function testNullTitleReturnsEmptyString(): void
    {
        self::markTestSkipped('Needs ImageService mock for TYPO3 v14+');
    }

    public function testEmptyCropReturnsPublicUrl(): void
    {
        self::markTestSkipped('Needs ImageService mock for TYPO3 v14+');
    }

    public function testWithCropReturnsProcessedUrl(): void
    {
        self::markTestSkipped('Needs ImageService mock for TYPO3 v14+');
    }

    public function testWithNullAltReturnsEmptyString(): void
    {
        self::markTestSkipped('Needs ImageService mock for TYPO3 v14+');
    }

    public function testWithNullTitleReturnsEmptyString(): void
    {
        self::markTestSkipped('Needs ImageService mock for TYPO3 v14+');
    }
}

<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\FileReferenceToArray;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class FileReferenceToArrayTest extends UnitTestCase
{
    public function testConvertsSingleFileReference(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('get')->willReturn('file_reference_123');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file_reference', $result);
        self::assertSame('file_reference_123', $result['file_reference']);
    }

    public function testConvertsNullToNull(): void
    {
        $fileReference = null;

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertNull($result);
    }

    public function testHandlesFileReferenceWithMultipleFiles(): void
    {
        $fileReference = $this->createMock(FileReference::class);

        $fileReference->method('get')->willReturn('file_reference_456');
        $fileReference->method('getMultiple')->willReturn([
            'file_reference_1' => 'ref1',
            'file_reference_2' => 'ref2',
        ]);

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file_reference', $result);
        self::assertArrayHasKey('file_references', $result);
    }

    public function testHandlesMultipleFilesWithEmptyArray(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getMultiple')->willReturn([]);

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file_references', $result);
        self::assertSame([], $result['file_references']);
    }

    public function testHandlesMultipleFilesWithNull(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getMultiple')->willReturn(null);

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file_references', $result);
        self::assertIsArray($result['file_references']);
    }

    public function testReturnsEmptyArrayForNullInput(): void
    {
        $subject = new FileReferenceToArray(null);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertEmpty($result);
    }

    public function testPreservesOriginalFileReferenceKey(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('get')->willReturn('original_ref');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertArrayHasKey('file_reference', $result);
        self::assertArrayNotHasKey('id', $result);
    }
}
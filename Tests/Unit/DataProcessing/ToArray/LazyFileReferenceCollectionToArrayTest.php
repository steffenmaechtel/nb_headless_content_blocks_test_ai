<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyFileReferenceCollectionToArray;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyFileReferenceCollectionToArrayTest extends UnitTestCase
{
    public function testConvertsCollectionToLazyLoadingArray(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('get')->willReturn('file_reference_123');

        $collection = $this->createMock(\TYPO3\CMS\Core\Resource\Collection\LazyFileReferenceCollection::class);
        $collection->method('getIterator')->willReturn([
            'file_reference_123' => ['original' => $fileReference],
        ]);

        $subject = new LazyFileReferenceCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertArrayHasKey('file_reference_123', $result);
        self::assertArrayHasKey('original', $result['file_reference_123']);
    }

    public function testHandlesEmptyCollection(): void
    {
        $collection = $this->createMock(\TYPO3\CMS\Core\Resource\Collection\LazyFileReferenceCollection::class);
        $collection->method('getIterator')->willReturn([]);

        $subject = new LazyFileReferenceCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertEmpty($result);
    }

    public function testHandlesNullCollection(): void
    {
        $collection = $this->createMock(\TYPO3\CMS\Core\Resource\Collection\LazyFileReferenceCollection::class);
        $collection->method('getIterator')->willReturn([]);

        $subject = new LazyFileReferenceCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertEmpty($result);
    }

    public function testHandlesMissingKeyWithDefault(): void
    {
        $collection = $this->createMock(\TYPO3\CMS\Core\Resource\Collection\LazyFileReferenceCollection::class);
        $collection->method('getIterator')->willReturn([]);

        $subject = new LazyFileReferenceCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertEmpty($result);
    }

    public function testHandlesMultipleFileReferences(): void
    {
        $fileReference1 = $this->createMock(FileReference::class);
        $fileReference1->method('get')->willReturn('file_reference_1');

        $fileReference2 = $this->createMock(FileReference::class);
        $fileReference2->method('get')->willReturn('file_reference_2');

        $fileReference3 = $this->createMock(FileReference::class);
        $fileReference3->method('get')->willReturn('file_reference_3');

        $collection = $this->createMock(\TYPO3\CMS\Core\Resource\Collection\LazyFileReferenceCollection::class);
        $collection->method('getIterator')->willReturn([
            'file_reference_1' => ['original' => $fileReference1],
            'file_reference_2' => ['original' => $fileReference2],
            'file_reference_3' => ['original' => $fileReference3],
        ]);

        $subject = new LazyFileReferenceCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertCount(3, $result);
    }

    public function testPreservesLazyLoadingStructure(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('get')->willReturn('file_reference_456');

        $collection = $this->createMock(\TYPO3\CMS\Core\Resource\Collection\LazyFileReferenceCollection::class);
        $collection->method('getIterator')->willReturn([
            'file_reference_456' => ['original' => $fileReference],
        ]);

        $subject = new LazyFileReferenceCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertArrayHasKey('file_reference_456', $result);
        self::assertArrayHasKey('original', $result['file_reference_456']);
    }

    public function testHandlesInvalidFileReferencesInCollection(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('get')->willReturn(null);

        $collection = $this->createMock(\TYPO3\CMS\Core\Resource\Collection\LazyFileReferenceCollection::class);
        $collection->method('getIterator')->willReturn([
            'file_reference_null' => ['original' => $fileReference],
        ]);

        $subject = new LazyFileReferenceCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertArrayHasKey('file_reference_null', $result);
    }

    public function testHandlesMixedValidAndNullReferences(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('get')->willReturn('file_reference_valid');

        $fileReferenceNull = $this->createMock(FileReference::class);
        $fileReferenceNull->method('get')->willReturn(null);

        $collection = $this->createMock(\TYPO3\CMS\Core\Resource\Collection\LazyFileReferenceCollection::class);
        $collection->method('getIterator')->willReturn([
            'file_reference_valid' => ['original' => $fileReference],
            'file_reference_null' => ['original' => $fileReferenceNull],
        ]);

        $subject = new LazyFileReferenceCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertCount(2, $result);
    }
}

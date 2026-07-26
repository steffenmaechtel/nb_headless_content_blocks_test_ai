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

        $collection = [
            'file_references' => [$fileReference],
        ];

        $subject = new LazyFileReferenceCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file_references', $result);
        self::assertIsArray($result['file_references']);
    }

    public function testHandlesEmptyCollection(): void
    {
        $collection = [
            'file_references' => [],
        ];

        $subject = new LazyFileReferenceCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file_references', $result);
        self::assertIsArray($result['file_references']);
        self::assertEmpty($result['file_references']);
    }

    public function testHandlesNullCollection(): void
    {
        $subject = new LazyFileReferenceCollectionToArray([]);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file_references', $result);
        self::assertIsArray($result['file_references']);
        self::assertEmpty($result['file_references']);
    }

    public function testHandlesMissingKeyWithDefault(): void
    {
        $collection = [];

        $subject = new LazyFileReferenceCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file_references', $result);
        self::assertIsArray($result['file_references']);
        self::assertEmpty($result['file_references']);
    }

    public function testHandlesMultipleFileReferences(): void
    {
        $fileReference1 = $this->createMock(FileReference::class);
        $fileReference1->method('get')->willReturn('file_reference_1');

        $fileReference2 = $this->createMock(FileReference::class);
        $fileReference2->method('get')->willReturn('file_reference_2');

        $fileReference3 = $this->createMock(FileReference::class);
        $fileReference3->method('get')->willReturn('file_reference_3');

        $collection = [
            'file_references' => [$fileReference1, $fileReference2, $fileReference3],
        ];

        $subject = new LazyFileReferenceCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertCount(3, $result['file_references']);
    }

    public function testPreservesLazyLoadingStructure(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('get')->willReturn('file_reference_456');

        $collection = [
            'file_references' => [$fileReference],
        ];

        $subject = new LazyFileReferenceCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertArrayHasKey('file_references', $result);
        self::assertIsArray($result['file_references']);
        self::assertArrayNotHasKey('original', $result);
    }

    public function testHandlesInvalidFileReferencesInCollection(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('get')->willReturn(null);

        $collection = [
            'file_references' => [$fileReference],
        ];

        $subject = new LazyFileReferenceCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file_references', $result);
    }

    public function testHandlesMixedValidAndNullReferences(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('get')->willReturn('file_reference_valid');

        $nullReference = null;

        $collection = [
            'file_references' => [$fileReference, $nullReference],
        ];

        $subject = new LazyFileReferenceCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file_references', $result);
        self::assertIsArray($result['file_references']);
        self::assertCount(2, $result['file_references']);
    }
}
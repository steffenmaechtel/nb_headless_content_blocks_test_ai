<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Iterator;
use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyFolderCollectionToArray;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyFolderCollectionToArrayTest extends UnitTestCase
{
    public function testConvertsFolderCollectionToLazyArray(): void
    {
        $folder1 = $this->createMock(Folder::class);
        $folder1->method('getStorage')->willReturn($storageMock = $this->createMock(\TYPO3\CMS\Core\Resource\StorageRepository::class));
        $folder1->method('getIdentifier')->willReturn('/var/www/html');

        $folder2 = $this->createMock(Folder::class);
        $folder2->method('getStorage')->willReturn($storageMock);
        $folder2->method('getIdentifier')->willReturn('/home/user/public');

        $folder3 = $this->createMock(Folder::class);
        $folder3->method('getStorage')->willReturn($storageMock);
        $folder3->method('getIdentifier')->willReturn('/data/uploads');

        $storageMock->method('getConfiguration')->willReturn(['basePath' => '/var/www/html']);

        $collection = $this->createMock(\TYPO3\CMS\Core\Resource\Collection\LazyFolderCollection::class);
        $collection->method('getIterator')->willReturn($iter = $this->createMock(Iterator::class));
        $iter->method('getIterator')->willReturn([$folder1, $folder2, $folder3]);

        $subject = new LazyFolderCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
        self::assertArrayHasKey(2, $result);
    }

    public function testHandlesEmptyFolderCollection(): void
    {
        $collection = $this->createMock(\TYPO3\CMS\Core\Resource\Collection\LazyFolderCollection::class);
        $collection->method('getIterator')->willReturn([]);

        $subject = new LazyFolderCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertEmpty($result);
    }

    public function testHandlesNullFolderCollection(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Typed property');

        $collection = null;
        $subject = new LazyFolderCollectionToArray($collection);
    }

    public function testHandlesMissingFolderCollection(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Typed property');

        $collection = null;
        $subject = new LazyFolderCollectionToArray($collection);
    }

    public function testHandlesAbsolutePaths(): void
    {
        $folder = $this->createMock(Folder::class);
        $folder->method('getStorage')->willReturn($storageMock = $this->createMock(\TYPO3\CMS\Core\Resource\StorageRepository::class));
        $folder->method('getIdentifier')->willReturn('/absolute/path');

        $storageMock->method('getConfiguration')->willReturn(['basePath' => '/']);

        $collection = $this->createMock(\TYPO3\CMS\Core\Resource\Collection\LazyFolderCollection::class);
        $collection->method('getIterator')->willReturn([$folder]);

        $subject = new LazyFolderCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertStringStartsWith('/absolute', $result[0]);
    }

    public function testHandlesRelativePaths(): void
    {
        $folder = $this->createMock(Folder::class);
        $folder->method('getStorage')->willReturn($storageMock = $this->createMock(\TYPO3\CMS\Core\Resource\StorageRepository::class));
        $folder->method('getIdentifier')->willReturn('relative/path');

        $storageMock->method('getConfiguration')->willReturn(['basePath' => '/']);

        $collection = $this->createMock(\TYPO3\CMS\Core\Resource\Collection\LazyFolderCollection::class);
        $collection->method('getIterator')->willReturn([$folder]);

        $subject = new LazyFolderCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertSame('/relative/path', $result[0]);
    }

    public function testPreservesFolderStructure(): void
    {
        $folder = $this->createMock(Folder::class);
        $folder->method('getStorage')->willReturn($storageMock = $this->createMock(\TYPO3\CMS\Core\Resource\StorageRepository::class));
        $folder->method('getIdentifier')->willReturn('/path1');

        $storageMock->method('getConfiguration')->willReturn(['basePath' => '/']);

        $collection = $this->createMock(\TYPO3\CMS\Core\Resource\Collection\LazyFolderCollection::class);
        $collection->method('getIterator')->willReturn([$folder]);

        $subject = new LazyFolderCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertArrayHasKey(0, $result);
    }

    public function testHandlesInvalidPaths(): void
    {
        $folder = $this->createMock(Folder::class);
        $folder->method('getStorage')->willReturn($storageMock = $this->createMock(\TYPO3\CMS\Core\Resource\StorageRepository::class));
        $folder->method('getIdentifier')->willReturn('');

        $storageMock->method('getConfiguration')->willReturn(['basePath' => '/']);

        $collection = $this->createMock(\TYPO3\CMS\Core\Resource\Collection\LazyFolderCollection::class);
        $collection->method('getIterator')->willReturn([$folder]);

        $subject = new LazyFolderCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertIsArray($result);
    }

    public function testHandlesPathsWithTrailingSlashes(): void
    {
        $folder = $this->createMock(Folder::class);
        $folder->method('getStorage')->willReturn($storageMock = $this->createMock(\TYPO3\CMS\Core\Resource\StorageRepository::class));
        $folder->method('getIdentifier')->willReturn('/path/to/folder/');

        $storageMock->method('getConfiguration')->willReturn(['basePath' => '/']);

        $collection = $this->createMock(\TYPO3\CMS\Core\Resource\Collection\LazyFolderCollection::class);
        $collection->method('getIterator')->willReturn([$folder]);

        $subject = new LazyFolderCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertStringEndsWith('/', $result[0]);
    }

    public function testHandlesPathsWithMultipleTrailingSlashes(): void
    {
        $folder = $this->createMock(Folder::class);
        $folder->method('getStorage')->willReturn($storageMock = $this->createMock(\TYPO3\CMS\Core\Resource\StorageRepository::class));
        $folder->method('getIdentifier')->willReturn('/path/to/folder///');

        $storageMock->method('getConfiguration')->willReturn(['basePath' => '/']);

        $collection = $this->createMock(\TYPO3\CMS\Core\Resource\Collection\LazyFolderCollection::class);
        $collection->method('getIterator')->willReturn([$folder]);

        $subject = new LazyFolderCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertStringEndsWith('/', $result[0]);
    }

    public function testHandlesSpecialCharactersInPaths(): void
    {
        $folder = $this->createMock(Folder::class);
        $folder->method('getStorage')->willReturn($storageMock = $this->createMock(\TYPO3\CMS\Core\Resource\StorageRepository::class));
        $folder->method('getIdentifier')->willReturn('/path with spaces');

        $storageMock->method('getConfiguration')->willReturn(['basePath' => '/']);

        $collection = $this->createMock(\TYPO3\CMS\Core\Resource\Collection\LazyFolderCollection::class);
        $collection->method('getIterator')->willReturn([$folder]);

        $subject = new LazyFolderCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertStringContainsString('with spaces', $result[0]);
    }
}

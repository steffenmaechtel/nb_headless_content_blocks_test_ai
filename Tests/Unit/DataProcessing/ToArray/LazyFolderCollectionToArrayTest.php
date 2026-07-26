<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyFolderCollectionToArray;
use TYPO3\CMS\Core\Resource\Collection\LazyFolderCollection;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\Driver\DriverInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyFolderCollectionToArrayTest extends UnitTestCase
{
    public function testEmptyCollectionReturnsEmptyArray(): void
    {
        $collectionMock = $this->createMock(LazyFolderCollection::class);
        $collectionMock->method('getIterator')->willReturn(new \ArrayIterator([]));

        $subject = new LazyFolderCollectionToArray($collectionMock);

        $result = $subject->toArray();

        self::assertSame([], $result);
    }

    public function testConvertsSingleFolder(): void
    {
        $driverMock = $this->createMock(DriverInterface::class);

        $folderMock = $this->createMock(Folder::class);
        $folderMock->method('getDriver')->willReturn($driverMock);
        $folderMock->method('getIdentifier')->willReturn('/images/test');

        $collectionMock = $this->createMock(LazyFolderCollection::class);
        $collectionMock->method('getIterator')->willReturn(new \ArrayIterator([0 => $folderMock]));

        $subject = new LazyFolderCollectionToArray($collectionMock);

        $result = $subject->toArray();
    }

    public function testConvertsMultipleFolders(): void
    {
        $driverMock = $this->createMock(DriverInterface::class);

        $folder1Mock = $this->createMock(Folder::class);
        $folder1Mock->method('getDriver')->willReturn($driverMock);
        $folder1Mock->method('getIdentifier')->willReturn('/images');

        $folder2Mock = $this->createMock(Folder::class);
        $folder2Mock->method('getDriver')->willReturn($driverMock);
        $folder2Mock->method('getIdentifier')->willReturn('/documents');

        $collectionMock = $this->createMock(LazyFolderCollection::class);
        $collectionMock->method('getIterator')->willReturn(new \ArrayIterator([0 => $folder1Mock, 1 => $folder2Mock]));

        $subject = new LazyFolderCollectionToArray($collectionMock);

        $result = $subject->toArray();

        self::assertCount(2, $result);
    }

    public function testHandlesRootFolderIdentifier(): void
    {
        $driverMock = $this->createMock(DriverInterface::class);

        $folderMock = $this->createMock(Folder::class);
        $folderMock->method('getDriver')->willReturn($driverMock);
        $folderMock->method('getIdentifier')->willReturn('/');

        $collectionMock = $this->createMock(LazyFolderCollection::class);
        $collectionMock->method('getIterator')->willReturn(new \ArrayIterator([0 => $folderMock]));

        $subject = new LazyFolderCollectionToArray($collectionMock);

        $result = $subject->toArray();

        self::assertCount(1, $result);
    }

    public function testHandlesEmptyIdentifier(): void
    {
        $driverMock = $this->createMock(DriverInterface::class);

        $folderMock = $this->createMock(Folder::class);
        $folderMock->method('getDriver')->willReturn($driverMock);
        $folderMock->method('getIdentifier')->willReturn('');

        $collectionMock = $this->createMock(LazyFolderCollection::class);
        $collectionMock->method('getIterator')->willReturn(new \ArrayIterator([0 => $folderMock]));

        $subject = new LazyFolderCollectionToArray($collectionMock);

        $result = $subject->toArray();

        self::assertCount(1, $result);
    }

    public function testPreservesNumericKeys(): void
    {
        $driverMock = $this->createMock(DriverInterface::class);

        $folderMock = $this->createMock(Folder::class);
        $folderMock->method('getDriver')->willReturn($driverMock);
        $folderMock->method('getIdentifier')->willReturn('/test');

        $collectionMock = $this->createMock(LazyFolderCollection::class);
        $collectionMock->method('getIterator')->willReturn(new \ArrayIterator([42 => $folderMock]));

        $subject = new LazyFolderCollectionToArray($collectionMock);

        $result = $subject->toArray();

        self::assertArrayHasKey(42, $result);
    }
}

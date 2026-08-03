<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use TYPO3\CMS\Core\Resource\Collection\LazyFolderCollection;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyFolderCollectionToArrayTest extends UnitTestCase
{
    public function testLazyFolderCollectionIsConvertedToArray(): void
    {
        $storageConfig = ['basePath' => '/public/'];
        $storage1 = $this->createMock(ResourceStorage::class);
        $storage1->method('getConfiguration')->willReturn($storageConfig);

        $storage2 = $this->createMock(ResourceStorage::class);
        $storage2->method('getConfiguration')->willReturn($storageConfig);

        $folder1 = $this->createMock(Folder::class);
        $folder1->method('getStorage')->willReturn($storage1);
        $folder1->method('getIdentifier')->willReturn('folder1');

        $folder2 = $this->createMock(Folder::class);
        $folder2->method('getStorage')->willReturn($storage2);
        $folder2->method('getIdentifier')->willReturn('folder2');

        $lazyCollection = $this->createMock(LazyFolderCollection::class);
        $lazyCollection->method('getIterator')->willReturn(new \ArrayIterator([$folder1, $folder2]));

        $converter = new LazyFolderCollectionToArray($lazyCollection);
        $result = $converter->toArray();

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
        self::assertSame('/public/folder1', $result[0]);
        self::assertSame('/public/folder2', $result[1]);
    }

    public function testEmptyLazyFolderCollectionReturnsEmptyArray(): void
    {
        $lazyCollection = $this->createMock(LazyFolderCollection::class);
        $lazyCollection->method('getIterator')->willReturn(new \ArrayIterator([]));

        $converter = new LazyFolderCollectionToArray($lazyCollection);
        $result = $converter->toArray();

        self::assertEmpty($result);
    }

    public function testLazyFolderCollectionWithNullValuesIsHandled(): void
    {
        $storageConfig = ['basePath' => '/public/'];
        $storage1 = $this->createMock(ResourceStorage::class);
        $storage1->method('getConfiguration')->willReturn($storageConfig);

        $folder1 = $this->createMock(Folder::class);
        $folder1->method('getStorage')->willReturn($storage1);
        $folder1->method('getIdentifier')->willReturn('folder1');

        $lazyCollection = $this->createMock(LazyFolderCollection::class);
        $lazyCollection->method('getIterator')->willReturn(new \ArrayIterator([$folder1, null]));

        $converter = new LazyFolderCollectionToArray($lazyCollection);
        $result = $converter->toArray();

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }
}

<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyFolderCollectionToArray;
use TYPO3\CMS\Core\Resource\Collection\LazyFolderCollection;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceStorage;
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

    public function testPreservesNumericKeys(): void
    {
        $storageMock = $this->createMock(ResourceStorage::class);
        $storageMock->method('getConfiguration')->willReturn(['basePath' => 'fileadmin']);

        $folderMock = $this->createMock(Folder::class);
        $folderMock->method('getStorage')->willReturn($storageMock);
        $folderMock->method('getIdentifier')->willReturn('/test');

        $collectionMock = $this->createMock(LazyFolderCollection::class);
        $collectionMock->method('getIterator')->willReturn(new \ArrayIterator([42 => $folderMock]));

        $subject = new LazyFolderCollectionToArray($collectionMock);

        $result = $subject->toArray();

        self::assertArrayHasKey(42, $result);
        self::assertStringStartsWith('/fileadmin', $result[42]);
    }
}

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

        self::assertEmpty($result);
    }

}

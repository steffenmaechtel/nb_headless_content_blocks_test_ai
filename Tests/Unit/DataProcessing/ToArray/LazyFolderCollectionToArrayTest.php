<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyFolderCollectionToArray;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Resource\Collection\LazyFolderCollection;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceStorage;

final class LazyFolderCollectionToArrayTest extends TestCase
{
    public function testBuildsPathFromStorageBasePathAndIdentifier(): void
    {
        $folder1 = $this->createFolderMock('fileadmin/', 'images/photos');
        $folder2 = $this->createFolderMock('uploads/', 'documents');

        $collection = $this->createMock(LazyFolderCollection::class);
        $collection->method('getIterator')->willReturn(new \ArrayIterator([0 => $folder1, 1 => $folder2]));

        $subject = new LazyFolderCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertSame([
            0 => '/fileadmin/images/photos',
            1 => '/uploads/documents',
        ], $result);
    }

    public function testHandlesIdentifierWithLeadingSlash(): void
    {
        $folder = $this->createFolderMock('fileadmin/', '/images');

        $collection = $this->createMock(LazyFolderCollection::class);
        $collection->method('getIterator')->willReturn(new \ArrayIterator([0 => $folder]));

        $subject = new LazyFolderCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertSame([
            0 => '/fileadmin/images',
        ], $result);
    }

    public function testEmptyCollectionReturnsEmptyArray(): void
    {
        $collection = $this->createMock(LazyFolderCollection::class);
        $collection->method('getIterator')->willReturn(new \ArrayIterator([]));

        $subject = new LazyFolderCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertSame([], $result);
    }

    public function testHandlesEmptyIdentifier(): void
    {
        $folder = $this->createFolderMock('fileadmin/', '');

        $collection = $this->createMock(LazyFolderCollection::class);
        $collection->method('getIterator')->willReturn(new \ArrayIterator([0 => $folder]));

        $subject = new LazyFolderCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertSame([
            0 => '/fileadmin/',
        ], $result);
    }

    private function createFolderMock(string $basePath, string $identifier): Folder
    {
        $storage = $this->createMock(ResourceStorage::class);
        $storage->method('getConfiguration')->willReturn(['basePath' => $basePath]);

        $folder = $this->createMock(Folder::class);
        $folder->method('getStorage')->willReturn($storage);
        $folder->method('getIdentifier')->willReturn($identifier);

        return $folder;
    }
}

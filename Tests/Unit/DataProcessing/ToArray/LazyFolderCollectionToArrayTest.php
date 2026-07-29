<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Generator;
use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyFolderCollectionToArray;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\Storage;
use TYPO3\CMS\Core\Resource\StorageRecordInterface;
use TYPO3\CMS\Core\Resource\Collection\LazyFolderCollection;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyFolderCollectionToArrayTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testLazyFolderCollectionToArrayReturnsPaths(): void
    {
        $folder1 = $this->createMock(Folder::class);
        $folder1->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('images/1');

        $folder1->expects(self::once())
            ->method('getStorage')
            ->willReturn($this->createMock(StorageRecordInterface::class));

        $folder2 = $this->createMock(Folder::class);
        $folder2->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('images/2');

        $folder2->expects(self::once())
            ->method('getStorage')
            ->willReturn($this->createMock(StorageRecordInterface::class));

        $folder3 = $this->createMock(Folder::class);
        $folder3->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('documents');

        $folder3->expects(self::once())
            ->method('getStorage')
            ->willReturn($this->createMock(StorageRecordInterface::class));

        $lazyCollection = $this->createLazyFolderCollection([
            'folder1' => $folder1,
            'folder2' => $folder2,
            'folder3' => $folder3,
        ]);

        $subject = new LazyFolderCollectionToArray($lazyCollection);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('folder1', $result);
        self::assertArrayHasKey('folder2', $result);
        self::assertArrayHasKey('folder3', $result);
        self::assertEquals('/images/1', $result['folder1']);
        self::assertEquals('/images/2', $result['folder2']);
        self::assertEquals('/documents', $result['folder3']);
    }

    public function testLazyFolderCollectionToArrayWithOneFolder(): void
    {
        $folder = $this->createMock(Folder::class);
        $folder->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('uploads');

        $folder->expects(self::once())
            ->method('getStorage')
            ->willReturn($this->createMock(StorageRecordInterface::class));

        $lazyCollection = $this->createLazyFolderCollection([
            'uploadFolder' => $folder,
        ]);

        $subject = new LazyFolderCollectionToArray($lazyCollection);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(1, $result);
        self::assertArrayHasKey('uploadFolder', $result);
        self::assertEquals('/uploads', $result['uploadFolder']);
    }

    public function testLazyFolderCollectionToArrayWithEmptyCollection(): void
    {
        $lazyCollection = $this->createLazyFolderCollection([]);

        $subject = new LazyFolderCollectionToArray($lazyCollection);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertEmpty($result);
    }

    public function testLazyFolderCollectionToArrayEmptyGenerator(): void
    {
        $lazyCollection = $this->createLazyFolderCollection((function (): Generator {
            yield from [];
        })());

        $subject = new LazyFolderCollectionToArray($lazyCollection);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertEmpty($result);
    }

    public function testLazyFolderCollectionToArrayWithRelativeIdentifier(): void
    {
        $folder = $this->createMock(Folder::class);
        $folder->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('relative/path/');

        $folder->expects(self::once())
            ->method('getStorage')
            ->willReturn($this->createMock(StorageRecordInterface::class));

        $lazyCollection = $this->createLazyFolderCollection([
            'relativeFolder' => $folder,
        ]);

        $subject = new LazyFolderCollectionToArray($lazyCollection);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('relativeFolder', $result);
        self::assertEquals('/relative/path', $result['relativeFolder']);
    }

    public function testLazyFolderCollectionToArrayWithTrailingSlashIdentifier(): void
    {
        $folder = $this->createMock(Folder::class);
        $folder->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('path/');

        $folder->expects(self::once())
            ->method('getStorage')
            ->willReturn($this->createMock(StorageRecordInterface::class));

        $lazyCollection = $this->createLazyFolderCollection([
            'trailingSlash' => $folder,
        ]);

        $subject = new LazyFolderCollectionToArray($lazyCollection);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('trailingSlash', $result);
        self::assertEquals('/path', $result['trailingSlash']);
    }

    public function testLazyFolderCollectionToArrayWithLeadingSlashIdentifier(): void
    {
        $folder = $this->createMock(Folder::class);
        $folder->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('/absolute/path');

        $folder->expects(self::once())
            ->method('getStorage')
            ->willReturn($this->createMock(StorageRecordInterface::class));

        $lazyCollection = $this->createLazyFolderCollection([
            'leadingSlash' => $folder,
        ]);

        $subject = new LazyFolderCollectionToArray($lazyCollection);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('leadingSlash', $result);
        self::assertEquals('/absolute/path', $result['leadingSlash']);
    }

    private function createLazyFolderCollection(array $items = []): LazyFolderCollection
    {
        return new class ($items) extends LazyFolderCollection {
            public function __construct(private readonly array $items) {}

            public function getIterator(): Generator
            {
                foreach ($this->items as $key => $folder) {
                    yield $key => $folder;
                }
            }
        };
    }
}
<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyFileReferenceCollectionToArray;
use TYPO3\CMS\Core\Resource\Collection\LazyFileReferenceCollection;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyFileReferenceCollectionToArrayTest extends UnitTestCase
{
    public function testLazyFileReferenceCollectionIsConvertedToArray(): void
    {
        $fileReference1 = $this->createMock(FileReference::class);
        $fileReference1->method('getUid')->willReturn(1);
        $fileReference1->method('getAlternative')->willReturn('Alt 1');
        $fileReference1->method('getTitle')->willReturn('Title 1');

        $fileReference2 = $this->createMock(FileReference::class);
        $fileReference2->method('getUid')->willReturn(2);
        $fileReference2->method('getAlternative')->willReturn('Alt 2');
        $fileReference2->method('getTitle')->willReturn('Title 2');

        $storage = $this->createMock(ResourceStorage::class);
        $storage->method('getConfiguration')->willReturn(['basePath' => '/public/']);

        $fileReference1->method('getStorage')->willReturn($storage);
        $fileReference2->method('getStorage')->willReturn($storage);

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('getIterator')->willReturn(new \ArrayIterator([$fileReference1, $fileReference2]));

        $converter = new LazyFileReferenceCollectionToArray($lazyCollection);
        $result = $converter->toArray();

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
        self::assertArrayHasKey('id', $result[0]);
        self::assertSame(1, $result[0]['id']);
        self::assertArrayHasKey('id', $result[1]);
        self::assertSame(2, $result[1]['id']);
    }

    public function testEmptyLazyFileReferenceCollectionReturnsEmptyArray(): void
    {
        $storage = $this->createMock(ResourceStorage::class);
        $storage->method('getConfiguration')->willReturn(['basePath' => '/public/']);

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('getIterator')->willReturn(new \ArrayIterator([]));

        $converter = new LazyFileReferenceCollectionToArray($lazyCollection);
        $result = $converter->toArray();

        self::assertEmpty($result);
    }

    public function testLazyFileReferenceCollectionWithNullValuesIsHandled(): void
    {
        $fileReference1 = $this->createMock(FileReference::class);
        $fileReference1->method('getUid')->willReturn(1);
        $fileReference1->method('getAlternative')->willReturn('Alt 1');
        $fileReference1->method('getTitle')->willReturn('Title 1');

        $storage = $this->createMock(ResourceStorage::class);
        $storage->method('getConfiguration')->willReturn(['basePath' => '/public/']);
        $fileReference1->method('getStorage')->willReturn($storage);

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('getIterator')->willReturn(new \ArrayIterator([$fileReference1, null]));

        $converter = new LazyFileReferenceCollectionToArray($lazyCollection);
        $result = $converter->toArray();

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }
}

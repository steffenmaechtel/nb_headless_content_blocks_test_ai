<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyRecordCollectionSysCategoryToArray;
use TYPO3\CMS\Core\Collection\LazyRecordCollection;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyRecordCollectionSysCategoryToArrayTest extends UnitTestCase
{
    public function testLazyRecordCollectionSysCategoryIsConvertedToArray(): void
    {
        $record1 = $this->createMock(Record::class);
        $record1->method('toArray')->willReturn(['uid' => 1, 'pid' => 2, 'title' => 'Category 1', 'description' => 'Desc 1']);

        $record2 = $this->createMock(Record::class);
        $record2->method('toArray')->willReturn(['uid' => 3, 'pid' => 2, 'title' => 'Category 2', 'description' => 'Desc 2']);

        $lazyCollection = $this->createMock(LazyRecordCollection::class);
        $lazyCollection->method('getIterator')->willReturn(new \ArrayIterator([$record1, $record2]));

        $converter = new LazyRecordCollectionSysCategoryToArray($lazyCollection);
        $result = $converter->toArray();

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
        self::assertArrayHasKey('uid', $result[0]);
        self::assertSame(1, $result[0]['uid']);
        self::assertArrayHasKey('pid', $result[0]);
        self::assertSame(2, $result[0]['pid']);
        self::assertArrayHasKey('title', $result[0]);
        self::assertSame('Category 1', $result[0]['title']);
        self::assertArrayNotHasKey('description', $result[0]);
        self::assertArrayHasKey('uid', $result[1]);
        self::assertSame(3, $result[1]['uid']);
        self::assertArrayHasKey('title', $result[1]);
        self::assertSame('Category 2', $result[1]['title']);
    }

    public function testEmptyLazyRecordCollectionSysCategoryReturnsEmptyArray(): void
    {
        $lazyCollection = $this->createMock(LazyRecordCollection::class);
        $lazyCollection->method('getIterator')->willReturn(new \ArrayIterator([]));

        $converter = new LazyRecordCollectionSysCategoryToArray($lazyCollection);
        $result = $converter->toArray();

        self::assertEmpty($result);
    }

    public function testLazyRecordCollectionSysCategoryWithNullValuesIsHandled(): void
    {
        $record1 = $this->createMock(Record::class);
        $record1->method('toArray')->willReturn(['uid' => 1, 'pid' => 2, 'title' => 'Category 1']);

        $lazyCollection = $this->createMock(LazyRecordCollection::class);
        $lazyCollection->method('getIterator')->willReturn(new \ArrayIterator([$record1, null]));

        $converter = new LazyRecordCollectionSysCategoryToArray($lazyCollection);
        $result = $converter->toArray();

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }
}

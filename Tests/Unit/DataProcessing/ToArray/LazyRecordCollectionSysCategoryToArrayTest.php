<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use TYPO3\CMS\Core\Collection\LazyRecordCollection;
use TYPO3\CMS\Core\Domain\Record;
use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyRecordCollectionSysCategoryToArray;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyRecordCollectionSysCategoryToArrayTest extends UnitTestCase
{
    public function testConvertsSysCategoryCollectionToArray(): void
    {
        $category1 = $this->createMock(Record::class);
        $category1->method('toArray')->willReturn(['uid' => 123, 'pid' => 1, 'title' => 'Test Category']);

        $lazyRecordCollection = $this->createMock(LazyRecordCollection::class);
        $lazyRecordCollection->method('getIterator')->willReturn(new \ArrayIterator([$category1]));

        $subject = new LazyRecordCollectionSysCategoryToArray($lazyRecordCollection);

        $result = $subject->toArray();

        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey('uid', $result[0]);
        self::assertArrayHasKey('pid', $result[0]);
        self::assertArrayHasKey('title', $result[0]);
        self::assertSame(123, $result[0]['uid']);
        self::assertSame(1, $result[0]['pid']);
        self::assertSame('Test Category', $result[0]['title']);
    }

    public function testConvertsMultipleSysCategoriesToArray(): void
    {
        $category1 = $this->createMock(Record::class);
        $category1->method('toArray')->willReturn(['uid' => 1, 'pid' => 1, 'title' => 'Category 1']);

        $category2 = $this->createMock(Record::class);
        $category2->method('toArray')->willReturn(['uid' => 2, 'pid' => 1, 'title' => 'Category 2']);

        $lazyRecordCollection = $this->createMock(LazyRecordCollection::class);
        $lazyRecordCollection->method('getIterator')->willReturn(new \ArrayIterator([$category1, $category2]));

        $subject = new LazyRecordCollectionSysCategoryToArray($lazyRecordCollection);

        $result = $subject->toArray();

        self::assertCount(2, $result);
        self::assertSame(1, $result[0]['uid']);
        self::assertSame(2, $result[1]['uid']);
        self::assertSame('Category 1', $result[0]['title']);
        self::assertSame('Category 2', $result[1]['title']);
    }

    public function testHandlesNestedDataInSysCategory(): void
    {
        $category1 = $this->createMock(Record::class);
        $category1->method('toArray')->willReturn([
            'uid' => 1,
            'pid' => 1,
            'title' => 'Nested Category',
            'config' => ['setting' => 'value'],
        ]);

        $lazyRecordCollection = $this->createMock(LazyRecordCollection::class);
        $lazyRecordCollection->method('getIterator')->willReturn(new \ArrayIterator([$category1]));

        $subject = new LazyRecordCollectionSysCategoryToArray($lazyRecordCollection);

        $result = $subject->toArray();

        self::assertArrayHasKey('uid', $result[0]);
        self::assertArrayHasKey('pid', $result[0]);
        self::assertArrayHasKey('title', $result[0]);
    }

    public function testHandlesNullValuesInSysCategory(): void
    {
        $category1 = $this->createMock(Record::class);
        $category1->method('toArray')->willReturn([
            'uid' => null,
            'pid' => 1,
            'title' => 'Title',
        ]);

        $lazyRecordCollection = $this->createMock(LazyRecordCollection::class);
        $lazyRecordCollection->method('getIterator')->willReturn(new \ArrayIterator([$category1]));

        $subject = new LazyRecordCollectionSysCategoryToArray($lazyRecordCollection);

        $result = $subject->toArray();

        self::assertNull($result[0]['uid']);
        self::assertNotEmpty($result[0]['title']);
    }

    public function testHandlesEmptyRecordTitle(): void
    {
        $category1 = $this->createMock(Record::class);
        $category1->method('toArray')->willReturn([
            'uid' => 1,
            'pid' => 1,
            'title' => '',
        ]);

        $lazyRecordCollection = $this->createMock(LazyRecordCollection::class);
        $lazyRecordCollection->method('getIterator')->willReturn(new \ArrayIterator([$category1]));

        $subject = new LazyRecordCollectionSysCategoryToArray($lazyRecordCollection);

        $result = $subject->toArray();

        self::assertEquals('', $result[0]['title']);
    }

    public function testConvertsSysCategoryWithExtraFields(): void
    {
        $category1 = $this->createMock(Record::class);
        $category1->method('toArray')->willReturn([
            'uid' => 1,
            'pid' => 1,
            'title' => 'Test',
            'description' => 'Some text',
            'additional' => 'field',
        ]);

        $lazyRecordCollection = $this->createMock(LazyRecordCollection::class);
        $lazyRecordCollection->method('getIterator')->willReturn(new \ArrayIterator([$category1]));

        $subject = new LazyRecordCollectionSysCategoryToArray($lazyRecordCollection);

        $result = $subject->toArray();

        self::assertArrayHasKey('uid', $result[0]);
        self::assertArrayHasKey('pid', $result[0]);
        self::assertArrayHasKey('title', $result[0]);
        self::assertArrayNotHasKey('description', $result[0]);
        self::assertArrayNotHasKey('additional', $result[0]);
    }

    public function testHandlesSysCategoryWithMissingFields(): void
    {
        $category1 = $this->createMock(Record::class);
        $category1->method('toArray')->willReturn([
            'pid' => 1,
            'title' => 'Test',
        ]);

        $lazyRecordCollection = $this->createMock(LazyRecordCollection::class);
        $lazyRecordCollection->method('getIterator')->willReturn(new \ArrayIterator([$category1]));

        $subject = new LazyRecordCollectionSysCategoryToArray($lazyRecordCollection);

        $result = $subject->toArray();

        self::assertArrayHasKey('pid', $result[0]);
        self::assertArrayHasKey('title', $result[0]);
        self::assertArrayNotHasKey('uid', $result[0]);
    }

    public function testConvertsSysCategoryWithSpecialChars(): void
    {
        $category1 = $this->createMock(Record::class);
        $category1->method('toArray')->willReturn([
            'uid' => 1,
            'pid' => 1,
            'title' => 'Category with "Special" Chars',
        ]);

        $lazyRecordCollection = $this->createMock(LazyRecordCollection::class);
        $lazyRecordCollection->method('getIterator')->willReturn(new \ArrayIterator([$category1]));

        $subject = new LazyRecordCollectionSysCategoryToArray($lazyRecordCollection);

        $result = $subject->toArray();

        self::assertEquals('Category with "Special" Chars', $result[0]['title']);
    }
}

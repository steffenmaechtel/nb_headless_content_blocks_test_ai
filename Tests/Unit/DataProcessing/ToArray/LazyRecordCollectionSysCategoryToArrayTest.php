<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyRecordCollectionSysCategoryToArray;
use TYPO3\CMS\Core\Collection\LazyRecordCollection;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyRecordCollectionSysCategoryToArrayTest extends UnitTestCase
{
    public function testEmptyCollectionReturnsEmptyArray(): void
    {
        $collectionMock = $this->createMock(LazyRecordCollection::class);
        $collectionMock->method('getIterator')->willReturn(new \ArrayIterator([]));

        $subject = new LazyRecordCollectionSysCategoryToArray($collectionMock);

        $result = $subject->toArray();

        self::assertSame([], $result);
    }

    public function testConvertsSingleCategory(): void
    {
        $recordMock = $this->createMock(Record::class);
        $recordMock->method('toArray')->willReturn([
            'uid' => 1,
            'pid' => 0,
            'title' => 'Category 1',
            'description' => 'Some description',
            'sys_language_uid' => 0,
        ]);

        $collectionMock = $this->createMock(LazyRecordCollection::class);
        $collectionMock->method('getIterator')->willReturn(new \ArrayIterator([0 => $recordMock]));

        $subject = new LazyRecordCollectionSysCategoryToArray($collectionMock);

        $result = $subject->toArray();

        self::assertSame([
            0 => [
                'uid' => 1,
                'pid' => 0,
                'title' => 'Category 1',
            ],
        ], $result);
    }

    public function testConvertsMultipleCategories(): void
    {
        $record1Mock = $this->createMock(Record::class);
        $record1Mock->method('toArray')->willReturn([
            'uid' => 1,
            'pid' => 0,
            'title' => 'Category 1',
        ]);

        $record2Mock = $this->createMock(Record::class);
        $record2Mock->method('toArray')->willReturn([
            'uid' => 2,
            'pid' => 1,
            'title' => 'Category 2',
        ]);

        $record3Mock = $this->createMock(Record::class);
        $record3Mock->method('toArray')->willReturn([
            'uid' => 3,
            'pid' => 1,
            'title' => 'Category 3',
        ]);

        $collectionMock = $this->createMock(LazyRecordCollection::class);
        $collectionMock->method('getIterator')->willReturn(new \ArrayIterator([0 => $record1Mock, 1 => $record2Mock, 2 => $record3Mock]));

        $subject = new LazyRecordCollectionSysCategoryToArray($collectionMock);

        $result = $subject->toArray();

        self::assertCount(3, $result);
        self::assertSame([
            'uid' => 1,
            'pid' => 0,
            'title' => 'Category 1',
        ], $result[0]);
        self::assertSame([
            'uid' => 2,
            'pid' => 1,
            'title' => 'Category 2',
        ], $result[1]);
        self::assertSame([
            'uid' => 3,
            'pid' => 1,
            'title' => 'Category 3',
        ], $result[2]);
    }

    public function testOnlyIncludesUidPidAndTitle(): void
    {
        $recordMock = $this->createMock(Record::class);
        $recordMock->method('toArray')->willReturn([
            'uid' => 1,
            'pid' => 0,
            'title' => 'Category',
            'description' => 'Should be excluded',
            'sys_language_uid' => 0,
            'l10n_parent' => 0,
            'sorting' => 150,
        ]);

        $collectionMock = $this->createMock(LazyRecordCollection::class);
        $collectionMock->method('getIterator')->willReturn(new \ArrayIterator([0 => $recordMock]));

        $subject = new LazyRecordCollectionSysCategoryToArray($collectionMock);

        $result = $subject->toArray();

        self::assertArrayHasKey('uid', $result[0]);
        self::assertArrayHasKey('pid', $result[0]);
        self::assertArrayHasKey('title', $result[0]);
        self::assertArrayNotHasKey('description', $result[0]);
        self::assertArrayNotHasKey('sys_language_uid', $result[0]);
        self::assertArrayNotHasKey('l10n_parent', $result[0]);
        self::assertArrayNotHasKey('sorting', $result[0]);
        self::assertCount(3, $result[0]);
    }

    public function testPreservesNumericKeys(): void
    {
        $recordMock = $this->createMock(Record::class);
        $recordMock->method('toArray')->willReturn([
            'uid' => 1,
            'pid' => 0,
            'title' => 'Category',
        ]);

        $collectionMock = $this->createMock(LazyRecordCollection::class);
        $collectionMock->method('getIterator')->willReturn(new \ArrayIterator([42 => $recordMock]));

        $subject = new LazyRecordCollectionSysCategoryToArray($collectionMock);

        $result = $subject->toArray();

        self::assertArrayHasKey(42, $result);
        self::assertSame(1, $result[42]['uid']);
    }
}

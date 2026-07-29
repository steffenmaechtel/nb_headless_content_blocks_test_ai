<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyRecordCollectionSysCategoryToArray;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Collection\LazyRecordCollection;
use TYPO3\CMS\Core\Domain\Record;

final class LazyRecordCollectionSysCategoryToArrayTest extends TestCase
{
    public function testReturnsUidPidTitleForEachCategory(): void
    {
        $record1 = $this->createMock(Record::class);
        $record1->method('toArray')->willReturn([
            'uid' => 1,
            'pid' => 0,
            'title' => 'Category One',
            'description' => 'Should be excluded',
            'hidden' => 0,
        ]);

        $record2 = $this->createMock(Record::class);
        $record2->method('toArray')->willReturn([
            'uid' => 2,
            'pid' => 1,
            'title' => 'Category Two',
            'description' => 'Also excluded',
            'hidden' => 1,
        ]);

        $collection = $this->createMock(LazyRecordCollection::class);
        $collection->method('getIterator')->willReturn(new \ArrayIterator([0 => $record1, 1 => $record2]));

        $subject = new LazyRecordCollectionSysCategoryToArray($collection);

        $result = $subject->toArray();

        self::assertSame([
            0 => ['uid' => 1, 'pid' => 0, 'title' => 'Category One'],
            1 => ['uid' => 2, 'pid' => 1, 'title' => 'Category Two'],
        ], $result);
    }

    public function testEmptyCollectionReturnsEmptyArray(): void
    {
        $collection = $this->createMock(LazyRecordCollection::class);
        $collection->method('getIterator')->willReturn(new \ArrayIterator([]));

        $subject = new LazyRecordCollectionSysCategoryToArray($collection);

        $result = $subject->toArray();

        self::assertSame([], $result);
    }

    public function testSingleCategory(): void
    {
        $record = $this->createMock(Record::class);
        $record->method('toArray')->willReturn([
            'uid' => 42,
            'pid' => 10,
            'title' => 'Only Category',
            'description' => 'ignored',
        ]);

        $collection = $this->createMock(LazyRecordCollection::class);
        $collection->method('getIterator')->willReturn(new \ArrayIterator([0 => $record]));

        $subject = new LazyRecordCollectionSysCategoryToArray($collection);

        $result = $subject->toArray();

        self::assertSame([
            0 => ['uid' => 42, 'pid' => 10, 'title' => 'Only Category'],
        ], $result);
    }
}

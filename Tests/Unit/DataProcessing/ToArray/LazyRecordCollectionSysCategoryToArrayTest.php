<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyRecordCollectionSysCategoryToArray;
use TYPO3\CMS\Core\Collection\LazyRecordCollection;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyRecordCollectionSysCategoryToArrayTest extends UnitTestCase
{
    public function testMapsToUidPidTitleOnly(): void
    {
        $record = $this->createMock(Record::class);
        $record->method('toArray')->willReturn([
            'uid' => 1,
            'pid' => 2,
            'title' => 'Category',
            'description' => 'should be removed',
        ]);

        $collection = $this->createMock(LazyRecordCollection::class);
        $collection->method('getIterator')->willReturn(new \ArrayIterator([$record]));

        $subject = new LazyRecordCollectionSysCategoryToArray($collection);

        self::assertSame(
            [0 => ['uid' => 1, 'pid' => 2, 'title' => 'Category']],
            $subject->toArray()
        );
    }
}

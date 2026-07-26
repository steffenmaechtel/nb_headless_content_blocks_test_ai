<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyRecordCollectionSysCategoryToArray;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Collection\LazyRecordCollection;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyRecordCollectionSysCategoryToArrayTest extends UnitTestCase
{
    private MockObject&LazyRecordCollection $lazyRecordCollection;
    private MockObject&Record $record;

    protected function setUp(): void
    {
        $this->lazyRecordCollection = $this->createMock(LazyRecordCollection::class);
        $this->record = $this->createMock(Record::class);
    }

    public function testToArrayWithRecords(): void
    {
        $records = ['key1' => $this->record];
        $expectedResult = [
            'key1' => [
                'uid' => 123,
                'pid' => 456,
                'title' => 'Category Title'
            ]
        ];
        
        $this->lazyRecordCollection->method('getIterator')->willReturn(new \ArrayIterator($records));
        $this->record->method('toArray')->willReturn([
            'uid' => 123,
            'pid' => 456,
            'title' => 'Category Title'
        ]);
        
        $subject = new LazyRecordCollectionSysCategoryToArray($this->lazyRecordCollection);
        
        $result = $subject->toArray();
        
        $this->assertEquals($expectedResult, $result);
    }

    public function testToArrayWithEmptyCollection(): void
    {
        $records = [];
        
        $this->lazyRecordCollection->method('getIterator')->willReturn(new \ArrayIterator($records));
        
        $subject = new LazyRecordCollectionSysCategoryToArray($this->lazyRecordCollection);
        
        $result = $subject->toArray();
        
        $this->assertEquals([], $result);
    }
}
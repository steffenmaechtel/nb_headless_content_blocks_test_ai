<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyRecordCollectionToArray;
use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\RecordToArray;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinition;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\Core\Collection\LazyRecordCollection;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyRecordCollectionToArrayTest extends UnitTestCase
{
    private MockObject&LazyRecordCollection $lazyRecordCollection;
    private MockObject&TableDefinition $tableDefinition;
    private MockObject&TableDefinitionCollection $tableDefinitionCollection;
    private MockObject&EventDispatcher $eventDispatcher;
    private MockObject&Record $record;
    private MockObject&RecordToArray $recordToArray;

    protected function setUp(): void
    {
        $this->lazyRecordCollection = $this->createMock(LazyRecordCollection::class);
        $this->tableDefinition = $this->createMock(TableDefinition::class);
        $this->tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $this->eventDispatcher = $this->createMock(EventDispatcher::class);
        $this->record = $this->createMock(Record::class);
        $this->recordToArray = $this->createMock(RecordToArray::class);
    }

    public function testToArrayWithDefinedTableDefinition(): void
    {
        $records = ['key1' => $this->record];
        $expectedResult = ['key1' => ['processed' => 'data']];
        
        $this->lazyRecordCollection->method('getIterator')->willReturn(new \ArrayIterator($records));
        $this->tableDefinitionCollection->method('hasTable')->willReturn(false);
        $this->recordToArray->method('toArray')->willReturn($expectedResult['key1']);
        
        GeneralUtility::addInstance(RecordToArray::class, $this->recordToArray);
        
        $subject = new LazyRecordCollectionToArray(
            $this->lazyRecordCollection,
            $this->tableDefinition,
            $this->tableDefinitionCollection,
            $this->eventDispatcher
        );
        
        $result = $subject->toArray();
        
        $this->assertEquals($expectedResult, $result);
    }

    public function testToArrayWithNullTableDefinitionAndValidTable(): void
    {
        $records = ['key1' => $this->record];
        $expectedResult = ['key1' => ['processed' => 'data']];
        
        $this->lazyRecordCollection->method('getIterator')->willReturn(new \ArrayIterator($records));
        $this->record->method('getRawRecord')->willReturn($this->record);
        $this->record->method('getMainType')->willReturn('tt_content');
        $this->tableDefinitionCollection->method('hasTable')->with('tt_content')->willReturn(true);
        $this->tableDefinitionCollection->method('getTable')->with('tt_content')->willReturn($this->tableDefinition);
        $this->recordToArray->method('toArray')->willReturn($expectedResult['key1']);
        
        GeneralUtility::addInstance(RecordToArray::class, $this->recordToArray);
        
        $subject = new LazyRecordCollectionToArray(
            $this->lazyRecordCollection,
            null,
            $this->tableDefinitionCollection,
            $this->eventDispatcher
        );
        
        $result = $subject->toArray();
        
        $this->assertEquals($expectedResult, $result);
    }

    public function testToArrayWithNullTableDefinitionAndUnknownTable(): void
    {
        $records = ['key1' => $this->record];
        
        $this->lazyRecordCollection->method('getIterator')->willReturn(new \ArrayIterator($records));
        $this->record->method('getRawRecord')->willReturn($this->record);
        $this->record->method('getMainType')->willReturn('unknown_table');
        $this->tableDefinitionCollection->method('hasTable')->with('unknown_table')->willReturn(false);
        
        $subject = new LazyRecordCollectionToArray(
            $this->lazyRecordCollection,
            null,
            $this->tableDefinitionCollection,
            $this->eventDispatcher
        );
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unknown case in LazyRecordCollectionToArray->toArray() switch for key "key1"');
        
        $subject->toArray();
    }

    public function testToArrayWithNullTableDefinitionAndSysCategory(): void
    {
        $records = ['key1' => $this->record];
        $expectedResult = ['key1' => ['processed' => 'data']];
        
        $this->lazyRecordCollection->method('getIterator')->willReturn(new \ArrayIterator($records));
        $this->record->method('getRawRecord')->willReturn($this->record);
        $this->record->method('getMainType')->willReturn('sys_category');
        $this->tableDefinitionCollection->method('hasTable')->with('sys_category')->willReturn(false);
        $this->recordToArray->method('toArray')->willReturn($expectedResult['key1']);
        
        GeneralUtility::addInstance(RecordToArray::class, $this->recordToArray);
        
        $subject = new LazyRecordCollectionToArray(
            $this->lazyRecordCollection,
            null,
            $this->tableDefinitionCollection,
            $this->eventDispatcher
        );
        
        $result = $subject->toArray();
        
        $this->assertEquals($expectedResult, $result);
    }
}
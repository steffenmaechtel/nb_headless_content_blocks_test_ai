<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\RecordToArray;
use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\ArrayRecursiveToArray;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinition;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class RecordToArrayTest extends UnitTestCase
{
    private MockObject&Record $record;
    private MockObject&TableDefinition $tableDefinition;
    private TableDefinitionCollection $tableDefinitionCollection;
    private MockObject&EventDispatcher $eventDispatcher;
    private MockObject&ArrayRecursiveToArray $arrayRecursiveToArray;

    protected function setUp(): void
    {
        $this->record = $this->createMock(Record::class);
        $this->tableDefinition = $this->createMock(TableDefinition::class);
        $this->tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $this->eventDispatcher = $this->createMock(EventDispatcher::class);
        $this->arrayRecursiveToArray = $this->createMock(ArrayRecursiveToArray::class);
    }

    public function testToArrayReturnsErrorMessageWhenFileDoesNotExistExceptionIsThrown(): void
    {
        $exceptionMessage = 'File does not exist';
        $exception = new FileDoesNotExistException($exceptionMessage);
        
        $this->record->method('toArray')->willThrowException($exception);
        
        $subject = new RecordToArray(
            $this->record,
            $this->tableDefinition,
            $this->tableDefinitionCollection,
            $this->eventDispatcher
        );
        
        $result = $subject->toArray();
        
        $this->assertArrayHasKey('__errorMessage', $result);
        $this->assertEquals($exceptionMessage, $result['__errorMessage']);
    }

    public function testToArrayRemovesSystemFields(): void
    {
        $recordArray = [
            'uid' => 123,
            'pid' => 456,
            'colPos' => 0,
            'CType' => 'text',
            'foreign_table_parent_uid' => 789,
            'tx_container_parent' => 987,
            'other_field' => 'value'
        ];
        
        $expectedResult = [
            'other_field' => 'value'
        ];
        
        $this->record->method('toArray')->willReturn($recordArray);
        $this->arrayRecursiveToArray->method('toArray')->willReturn($expectedResult);
        
        GeneralUtility::addInstance(ArrayRecursiveToArray::class, $this->arrayRecursiveToArray);
        
        $subject = new RecordToArray(
            $this->record,
            $this->tableDefinition,
            $this->tableDefinitionCollection,
            $this->eventDispatcher
        );
        
        $result = $subject->toArray();
        
        $this->assertEquals($expectedResult, $result);
    }

    public function testToArrayCallsArrayRecursiveToArray(): void
    {
        $recordArray = ['field' => 'value'];
        $expectedConvertedArray = ['field' => 'converted_value'];
        
        $this->record->method('toArray')->willReturn($recordArray);
        $this->arrayRecursiveToArray->method('toArray')->willReturn($expectedConvertedArray);
        
        GeneralUtility::addInstance(ArrayRecursiveToArray::class, $this->arrayRecursiveToArray);
        
        $subject = new RecordToArray(
            $this->record,
            $this->tableDefinition,
            $this->tableDefinitionCollection,
            $this->eventDispatcher
        );
        
        $result = $subject->toArray();
        
        $this->assertEquals($expectedConvertedArray, $result);
    }
}
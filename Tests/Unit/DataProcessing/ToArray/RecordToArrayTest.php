<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\ArrayRecursiveToArray;
use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\RecordToArray;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinition;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class RecordToArrayTest extends UnitTestCase
{
    public function testToArrayRemovesInternalKeys(): void
    {
        $record = $this->createMock(Record::class);
        $record->method('toArray')->willReturn([
            'uid' => 1,
            'pid' => 10,
            'colPos' => 1,
            'CType' => 'text',
            'foreign_table_parent_uid' => 2,
            'tx_container_parent' => 3,
            'title' => 'Hello World',
            'bodytext' => 'Some content',
        ]);

        $tableDefinitionCollection = new TableDefinitionCollection(new \TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry());
        $eventDispatcher = $this->createMock(EventDispatcher::class);

        $subject = new RecordToArray(
            $record,
            null,
            $tableDefinitionCollection,
            $eventDispatcher
        );

        $result = $subject->toArray();

        self::assertArrayNotHasKey('uid', $result);
        self::assertArrayNotHasKey('pid', $result);
        self::assertArrayNotHasKey('colPos', $result);
        self::assertArrayNotHasKey('CType', $result);
        self::assertArrayNotHasKey('foreign_table_parent_uid', $result);
        self::assertArrayNotHasKey('tx_container_parent', $result);
        self::assertSame('Hello World', $result['title']);
        self::assertSame('Some content', $result['bodytext']);
    }

    public function testToArrayReturnsErrorMessageOnFileDoesNotExistException(): void
    {
        $record = $this->createMock(Record::class);
        $record->method('toArray')->willThrowException(new FileDoesNotExistException('File not found'));

        $tableDefinitionCollection = new TableDefinitionCollection(new \TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry());
        $eventDispatcher = $this->createMock(EventDispatcher::class);

        $subject = new RecordToArray(
            $record,
            null,
            $tableDefinitionCollection,
            $eventDispatcher
        );

        $result = $subject->toArray();

        self::assertSame(['__errorMessage' => 'File not found'], $result);
    }
}

<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\RecordToArray;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinition;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversNothing;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(RecordToArray::class)]
final class RecordToArrayTest extends UnitTestCase
{
    public function testToArrayRemovesInternalFields(): void
    {
        $record = $this->createMock(Record::class);
        $record->method('toArray')->willReturn([
            'uid' => 1,
            'pid' => 0,
            'colPos' => 0,
            'CType' => 'text',
            'header' => 'My Header',
            'bodytext' => 'My body text',
            'foreign_table_parent_uid' => 5,
            'tx_container_parent' => 3,
        ]);

        $tableDefinitionCollection = new TableDefinitionCollection(
            new \TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry()
        );
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
        self::assertSame('My Header', $result['header']);
        self::assertSame('My body text', $result['bodytext']);
    }

    public function testToArrayHandlesFileDoesNotExistException(): void
    {
        $record = $this->createMock(Record::class);
        $record->method('toArray')->willThrowException(
            new FileDoesNotExistException('File not found', 1234567890)
        );

        $tableDefinitionCollection = new TableDefinitionCollection(
            new \TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry()
        );
        $eventDispatcher = $this->createMock(EventDispatcher::class);

        $subject = new RecordToArray(
            $record,
            null,
            $tableDefinitionCollection,
            $eventDispatcher
        );

        $result = $subject->toArray();

        self::assertArrayHasKey('__errorMessage', $result);
        self::assertSame('File not found', $result['__errorMessage']);
    }

    public function testToArrayPassesEventToEventDispatcher(): void
    {
        $record = $this->createMock(Record::class);
        $record->method('toArray')->willReturn([
            'uid' => 1,
            'pid' => 0,
            'colPos' => 0,
            'CType' => 'text',
            'header' => 'Test',
        ]);

        $tableDefinitionCollection = new TableDefinitionCollection(
            new \TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry()
        );
        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher->expects(self::once())->method('dispatch');

        $subject = new RecordToArray(
            $record,
            null,
            $tableDefinitionCollection,
            $eventDispatcher
        );

        $subject->toArray();
    }
}
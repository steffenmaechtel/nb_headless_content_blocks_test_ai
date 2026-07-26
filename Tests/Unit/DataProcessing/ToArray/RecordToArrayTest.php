<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\RecordToArray;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinition;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use Psr\EventDispatcher\ListenerProviderInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class RecordToArrayTest extends UnitTestCase
{
    public function testConvertsRecordArrayToJsonCompatible(): void
    {
        $recordData = [
            'uid' => 123,
            'title' => 'Test Title',
            'description' => 'Test Description',
            'created' => '2026-01-01 00:00:00',
        ];

        $tableDefinition = $this->createMock(TableDefinition::class);
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $eventDispatcher = new EventDispatcher($this->createMock(ListenerProviderInterface::class));

        $record = $this->getMockBuilder(Record::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['toArray'])
            ->getMock();

        $record->method('toArray')->willReturn($recordData);

        $subject = new RecordToArray($record, $tableDefinition, $tableDefinitionCollection, $eventDispatcher);
        $result = $subject->toArray();

        self::assertArrayHasKey('uid', $result);
        self::assertEquals(123, $result['uid']);
        self::assertArrayHasKey('title', $result);
        self::assertEquals('Test Title', $result['title']);
        self::assertArrayHasKey('description', $result);
        self::assertArrayHasKey('created', $result);
    }

    public function testRemovesSystemFields(): void
    {
        $recordData = [
            'uid' => 123,
            'pid' => 456,
            'colPos' => 0,
            'CType' => 'text',
            'foreign_table_parent_uid' => 789,
            'tx_container_parent' => 101,
            'title' => 'Test',
        ];

        $tableDefinition = $this->createMock(TableDefinition::class);
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $eventDispatcher = new EventDispatcher($this->createMock(ListenerProviderInterface::class));

        $record = $this->getMockBuilder(Record::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['toArray'])
            ->getMock();

        $record->method('toArray')->willReturn($recordData);

        $subject = new RecordToArray($record, $tableDefinition, $tableDefinitionCollection, $eventDispatcher);
        $result = $subject->toArray();

        self::assertArrayNotHasKey('uid', $result);
        self::assertArrayNotHasKey('pid', $result);
        self::assertArrayNotHasKey('colPos', $result);
        self::assertArrayNotHasKey('CType', $result);
        self::assertArrayNotHasKey('foreign_table_parent_uid', $result);
        self::assertArrayNotHasKey('tx_container_parent', $result);
        self::assertArrayHasKey('title', $result);
    }

    public function testHandlesNullValues(): void
    {
        $recordData = [
            'uid' => 123,
            'title' => null,
            'description' => 'Test',
            'created' => '2026-01-01 00:00:00',
        ];

        $tableDefinition = $this->createMock(TableDefinition::class);
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $eventDispatcher = new EventDispatcher($this->createMock(ListenerProviderInterface::class));

        $record = $this->getMockBuilder(Record::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['toArray'])
            ->getMock();

        $record->method('toArray')->willReturn($recordData);

        $subject = new RecordToArray($record, $tableDefinition, $tableDefinitionCollection, $eventDispatcher);
        $result = $subject->toArray();

        self::assertNull($result['title']);
        self::assertArrayHasKey('uid', $result);
    }

    public function testHandlesNestedArrays(): void
    {
        $recordData = [
            'uid' => 123,
            'title' => 'Test',
            'config' => [
                'setting1' => 'value1',
                'setting2' => 'value2',
            ],
        ];

        $tableDefinition = $this->createMock(TableDefinition::class);
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $eventDispatcher = new EventDispatcher($this->createMock(ListenerProviderInterface::class));

        $record = $this->getMockBuilder(Record::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['toArray'])
            ->getMock();

        $record->method('toArray')->willReturn($recordData);

        $subject = new RecordToArray($record, $tableDefinition, $tableDefinitionCollection, $eventDispatcher);
        $result = $subject->toArray();

        self::assertArrayHasKey('config', $result);
        self::assertArrayHasKey('setting1', $result['config']);
        self::assertEquals('value1', $result['config']['setting1']);
    }

    public function testHandlesDateTimeValues(): void
    {
        $recordData = [
            'uid' => 123,
            'title' => 'Test',
            'created' => '2026-01-01 00:00:00',
            'modified' => '2026-01-02 00:00:00',
        ];

        $tableDefinition = $this->createMock(TableDefinition::class);
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $eventDispatcher = new EventDispatcher($this->createMock(ListenerProviderInterface::class));

        $record = $this->getMockBuilder(Record::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['toArray'])
            ->getMock();

        $record->method('toArray')->willReturn($recordData);

        $subject = new RecordToArray($record, $tableDefinition, $tableDefinitionCollection, $eventDispatcher);
        $result = $subject->toArray();

        self::assertIsString($result['created']);
        self::assertEquals('2026-01-01 00:00:00', $result['created']);
    }

    public function testHandlesEmptyRecord(): void
    {
        $recordData = [
            'uid' => 123,
        ];

        $tableDefinition = $this->createMock(TableDefinition::class);
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $eventDispatcher = new EventDispatcher($this->createMock(ListenerProviderInterface::class));

        $record = $this->getMockBuilder(Record::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['toArray'])
            ->getMock();

        $record->method('toArray')->willReturn($recordData);

        $subject = new RecordToArray($record, $tableDefinition, $tableDefinitionCollection, $eventDispatcher);
        $result = $subject->toArray();

        self::assertEquals(['uid' => 123], $result);
    }

    public function testHandlesMixedScalarTypes(): void
    {
        $recordData = [
            'uid' => 123,
            'title' => 'Test',
            'isActive' => true,
            'score' => 42.5,
            'tags' => [],
        ];

        $tableDefinition = $this->createMock(TableDefinition::class);
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $eventDispatcher = new EventDispatcher($this->createMock(ListenerProviderInterface::class));

        $record = $this->getMockBuilder(Record::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['toArray'])
            ->getMock();

        $record->method('toArray')->willReturn($recordData);

        $subject = new RecordToArray($record, $tableDefinition, $tableDefinitionCollection, $eventDispatcher);
        $result = $subject->toArray();

        self::assertTrue($result['isActive']);
        self::assertEquals(42.5, $result['score']);
        self::assertIsArray($result['tags']);
    }
}

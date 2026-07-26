<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyRecordCollectionToArray;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinition;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\Core\Collection\LazyRecordCollection;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\EventDispatcher\ListenerProviderInterface;
use TYPO3\CMS\Core\Resource\Record;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyRecordCollectionToArrayTest extends UnitTestCase
{
    public function testConvertsLazyRecordCollectionToArray(): void
    {
        $lazyRecordCollection = $this->createMock(LazyRecordCollection::class);

        $record1 = $this->createMock(Record::class);
        $record1->method('getMainType')->willReturn('tt_content');

        $record2 = $this->createMock(Record::class);
        $record2->method('getMainType')->willReturn('tt_content');

        $lazyRecordCollection->method('__iterative')->willReturn(true);
        $lazyRecordCollection->method('current')->willReturnOnConsecutiveCalls($record1, $record2);
        $lazyRecordCollection->method('key')->willReturnOnConsecutiveCalls(0, 1);

        $tableDefinition = $this->createMock(TableDefinition::class);
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $eventDispatcher = new EventDispatcher($this->createMock(ListenerProviderInterface::class));

        $subject = new LazyRecordCollectionToArray($lazyRecordCollection, $tableDefinition, $tableDefinitionCollection, $eventDispatcher);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    public function testConvertsMultipleLazyRecordCollectionsToArray(): void
    {
        $lazyRecordCollection = $this->createMock(LazyRecordCollection::class);

        $record1 = $this->createMock(Record::class);
        $record1->method('getMainType')->willReturn('tt_content');

        $record2 = $this->createMock(Record::class);
        $record2->method('getMainType')->willReturn('tt_content');

        $record3 = $this->createMock(Record::class);
        $record3->method('getMainType')->willReturn('tt_content');

        $lazyRecordCollection->method('__iterative')->willReturn(true);
        $lazyRecordCollection->method('current')->willReturnOnConsecutiveCalls($record1, $record2, $record3);
        $lazyRecordCollection->method('key')->willReturnOnConsecutiveCalls(0, 1, 2);

        $tableDefinition = $this->createMock(TableDefinition::class);
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $eventDispatcher = new EventDispatcher($this->createMock(ListenerProviderInterface::class));

        $subject = new LazyRecordCollectionToArray($lazyRecordCollection, $tableDefinition, $tableDefinitionCollection, $eventDispatcher);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
        self::assertArrayHasKey(2, $result);
    }

    public function testHandlesEmptyLazyRecordCollection(): void
    {
        $lazyRecordCollection = $this->createMock(LazyRecordCollection::class);

        $tableDefinition = $this->createMock(TableDefinition::class);
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $eventDispatcher = new EventDispatcher($this->createMock(ListenerProviderInterface::class));

        $subject = new LazyRecordCollectionToArray($lazyRecordCollection, $tableDefinition, $tableDefinitionCollection, $eventDispatcher);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertEmpty($result);
    }

    public function testHandlesNullLazyRecordCollection(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Typed property');

        $tableDefinition = $this->createMock(TableDefinition::class);
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $eventDispatcher = new EventDispatcher($this->createMock(ListenerProviderInterface::class));

        $subject = new LazyRecordCollectionToArray(null, $tableDefinition, $tableDefinitionCollection, $eventDispatcher);

        $result = $subject->toArray();
    }

    public function testHandlesMissingKeyLazyRecordCollection(): void
    {
        $lazyRecordCollection = $this->createMock(LazyRecordCollection::class);

        $tableDefinition = $this->createMock(TableDefinition::class);
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $eventDispatcher = new EventDispatcher($this->createMock(ListenerProviderInterface::class));

        $subject = new LazyRecordCollectionToArray($lazyRecordCollection, $tableDefinition, $tableDefinitionCollection, $eventDispatcher);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertEmpty($result);
    }

    public function testConvertsNestedRecordTypesToArray(): void
    {
        $lazyRecordCollection = $this->createMock(LazyRecordCollection::class);

        $record1 = $this->createMock(Record::class);
        $record1->method('getMainType')->willReturn('tt_content');

        $record2 = $this->createMock(Record::class);
        $record2->method('getMainType')->willReturn('tt_content');

        $lazyRecordCollection->method('__iterative')->willReturn(true);
        $lazyRecordCollection->method('current')->willReturnOnConsecutiveCalls($record1, $record2);
        $lazyRecordCollection->method('key')->willReturnOnConsecutiveCalls(0, 1);

        $tableDefinition = $this->createMock(TableDefinition::class);
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $eventDispatcher = new EventDispatcher($this->createMock(ListenerProviderInterface::class));

        $subject = new LazyRecordCollectionToArray($lazyRecordCollection, $tableDefinition, $tableDefinitionCollection, $eventDispatcher);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    public function testConvertsWithTableDefinition(): void
    {
        $lazyRecordCollection = $this->createMock(LazyRecordCollection::class);

        $record1 = $this->createMock(Record::class);
        $record1->method('getMainType')->willReturn('tt_content');

        $record2 = $this->createMock(Record::class);
        $record2->method('getMainType')->willReturn('tt_content');

        $lazyRecordCollection->method('__iterative')->willReturn(true);
        $lazyRecordCollection->method('current')->willReturnOnConsecutiveCalls($record1, $record2);
        $lazyRecordCollection->method('key')->willReturnOnConsecutiveCalls(0, 1);

        $tableDefinition = $this->createMock(TableDefinition::class);
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $eventDispatcher = new EventDispatcher($this->createMock(ListenerProviderInterface::class));

        $subject = new LazyRecordCollectionToArray($lazyRecordCollection, $tableDefinition, $tableDefinitionCollection, $eventDispatcher);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    public function testConvertsWithoutTableDefinition(): void
    {
        $lazyRecordCollection = $this->createMock(LazyRecordCollection::class);

        $record1 = $this->createMock(Record::class);
        $record1->method('getMainType')->willReturn('tt_content');

        $lazyRecordCollection->method('__iterative')->willReturn(true);
        $lazyRecordCollection->method('current')->willReturn($record1);
        $lazyRecordCollection->method('key')->willReturn(0);

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $eventDispatcher = new EventDispatcher($this->createMock(ListenerProviderInterface::class));

        $subject = new LazyRecordCollectionToArray($lazyRecordCollection, null, $tableDefinitionCollection, $eventDispatcher);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey(0, $result);
    }
}
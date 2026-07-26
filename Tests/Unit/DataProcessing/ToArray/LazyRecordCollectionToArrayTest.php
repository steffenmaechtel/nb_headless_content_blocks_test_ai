<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Iterator;
use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyRecordCollectionToArray;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinition;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\Core\Collection\LazyRecordCollection;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\EventDispatcher\ListenerProviderInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyRecordCollectionToArrayTest extends UnitTestCase
{
    public function testConvertsLazyRecordCollectionToArray(): void
    {
        $lazyRecordCollection = $this->createMock(LazyRecordCollection::class);
        $lazyRecordCollection->method('getIterator')->willReturn([
            0 => ['uid' => 1, 'title' => 'Record 1'],
            1 => ['uid' => 2, 'title' => 'Record 2'],
        ]);

        $tableDefinition = $this->createMock(TableDefinition::class);
        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $eventDispatcher = new EventDispatcher($this->createMock(ListenerProviderInterface::class));

        $subject = new LazyRecordCollectionToArray(
            $lazyRecordCollection,
            $tableDefinition,
            $tableDefinitionCollection,
            $eventDispatcher
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    public function testConvertsMultipleLazyRecordCollectionsToArray(): void
    {
        $lazyRecordCollection = $this->createMock(LazyRecordCollection::class);
        $lazyRecordCollection->method('getIterator')->willReturn([
            0 => ['uid' => 1, 'title' => 'Record 1'],
            1 => ['uid' => 2, 'title' => 'Record 2'],
            2 => ['uid' => 3, 'title' => 'Record 3'],
        ]);

        $tableDefinition = $this->createMock(TableDefinition::class);
        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $eventDispatcher = new EventDispatcher($this->createMock(ListenerProviderInterface::class));

        $subject = new LazyRecordCollectionToArray(
            $lazyRecordCollection,
            $tableDefinition,
            $tableDefinitionCollection,
            $eventDispatcher
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
        self::assertArrayHasKey(2, $result);
    }

    public function testHandlesEmptyLazyRecordCollection(): void
    {
        $lazyRecordCollection = $this->createMock(LazyRecordCollection::class);
        $lazyRecordCollection->method('getIterator')->willReturn([]);

        $tableDefinition = $this->createMock(TableDefinition::class);
        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $eventDispatcher = new EventDispatcher($this->createMock(ListenerProviderInterface::class));

        $subject = new LazyRecordCollectionToArray(
            $lazyRecordCollection,
            $tableDefinition,
            $tableDefinitionCollection,
            $eventDispatcher
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertEmpty($result);
    }

    public function testHandlesNullLazyRecordCollection(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Typed property');

        $tableDefinition = $this->createMock(TableDefinition::class);
        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $eventDispatcher = new EventDispatcher($this->createMock(ListenerProviderInterface::class));

        $subject = new LazyRecordCollectionToArray(
            null,
            $tableDefinition,
            $tableDefinitionCollection,
            $eventDispatcher
        );
    }

    public function testHandlesMissingKeyLazyRecordCollection(): void
    {
        $lazyRecordCollection = $this->createMock(LazyRecordCollection::class);
        $lazyRecordCollection->method('getIterator')->willReturn([]);

        $tableDefinition = $this->createMock(TableDefinition::class);
        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $eventDispatcher = new EventDispatcher($this->createMock(ListenerProviderInterface::class));

        $subject = new LazyRecordCollectionToArray(
            $lazyRecordCollection,
            $tableDefinition,
            $tableDefinitionCollection,
            $eventDispatcher
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertEmpty($result);
    }

    public function testConvertsWithTableDefinition(): void
    {
        $lazyRecordCollection = $this->createMock(LazyRecordCollection::class);
        $lazyRecordCollection->method('getIterator')->willReturn([
            0 => ['uid' => 1, 'title' => 'Record 1'],
            1 => ['uid' => 2, 'title' => 'Record 2'],
        ]);

        $tableDefinition = $this->createMock(TableDefinition::class);
        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $eventDispatcher = new EventDispatcher($this->createMock(ListenerProviderInterface::class));

        $subject = new LazyRecordCollectionToArray(
            $lazyRecordCollection,
            $tableDefinition,
            $tableDefinitionCollection,
            $eventDispatcher
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    public function testConvertsWithoutTableDefinition(): void
    {
        $lazyRecordCollection = $this->createMock(LazyRecordCollection::class);
        $lazyRecordCollection->method('getIterator')->willReturn([
            0 => ['uid' => 1, 'title' => 'Record 1'],
        ]);

        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $eventDispatcher = new EventDispatcher($this->createMock(ListenerProviderInterface::class));

        $subject = new LazyRecordCollectionToArray(
            $lazyRecordCollection,
            null,
            $tableDefinitionCollection,
            $eventDispatcher
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey(0, $result);
    }
}

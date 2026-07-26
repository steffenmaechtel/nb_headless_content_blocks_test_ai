<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Iterator;
use AutomaticLanguageKeysRegistry;
use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyRecordCollectionToArray;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\Core\Collection\LazyRecordCollection;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use Psr\EventDispatcher\ListenerProviderInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyRecordCollectionToArrayTest extends UnitTestCase
{
    public function testConvertsLazyRecordCollectionToArray(): void
    {
        $lazyRecordCollection = $this->createMock(LazyRecordCollection::class);
        $lazyRecordCollection->method('getIterator')->willReturn(new \ArrayIterator([
            ['uid' => 1, 'title' => 'Record 1'],
            ['uid' => 2, 'title' => 'Record 2'],
        ]));

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $eventDispatcher = new EventDispatcher($this->createMock(ListenerProviderInterface::class));

        $subject = new LazyRecordCollectionToArray(
            $lazyRecordCollection,
            null,
            $tableDefinitionCollection,
            $eventDispatcher
        );

        $result = $subject->toArray();

        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    public function testConvertsMultipleLazyRecordCollectionsToArray(): void
    {
        $lazyRecordCollection = $this->createMock(LazyRecordCollection::class);
        $lazyRecordCollection->method('getIterator')->willReturn(new \ArrayIterator([
            ['uid' => 1, 'title' => 'Record 1'],
            ['uid' => 2, 'title' => 'Record 2'],
            ['uid' => 3, 'title' => 'Record 3'],
        ]));

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $eventDispatcher = new EventDispatcher($this->createMock(ListenerProviderInterface::class));

        $subject = new LazyRecordCollectionToArray(
            $lazyRecordCollection,
            null,
            $tableDefinitionCollection,
            $eventDispatcher
        );

        $result = $subject->toArray();

        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
        self::assertArrayHasKey(2, $result);
    }

    public function testHandlesEmptyLazyRecordCollection(): void
    {
        $lazyRecordCollection = $this->createMock(LazyRecordCollection::class);
        $lazyRecordCollection->method('getIterator')->willReturn(new \ArrayIterator([]));

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $eventDispatcher = new EventDispatcher($this->createMock(ListenerProviderInterface::class));

        $subject = new LazyRecordCollectionToArray(
            $lazyRecordCollection,
            null,
            $tableDefinitionCollection,
            $eventDispatcher
        );

        $result = $subject->toArray();

        self::assertEmpty($result);
    }
}

<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use TYPO3\CMS\ContentBlocks\Definition\TableDefinition;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\Core\Collection\LazyRecordCollection;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\RawRecord;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyRecordCollectionToArrayTest extends UnitTestCase
{
    public function testLazyRecordCollectionIsConvertedToArray(): void
    {
        $rawRecord1 = $this->createMock(RawRecord::class);
        $rawRecord1->method('getMainType')->willReturn('tx_nb_headless_content_blocks_contentblocks');

        $record1 = $this->createMock(Record::class);
        $record1->method('getRawRecord')->willReturn($rawRecord1);
        $record1->method('toArray')->willReturn(['title' => 'Record 1', 'bodytext' => 'Body 1']);

        $rawRecord2 = $this->createMock(RawRecord::class);
        $rawRecord2->method('getMainType')->willReturn('tx_nb_headless_content_blocks_contentblocks');

        $record2 = $this->createMock(Record::class);
        $record2->method('getRawRecord')->willReturn($rawRecord2);
        $record2->method('toArray')->willReturn(['title' => 'Record 2', 'bodytext' => 'Body 2']);

        $lazyCollection = $this->createMock(LazyRecordCollection::class);
        $lazyCollection->method('getIterator')->willReturn(new \ArrayIterator([$record1, $record2]));

        $tableDefinition = $this->createMock(TableDefinition::class);
        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $eventDispatcher = $this->createMock(EventDispatcher::class);

        $converter = new LazyRecordCollectionToArray($lazyCollection, $tableDefinition, $tableDefinitionCollection, $eventDispatcher);
        $result = $converter->toArray();

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
        self::assertArrayHasKey('title', $result[0]);
        self::assertSame('Record 1', $result[0]['title']);
        self::assertArrayHasKey('title', $result[1]);
        self::assertSame('Record 2', $result[1]['title']);
    }

    public function testEmptyLazyRecordCollectionReturnsEmptyArray(): void
    {
        $lazyCollection = $this->createMock(LazyRecordCollection::class);
        $lazyCollection->method('getIterator')->willReturn(new \ArrayIterator([]));

        $tableDefinition = $this->createMock(TableDefinition::class);
        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $eventDispatcher = $this->createMock(EventDispatcher::class);

        $converter = new LazyRecordCollectionToArray($lazyCollection, $tableDefinition, $tableDefinitionCollection, $eventDispatcher);
        $result = $converter->toArray();

        self::assertEmpty($result);
    }

    public function testLazyRecordCollectionWithNullValuesIsHandled(): void
    {
        $rawRecord1 = $this->createMock(RawRecord::class);
        $rawRecord1->method('getMainType')->willReturn('tx_nb_headless_content_blocks_contentblocks');

        $record1 = $this->createMock(Record::class);
        $record1->method('getRawRecord')->willReturn($rawRecord1);
        $record1->method('toArray')->willReturn(['title' => 'Record 1']);

        $lazyCollection = $this->createMock(LazyRecordCollection::class);
        $lazyCollection->method('getIterator')->willReturn(new \ArrayIterator([$record1, null]));

        $tableDefinition = $this->createMock(TableDefinition::class);
        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $eventDispatcher = $this->createMock(EventDispatcher::class);

        $converter = new LazyRecordCollectionToArray($lazyCollection, $tableDefinition, $tableDefinitionCollection, $eventDispatcher);
        $result = $converter->toArray();

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }
}

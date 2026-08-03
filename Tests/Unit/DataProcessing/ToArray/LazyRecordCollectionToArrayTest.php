<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyRecordCollectionToArray;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\Core\Collection\LazyRecordCollection as CoreLazyRecordCollection;
use TYPO3\CMS\Core\Domain\RawRecord;
use TYPO3\CMS\Core\Domain\Record;
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

        $tableDefinition = null; // Will be auto-resolved based on table name in the collection
        $tableDefCollection = new TableDefinitionCollection(new \TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry());

        $eventDispatcherMock = $this->createMock(\TYPO3\CMS\Core\EventDispatcher\EventDispatcher::class);

        $lazyCollection = $this->createMock(CoreLazyRecordCollection::class);
        $lazyCollection->method('getIterator')->willReturn(new \ArrayIterator([$record1, $record2]));

        $converter = new LazyRecordCollectionToArray(
            $lazyCollection,
            $tableDefinition,
            $tableDefCollection,
            $eventDispatcherMock
        );
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
        $tableDefinition = null; // Will be auto-resolved based on table name in the collection
        $tableDefCollection = new TableDefinitionCollection(new \TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry());

        $eventDispatcherMock = $this->createMock(\TYPO3\CMS\Core\EventDispatcher\EventDispatcher::class);

        $lazyCollection = $this->createMock(CoreLazyRecordCollection::class);
        $lazyCollection->method('getIterator')->willReturn(new \ArrayIterator([]));

        $converter = new LazyRecordCollectionToArray(
            $lazyCollection,
            $tableDefinition,
            $tableDefCollection,
            $eventDispatcherMock
        );
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

        $tableDefinition = null; // Will be auto-resolved based on table name in the collection
        $tableDefCollection = new TableDefinitionCollection(new \TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry());

        $eventDispatcherMock = $this->createMock(\TYPO3\CMS\Core\EventDispatcher\EventDispatcher::class);

        $lazyCollection = $this->createMock(CoreLazyRecordCollection::class);
        $lazyCollection->method('getIterator')->willReturn(new \ArrayIterator([$record1, null]));

        $converter = new LazyRecordCollectionToArray(
            $lazyCollection,
            $tableDefinition,
            $tableDefCollection,
            $eventDispatcherMock
        );
        $result = $converter->toArray();

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }
}

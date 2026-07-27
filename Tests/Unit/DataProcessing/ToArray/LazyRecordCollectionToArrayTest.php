<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyRecordCollectionToArray;
use Psr\EventDispatcher\ListenerProviderInterface;
use TYPO3\CMS\ContentBlocks\Definition\Capability\TableDefinitionCapability;
use TYPO3\CMS\ContentBlocks\Definition\ContentType\ContentType;
use TYPO3\CMS\ContentBlocks\Definition\ContentType\ContentTypeDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Definition\PaletteDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Definition\SqlColumnDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinition;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry;
use TYPO3\CMS\Core\Collection\LazyRecordCollection;
use TYPO3\CMS\Core\Domain\RawRecord;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyRecordCollectionToArrayTest extends UnitTestCase
{
    public function testSysCategoryResolvesWithoutTableDefinition(): void
    {
        $record = $this->createRecordMock(['uid' => 5, 'title' => 'category'], 'sys_category');
        $collection = $this->createCollectionMock([$record]);

        $subject = new LazyRecordCollectionToArray(
            $collection,
            null,
            new TableDefinitionCollection(new AutomaticLanguageKeysRegistry()),
            $this->createEventDispatcher()
        );

        self::assertSame([0 => ['title' => 'category']], $subject->toArray());
    }

    public function testKnownTableResolvesTableDefinition(): void
    {
        $record = $this->createRecordMock(['uid' => 7, 'title' => 'item'], 'tx_known');
        $collection = $this->createCollectionMock([$record]);

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $tableDefinitionCollection->addTable($this->buildTableDefinition('tx_known'));

        $subject = new LazyRecordCollectionToArray(
            $collection,
            null,
            $tableDefinitionCollection,
            $this->createEventDispatcher()
        );

        self::assertSame([0 => ['title' => 'item']], $subject->toArray());
    }

    public function testUnknownTableThrowsException(): void
    {
        $record = $this->createRecordMock(['uid' => 9, 'title' => 'item'], 'tx_unknown');
        $collection = $this->createCollectionMock([$record]);

        $subject = new LazyRecordCollectionToArray(
            $collection,
            null,
            new TableDefinitionCollection(new AutomaticLanguageKeysRegistry()),
            $this->createEventDispatcher()
        );

        $this->expectException(\Exception::class);
        $subject->toArray();
    }

    /**
     * @param array<string, mixed> $rawData
     */
    private function createRecordMock(array $rawData, string $mainType): Record
    {
        $rawRecord = $this->createMock(RawRecord::class);
        $rawRecord->method('getMainType')->willReturn($mainType);

        $record = $this->createMock(Record::class);
        $record->method('toArray')->willReturn($rawData);
        $record->method('getRawRecord')->willReturn($rawRecord);

        return $record;
    }

    /**
     * @param array<int, Record> $records
     */
    private function createCollectionMock(array $records): LazyRecordCollection
    {
        $collection = $this->createMock(LazyRecordCollection::class);
        $collection->method('getIterator')->willReturn(new \ArrayIterator($records));

        return $collection;
    }

    private function buildTableDefinition(string $table): TableDefinition
    {
        return new TableDefinition(
            $table,
            TableDefinitionCapability::createFromArray([]),
            null,
            ContentType::RECORD_TYPE,
            new ContentTypeDefinitionCollection(),
            new SqlColumnDefinitionCollection(),
            new \TYPO3\CMS\ContentBlocks\Definition\TcaFieldDefinitionCollection(),
            new PaletteDefinitionCollection(),
            []
        );
    }

    private function createEventDispatcher(): EventDispatcher
    {
        $listenerProvider = new class implements ListenerProviderInterface {
            public function getListenersForEvent(object $event): iterable
            {
                return [];
            }
        };

        return new EventDispatcher($listenerProvider);
    }
}

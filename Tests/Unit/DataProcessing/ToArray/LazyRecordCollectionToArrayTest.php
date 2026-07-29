<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyRecordCollectionToArray;
use Psr\EventDispatcher\ListenerProviderInterface;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry;
use TYPO3\CMS\Core\Collection\LazyRecordCollection;
use TYPO3\CMS\Core\Domain\RawRecord;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\Record\ComputedProperties;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyRecordCollectionToArrayTest extends UnitTestCase
{
    public function testThrowsExceptionForUnknownTable(): void
    {
        $rawRecord = new RawRecord(
            uid: 1,
            pid: 0,
            properties: [],
            computedProperties: new ComputedProperties(),
            type: 'tx_unknown_table'
        );

        $record = $this->createMock(Record::class);
        $record->method('getRawRecord')->willReturn($rawRecord);

        $collection = $this->createMock(LazyRecordCollection::class);
        $collection->method('getIterator')->willReturn(new \ArrayIterator([0 => $record]));

        $tableDefinitionCollection = new TableDefinitionCollection(
            new AutomaticLanguageKeysRegistry()
        );

        $subject = new LazyRecordCollectionToArray(
            $collection,
            null,
            $tableDefinitionCollection,
            $this->createEventDispatcher()
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unknown case in LazyRecordCollectionToArray->toArray() switch for key "0"');
        $this->expectExceptionCode(1746095968);

        $subject->toArray();
    }

    /**
     * Note: The sys_category and normal table resolution paths cannot be tested
     * cleanly in unit tests because they delegate to RecordToArray via
     * GeneralUtility::makeInstance(). These paths are covered by functional tests.
     */
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

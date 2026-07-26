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
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyRecordCollectionToArrayTest extends UnitTestCase
{
    public function testEmptyCollectionReturnsEmptyArray(): void
    {
        $collectionMock = $this->createMock(LazyRecordCollection::class);
        $collectionMock->method('getIterator')->willReturn(new \ArrayIterator([]));

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());

        $subject = new LazyRecordCollectionToArray(
            $collectionMock,
            null,
            $tableDefinitionCollection,
            $this->createEventDispatcher([])
        );

        $result = $subject->toArray();

        self::assertSame([], $result);
    }

    public function testPreservesNumericKeys(): void
    {
        $rawRecordMock = $this->createMock(RawRecord::class);
        $rawRecordMock->method('getMainType')->willReturn('sys_category');

        $recordMock = $this->createMock(Record::class);
        $recordMock->method('getRawRecord')->willReturn($rawRecordMock);
        $recordMock->method('toArray')->willReturn([
            'uid' => 1,
            'pid' => 0,
            'title' => 'Test',
        ]);

        $collectionMock = $this->createMock(LazyRecordCollection::class);
        $collectionMock->method('getIterator')->willReturn(new \ArrayIterator([42 => $recordMock]));

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());

        $subject = new LazyRecordCollectionToArray(
            $collectionMock,
            null,
            $tableDefinitionCollection,
            $this->createEventDispatcher([])
        );

        $result = $subject->toArray();

        self::assertArrayHasKey(42, $result);
    }

    private function createEventDispatcher(array $listeners): EventDispatcher
    {
        $listenerProvider = new class ($listeners) implements ListenerProviderInterface {
            public function __construct(private readonly array $listeners) {}

            public function getListenersForEvent(object $event): iterable
            {
                return $this->listeners;
            }
        };

        return new EventDispatcher($listenerProvider);
    }
}

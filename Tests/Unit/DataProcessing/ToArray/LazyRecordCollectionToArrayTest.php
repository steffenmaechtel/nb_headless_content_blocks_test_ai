<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Generator;
use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyRecordCollectionToArray;
use Netzbewegung\NbHeadlessContentBlocks\Event\ModifyArrayRecursiveToArrayEvent;
use Psr\EventDispatcher\ListenerProviderInterface;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinition;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Definition\TcaFieldDefinition;
use TYPO3\CMS\ContentBlocks\FieldType\SelectFieldType;
use TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyRecordCollectionToArrayTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testLazyRecordCollectionToArrayWithRecords(): void
    {
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());

        $recordFactory = $this->createMock(\TYPO3\CMS\Core\Domain\RecordFactory::class);
        GeneralUtility::setSingletonInstance(\TYPO3\CMS\Core\Domain\RecordFactory::class, $recordFactory);

        $record1 = $this->createMock(\TYPO3\CMS\Core\Domain\Record::class);
        $record1->expects(self::once())
            ->method('toArray')
            ->willReturn(['uid' => 1, 'title' => 'Record 1']);

        $record2 = $this->createMock(\TYPO3\CMS\Core\Domain\Record::class);
        $record2->expects(self::once())
            ->method('toArray')
            ->willReturn(['uid' => 2, 'title' => 'Record 2']);

        $recordFactory->expects(self::exactly(2))
            ->method('createResolvedRecordFromDatabaseRow')
            ->willReturnMap([
                ['tx_my_table', ['uid' => 1], $record1],
                ['tx_my_table', ['uid' => 2], $record2],
            ]);

        $lazyCollection = $this->createLazyRecordCollection([
            'record1' => $record1,
            'record2' => $record2,
        ]);

        $subject = new LazyRecordCollectionToArray(
            $lazyCollection,
            null,
            $tableDefinitionCollection,
            new EventDispatcher($this->createListenerProvider())
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('record1', $result);
        self::assertArrayHasKey('record2', $result);
    }

    public function testLazyRecordCollectionToArrayWithTableDefinition(): void
    {
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());

        $tableDefinition = new TableDefinition(
            'tx_my_table',
            [],
            [],
            new TcaFieldDefinitionCollection(new AutomaticLanguageKeysRegistry())
        );

        $recordFactory = $this->createMock(\TYPO3\CMS\Core\Domain\RecordFactory::class);
        GeneralUtility::setSingletonInstance(\TYPO3\CMS\Core\Domain\RecordFactory::class, $recordFactory);

        $record = $this->createMock(\TYPO3\CMS\Core\Domain\Record::class);
        $record->expects(self::once())
            ->method('toArray')
            ->willReturn(['title' => 'Test Record']);

        $recordFactory->expects(self::once())
            ->method('createResolvedRecordFromDatabaseRow')
            ->willReturn($record);

        $lazyCollection = $this->createLazyRecordCollection([
            'record' => $record,
        ]);

        $subject = new LazyRecordCollectionToArray(
            $lazyCollection,
            $tableDefinition,
            $tableDefinitionCollection,
            new EventDispatcher($this->createListenerProvider())
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('record', $result);
        self::assertArrayHasKey('title', $result['record']);
    }

    public function testLazyRecordCollectionToArrayWithEmptyCollection(): void
    {
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());

        $lazyCollection = $this->createLazyRecordCollection([]);

        $subject = new LazyRecordCollectionToArray(
            $lazyCollection,
            null,
            $tableDefinitionCollection,
            new EventDispatcher($this->createListenerProvider())
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertEmpty($result);
    }

    public function testLazyRecordCollectionToArrayWithSysCategory(): void
    {
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());

        $recordFactory = $this->createMock(\TYPO3\CMS\Core\Domain\RecordFactory::class);
        GeneralUtility::setSingletonInstance(\TYPO3\CMS\Core\Domain\RecordFactory::class, $recordFactory);

        $record = $this->createMock(\TYPO3\CMS\Core\Domain\Record::class);
        $record->expects(self::once())
            ->method('toArray')
            ->willReturn(['uid' => 123, 'pid' => 0, 'title' => 'Category 1']);

        $recordFactory->expects(self::once())
            ->method('createResolvedRecordFromDatabaseRow')
            ->willReturn($record);

        $lazyCollection = $this->createLazyRecordCollection([
            'category1' => $record,
        ]);

        $subject = new LazyRecordCollectionToArray(
            $lazyCollection,
            null,
            $tableDefinitionCollection,
            new EventDispatcher($this->createListenerProvider())
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('category1', $result);
        self::assertArrayHasKey('uid', $result['category1']);
        self::assertArrayHasKey('pid', $result['category1']);
        self::assertArrayHasKey('title', $result['category1']);
    }

    public function testLazyRecordCollectionToArrayWithEventDispatcher(): void
    {
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());

        $recordFactory = $this->createMock(\TYPO3\CMS\Core\Domain\RecordFactory::class);
        GeneralUtility::setSingletonInstance(\TYPO3\CMS\Core\Domain\RecordFactory::class, $recordFactory);

        $receivedEvents = [];

        $record = $this->createMock(\TYPO3\CMS\Core\Domain\Record::class);
        $record->expects(self::once())
            ->method('toArray')
            ->willReturn(['test_key' => 'test_value']);

        $recordFactory->expects(self::once())
            ->method('createResolvedRecordFromDatabaseRow')
            ->willReturn($record);

        $lazyCollection = $this->createLazyRecordCollection([
            'record' => $record,
        ]);

        $listener = static function (ModifyArrayRecursiveToArrayEvent $event) use (&$receivedEvents): void {
            $receivedEvents[] = [
                'key' => $event->getKey(),
                'value' => $event->getValue(),
            ];
        };

        $subject = new LazyRecordCollectionToArray(
            $lazyCollection,
            null,
            $tableDefinitionCollection,
            new EventDispatcher($this->createListenerProvider([$listener]))
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('record', $result);
        self::assertCount(1, $receivedEvents);
        self::assertEquals('test_key', $receivedEvents[0]['key']);
    }

    public function testLazyRecordCollectionToArrayWithUnknownTable(): void
    {
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());

        $recordFactory = $this->createMock(\TYPO3\CMS\Core\Domain\RecordFactory::class);
        GeneralUtility::setSingletonInstance(\TYPO3\CMS\Core\Domain\RecordFactory::class, $recordFactory);

        $record = $this->createMock(\TYPO3\CMS\Core\Domain\Record::class);
        $record->expects(self::once())
            ->method('toArray')
            ->willReturn(['title' => 'Test']);

        $recordFactory->expects(self::once())
            ->method('createResolvedRecordFromDatabaseRow')
            ->willReturn($record);

        $lazyCollection = $this->createLazyRecordCollection([
            'record' => $record,
        ]);

        $subject = new LazyRecordCollectionToArray(
            $lazyCollection,
            null,
            $tableDefinitionCollection,
            new EventDispatcher($this->createListenerProvider())
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('record', $result);
    }

    private function createLazyRecordCollection(array $items = []): \TYPO3\CMS\Core\Collection\LazyRecordCollection
    {
        return new class ($items) extends \TYPO3\CMS\Core\Collection\LazyRecordCollection {
            public function __construct(private readonly array $items) {}

            public function getIterator(): Generator
            {
                foreach ($this->items as $key => $record) {
                    yield $key => $record;
                }
            }
        };
    }

    private function createListenerProvider(array $listeners = []): ListenerProviderInterface
    {
        return new class ($listeners) implements ListenerProviderInterface {
            public function __construct(private readonly array $listeners) {}

            public function getListenersForEvent(object $event): iterable
            {
                return $this->listeners;
            }
        };
    }
}
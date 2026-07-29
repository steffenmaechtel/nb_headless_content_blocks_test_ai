<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Exception;
use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\RecordToArray;
use Netzbewegung\NbHeadlessContentBlocks\Event\ModifyArrayRecursiveToArrayEvent;
use Psr\EventDispatcher\ListenerProviderInterface;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinition;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Definition\TcaFieldDefinition;
use TYPO3\CMS\ContentBlocks\FieldType\SelectFieldType;
use TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class RecordToArrayTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testRecordToArrayReturnsProcessedData(): void
    {
        $recordFactory = $this->createMock(\TYPO3\CMS\Core\Domain\RecordFactory::class);
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());

        $recordMock = $this->createMock(\TYPO3\CMS\Core\Domain\Record::class);
        $recordMock->expects(self::once())
            ->method('toArray')
            ->willReturn([
                'uid' => 1,
                'pid' => 2,
                'title' => 'Test Title',
                'description' => 'Test Description',
            ]);

        $recordFactory->expects(self::once())
            ->method('createResolvedRecordFromDatabaseRow')
            ->willReturn($recordMock);

        GeneralUtility::setSingletonInstance(\TYPO3\CMS\Core\Domain\RecordFactory::class, $recordFactory);

        $subject = new RecordToArray(
            $recordMock,
            null,
            $tableDefinitionCollection,
            new EventDispatcher($this->createListenerProvider())
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayNotHasKey('uid', $result);
        self::assertArrayNotHasKey('pid', $result);
        self::assertArrayHasKey('title', $result);
        self::assertEquals('Test Title', $result['title']);
    }

    public function testRecordToArrayThrowsFileDoesNotExistException(): void
    {
        $recordFactory = $this->createMock(\TYPO3\CMS\Core\Domain\RecordFactory::class);
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());

        $recordMock = $this->createMock(\TYPO3\CMS\Core\Domain\Record::class);
        $recordMock->expects(self::once())
            ->method('toArray')
            ->willThrowException(new FileDoesNotExistException('File not found'));

        $recordFactory->expects(self::once())
            ->method('createResolvedRecordFromDatabaseRow')
            ->willReturn($recordMock);

        GeneralUtility::setSingletonInstance(\TYPO3\CMS\Core\Domain\RecordFactory::class, $recordFactory);

        $subject = new RecordToArray(
            $recordMock,
            null,
            $tableDefinitionCollection,
            new EventDispatcher($this->createListenerProvider())
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('__errorMessage', $result);
        self::assertEquals('File not found', $result['__errorMessage']);
    }

    public function testRecordToArrayRemovesSpecificFields(): void
    {
        $recordFactory = $this->createMock(\TYPO3\CMS\Core\Domain\RecordFactory::class);
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());

        $recordMock = $this->createMock(\TYPO3\CMS\Core\Domain\Record::class);
        $recordMock->expects(self::once())
            ->method('toArray')
            ->willReturn([
                'uid' => 123,
                'pid' => 456,
                'colPos' => 0,
                'CType' => 'content_block',
                'foreign_table_parent_uid' => 789,
                'tx_container_parent' => 101,
                'title' => 'Test',
            ]);

        $recordFactory->expects(self::once())
            ->method('createResolvedRecordFromDatabaseRow')
            ->willReturn($recordMock);

        GeneralUtility::setSingletonInstance(\TYPO3\CMS\Core\Domain\RecordFactory::class, $recordFactory);

        $subject = new RecordToArray(
            $recordMock,
            null,
            $tableDefinitionCollection,
            new EventDispatcher($this->createListenerProvider())
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayNotHasKey('uid', $result);
        self::assertArrayNotHasKey('pid', $result);
        self::assertArrayNotHasKey('colPos', $result);
        self::assertArrayNotHasKey('CType', $result);
        self::assertArrayNotHasKey('foreign_table_parent_uid', $result);
        self::assertArrayNotHasKey('tx_container_parent', $result);
        self::assertArrayHasKey('title', $result);
    }

    public function testRecordToArrayWithTcaFieldDefinition(): void
    {
        $recordFactory = $this->createMock(\TYPO3\CMS\Core\Domain\RecordFactory::class);
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());

        $recordMock = $this->createMock(\TYPO3\CMS\Core\Domain\Record::class);
        $recordMock->expects(self::once())
            ->method('toArray')
            ->willReturn(['title' => 'Test']);

        $recordFactory->expects(self::once())
            ->method('createResolvedRecordFromDatabaseRow')
            ->willReturn($recordMock);

        GeneralUtility::setSingletonInstance(\TYPO3\CMS\Core\Domain\RecordFactory::class, $recordFactory);

        $tcaFieldDefinition = new TcaFieldDefinition('test_field', 'Test Field', SelectFieldType::class);

        $subject = new RecordToArray(
            $recordMock,
            $tcaFieldDefinition,
            $tableDefinitionCollection,
            new EventDispatcher($this->createListenerProvider())
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('title', $result);
    }

    public function testRecordToArrayWithEventDispatcher(): void
    {
        $recordFactory = $this->createMock(\TYPO3\CMS\Core\Domain\RecordFactory::class);
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());

        $receivedEvents = [];

        $recordMock = $this->createMock(\TYPO3\CMS\Core\Domain\Record::class);
        $recordMock->expects(self::once())
            ->method('toArray')
            ->willReturn(['test_key' => 'test_value']);

        $recordFactory->expects(self::once())
            ->method('createResolvedRecordFromDatabaseRow')
            ->willReturn($recordMock);

        GeneralUtility::setSingletonInstance(\TYPO3\CMS\Core\Domain\RecordFactory::class, $recordFactory);

        $listener = static function (ModifyArrayRecursiveToArrayEvent $event) use (&$receivedEvents): void {
            $receivedEvents[] = [
                'key' => $event->getKey(),
                'value' => $event->getValue(),
            ];
        };

        $subject = new RecordToArray(
            $recordMock,
            null,
            $tableDefinitionCollection,
            new EventDispatcher($this->createListenerProvider([$listener]))
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(1, $receivedEvents);
        self::assertEquals('test_key', $receivedEvents[0]['key']);
    }

    public function testRecordToArrayKeepsNonSystemFields(): void
    {
        $recordFactory = $this->createMock(\TYPO3\CMS\Core\Domain\RecordFactory::class);
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());

        $recordMock = $this->createMock(\TYPO3\CMS\Core\Domain\Record::class);
        $recordMock->expects(self::once())
            ->method('toArray')
            ->willReturn([
                'uid' => 1,
                'pid' => 2,
                'sys_language_uid' => 0,
                'l10n_diffsource' => 'test',
                'tstamp' => 1234567890,
                'crdate' => 1234567890,
                'title' => 'Test',
                'bodytext' => 'Test body',
                'author' => 'Test Author',
            ]);

        $recordFactory->expects(self::once())
            ->method('createResolvedRecordFromDatabaseRow')
            ->willReturn($recordMock);

        GeneralUtility::setSingletonInstance(\TYPO3\CMS\Core\Domain\RecordFactory::class, $recordFactory);

        $subject = new RecordToArray(
            $recordMock,
            null,
            $tableDefinitionCollection,
            new EventDispatcher($this->createListenerProvider())
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayNotHasKey('uid', $result);
        self::assertArrayNotHasKey('pid', $result);
        self::assertArrayNotHasKey('sys_language_uid', $result);
        self::assertArrayNotHasKey('l10n_diffsource', $result);
        self::assertArrayNotHasKey('tstamp', $result);
        self::assertArrayNotHasKey('crdate', $result);
        self::assertArrayHasKey('title', $result);
        self::assertArrayHasKey('bodytext', $result);
        self::assertArrayHasKey('author', $result);
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
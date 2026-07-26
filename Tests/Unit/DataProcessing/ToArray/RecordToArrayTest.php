<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\ArrayRecursiveToArray;
use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\RecordToArray;
use Netzbewegung\NbHeadlessContentBlocks\Event\ModifyArrayRecursiveToArrayEvent;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\EventDispatcher\ListenerProviderInterface;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class RecordToArrayTest extends UnitTestCase
{
    public function testRemovesInternalFields(): void
    {
        $recordMock = $this->createMock(Record::class);
        $recordMock->method('toArray')->willReturn([
            'uid' => 1,
            'pid' => 2,
            'colPos' => 3,
            'CType' => 'text',
            'foreign_table_parent_uid' => 4,
            'tx_container_parent' => 5,
            'title' => 'Test',
            'content' => 'Body',
        ]);

        $subject = $this->createSubject($recordMock);

        $result = $subject->toArray();

        self::assertArrayNotHasKey('uid', $result);
        self::assertArrayNotHasKey('pid', $result);
        self::assertArrayNotHasKey('colPos', $result);
        self::assertArrayNotHasKey('CType', $result);
        self::assertArrayNotHasKey('foreign_table_parent_uid', $result);
        self::assertArrayNotHasKey('tx_container_parent', $result);
        self::assertArrayHasKey('title', $result);
        self::assertArrayHasKey('content', $result);
    }

    public function testReturnsErrorMessageOnFileDoesNotExistException(): void
    {
        $recordMock = $this->createMock(Record::class);
        $recordMock->method('toArray')->willThrowException(
            new FileDoesNotExistException('File not found', 1234567890)
        );

        $subject = $this->createSubject($recordMock);

        $result = $subject->toArray();

        self::assertArrayHasKey('__errorMessage', $result);
        self::assertSame('File not found', $result['__errorMessage']);
    }

    public function testPreservesCustomFields(): void
    {
        $recordMock = $this->createMock(Record::class);
        $recordMock->method('toArray')->willReturn([
            'uid' => 1,
            'pid' => 2,
            'custom_field_1' => 'value1',
            'custom_field_2' => 'value2',
            'number_field' => 42,
        ]);

        $subject = $this->createSubject($recordMock);

        $result = $subject->toArray();

        self::assertSame('value1', $result['custom_field_1']);
        self::assertSame('value2', $result['custom_field_2']);
        self::assertSame(42, $result['number_field']);
    }

    public function testResultIsSortedByKey(): void
    {
        $recordMock = $this->createMock(Record::class);
        $recordMock->method('toArray')->willReturn([
            'uid' => 1,
            'pid' => 2,
            'zulu' => 'z',
            'alpha' => 'a',
            'mike' => 'm',
        ]);

        $subject = $this->createSubject($recordMock);

        $result = $subject->toArray();

        $keys = array_keys($result);
        self::assertSame(['alpha', 'mike', 'zulu'], $keys);
    }

    public function testEmptyRecordReturnsEmptyArray(): void
    {
        $recordMock = $this->createMock(Record::class);
        $recordMock->method('toArray')->willReturn([
            'uid' => 1,
            'pid' => 2,
        ]);

        $subject = $this->createSubject($recordMock);

        $result = $subject->toArray();

        self::assertSame([], $result);
    }

    public function testHandledEventProcessedValueIsUsed(): void
    {
        $listener = static function (ModifyArrayRecursiveToArrayEvent $event): void {
            if ($event->getKey() === 'title') {
                $event->setProcessedValue('Processed Title');
            }
        };

        $recordMock = $this->createMock(Record::class);
        $recordMock->method('toArray')->willReturn([
            'uid' => 1,
            'pid' => 2,
            'title' => 'Original Title',
        ]);

        $subject = $this->createSubject($recordMock, [$listener]);

        $result = $subject->toArray();

        self::assertSame('Processed Title', $result['title']);
    }

    private function createSubject(Record $record, array $listeners = []): RecordToArray
    {
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());

        return new RecordToArray(
            $record,
            null,
            $tableDefinitionCollection,
            $this->createEventDispatcher($listeners)
        );
    }

    /**
     * @param callable[] $listeners
     */
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

<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\RecordToArray;
use Psr\EventDispatcher\ListenerProviderInterface;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class RecordToArrayTest extends UnitTestCase
{
    public function testStripsReservedKeys(): void
    {
        $record = $this->createMock(Record::class);
        $record->method('toArray')->willReturn([
            'uid' => 1,
            'pid' => 2,
            'colPos' => 0,
            'CType' => 'text',
            'foreign_table_parent_uid' => 3,
            'tx_container_parent' => 4,
            'bodytext' => 'hello',
        ]);

        $subject = new RecordToArray(
            $record,
            null,
            new TableDefinitionCollection(new AutomaticLanguageKeysRegistry()),
            $this->createEventDispatcher()
        );

        self::assertSame(['bodytext' => 'hello'], $subject->toArray());
    }

    public function testFileDoesNotExistExceptionReturnsErrorMessage(): void
    {
        $record = $this->createMock(Record::class);
        $record->method('toArray')->willThrowException(new FileDoesNotExistException('file is gone', 123));

        $subject = new RecordToArray(
            $record,
            null,
            new TableDefinitionCollection(new AutomaticLanguageKeysRegistry()),
            $this->createEventDispatcher()
        );

        self::assertSame(['__errorMessage' => 'file is gone'], $subject->toArray());
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

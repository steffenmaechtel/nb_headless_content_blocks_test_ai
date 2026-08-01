<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\RecordToArray;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\ListenerProviderInterface;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;

final class RecordToArrayTest extends TestCase
{
    public function testToArrayStripsInternalKeys(): void
    {
        $record = $this->createConfiguredMock(Record::class, [
            'toArray' => [
                'uid' => 42,
                'pid' => 7,
                'colPos' => 0,
                'CType' => 'test_simple',
                'foreign_table_parent_uid' => 1,
                'tx_container_parent' => 0,
                'header' => 'My header',
                'bodytext' => 'My body',
            ],
        ]);

        $subject = new RecordToArray(
            $record,
            null,
            new TableDefinitionCollection(new AutomaticLanguageKeysRegistry()),
            $this->createNoopEventDispatcher()
        );

        $result = $subject->toArray();

        self::assertSame(['bodytext' => 'My body', 'header' => 'My header'], $result);
    }

    public function testFileDoesNotExistExceptionReturnsErrorMessageArray(): void
    {
        $record = self::createStub(Record::class);
        $record->method('toArray')->willThrowException(
            new FileDoesNotExistException('missing-file.jpg', 1701234567)
        );

        $subject = new RecordToArray(
            $record,
            null,
            new TableDefinitionCollection(new AutomaticLanguageKeysRegistry()),
            $this->createNoopEventDispatcher()
        );

        $result = $subject->toArray();

        self::assertSame(['__errorMessage' => 'missing-file.jpg'], $result);
    }

    private function createNoopEventDispatcher(): EventDispatcher
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

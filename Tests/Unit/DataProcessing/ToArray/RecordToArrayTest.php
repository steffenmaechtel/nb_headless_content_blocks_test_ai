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
    public function testFileDoesNotExistExceptionReturnsErrorMessage(): void
    {
        $record = $this->createMock(Record::class);
        $record->method('toArray')->willThrowException(
            new FileDoesNotExistException('File not found')
        );

        $subject = new RecordToArray(
            $record,
            null,
            new TableDefinitionCollection(new AutomaticLanguageKeysRegistry()),
            $this->createEventDispatcher()
        );

        $result = $subject->toArray();

        self::assertSame(['__errorMessage' => 'File not found'], $result);
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

<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\RecordToArray;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;

final class RecordToArrayTest extends TestCase
{
    public function testSystemFieldsAreRemoved(): void
    {
        $record = $this->createMock(Record::class);
        $record->expects($this->once())
            ->method('toArray')
            ->willReturn([
                'uid' => 1,
                'pid' => 2,
                'colPos' => 3,
                'CType' => 'text',
                'foreign_table_parent_uid' => 4,
                'tx_container_parent' => 5,
                'title' => 'Keep me',
            ]);

        $subject = new RecordToArray(
            $record,
            null,
            new TableDefinitionCollection(new AutomaticLanguageKeysRegistry()),
            self::createStub(EventDispatcher::class)
        );

        self::assertSame(['title' => 'Keep me'], $subject->toArray());
    }

    public function testMissingFileReturnsErrorArray(): void
    {
        $exception = new FileDoesNotExistException('File does not exist');
        $record = $this->createMock(Record::class);
        $record->expects($this->once())
            ->method('toArray')
            ->willThrowException($exception);

        $subject = new RecordToArray(
            $record,
            null,
            new TableDefinitionCollection(new AutomaticLanguageKeysRegistry()),
            self::createStub(EventDispatcher::class)
        );

        self::assertSame(['__errorMessage' => 'File does not exist'], $subject->toArray());
    }
}

<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyRecordCollectionToArray;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry;
use TYPO3\CMS\Core\Collection\LazyRecordCollection;
use TYPO3\CMS\Core\Domain\RawRecord;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\Record\ComputedProperties;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;

final class LazyRecordCollectionToArrayTest extends TestCase
{
    public function testUnknownRecordTypeThrowsException(): void
    {
        $record = $this->createMock(Record::class);
        $record->method('getRawRecord')->willReturn(
            new RawRecord(1, 1, [], new ComputedProperties(), 'tx_unknown')
        );
        $collection = new LazyRecordCollection('records', static function () use ($record): array {
            return ['record' => $record];
        });

        $subject = new LazyRecordCollectionToArray(
            $collection,
            null,
            new TableDefinitionCollection(new AutomaticLanguageKeysRegistry()),
            self::createStub(EventDispatcher::class)
        );

        $this->expectExceptionCode(1746095968);
        $this->expectExceptionMessage('record');

        $subject->toArray();
    }
}

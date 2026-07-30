<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyRecordCollectionToArray;
use Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\TestHelper\ContentBlocksDefinitionTrait;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinition;
use TYPO3\CMS\ContentBlocks\FieldType\TextFieldType;
use TYPO3\CMS\Core\Collection\LazyRecordCollection;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyRecordCollectionToArrayTest extends UnitTestCase
{
    use ContentBlocksDefinitionTrait;

    public function testEmptyCollectionReturnsEmptyArray(): void
    {
        $subject = new LazyRecordCollectionToArray(
            $this->createCollection([]),
            null,
            $this->createTableDefinitionCollection(),
            $this->createEventDispatcher()
        );

        self::assertSame([], $subject->toArray());
    }

    public function testInjectedTableDefinitionTakesPrecedenceOverRecordType(): void
    {
        $tableDefinition = $this->createCollectionTableDefinition('tt_content');
        $record = $this->createRecord(['tt_content_headline' => 'Hello'], 'tx_some_other_table');

        $subject = new LazyRecordCollectionToArray(
            $this->createCollection([$record]),
            $tableDefinition,
            $this->createTableDefinitionCollection($tableDefinition),
            $this->createEventDispatcher()
        );

        // The de-prefixed key proves the injected definition was applied.
        self::assertSame([0 => ['headline' => 'Hello']], $subject->toArray());
    }

    public function testTableDefinitionIsResolvedFromRecordTypeWhenNotInjected(): void
    {
        $tableDefinition = $this->createCollectionTableDefinition('tx_test_collection');
        $record = $this->createRecord(['tx_test_collection_headline' => 'Hello'], 'tx_test_collection');

        $subject = new LazyRecordCollectionToArray(
            $this->createCollection([$record]),
            null,
            $this->createTableDefinitionCollection($tableDefinition),
            $this->createEventDispatcher()
        );

        self::assertSame([0 => ['headline' => 'Hello']], $subject->toArray());
    }

    public function testMainTypeIsUsedForRecordsWithARecordType(): void
    {
        $tableDefinition = $this->createCollectionTableDefinition('tt_content');
        $record = $this->createRecord(['tt_content_headline' => 'Hello'], 'tt_content.simple');

        $subject = new LazyRecordCollectionToArray(
            $this->createCollection([$record]),
            null,
            $this->createTableDefinitionCollection($tableDefinition),
            $this->createEventDispatcher()
        );

        self::assertSame([0 => ['headline' => 'Hello']], $subject->toArray());
    }

    /**
     * sys_category records are intentionally processed without a table definition,
     * because Content Blocks does not register a definition for sys_category.
     *
     * Reachable whenever no definition could be derived from the field, for
     * example a Relation field with several allowed tables, one of them being
     * sys_category. Without this branch such a collection would hit the
     * exception below.
     */
    public function testSysCategoryRecordIsProcessedWithoutTableDefinition(): void
    {
        $record = $this->createRecord(['title' => 'News'], 'sys_category');

        $subject = new LazyRecordCollectionToArray(
            $this->createCollection([$record]),
            null,
            $this->createTableDefinitionCollection(),
            $this->createEventDispatcher()
        );

        self::assertSame([0 => ['title' => 'News']], $subject->toArray());
    }

    public function testUnknownTableWithoutTableDefinitionThrowsException(): void
    {
        $record = $this->createRecord(['title' => 'Whatever'], 'tx_unknown_table');

        $subject = new LazyRecordCollectionToArray(
            $this->createCollection([$record]),
            null,
            $this->createTableDefinitionCollection(),
            $this->createEventDispatcher()
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(1746095968);
        $this->expectExceptionMessage('Unknown case in LazyRecordCollectionToArray->toArray() switch for key "0"');

        $subject->toArray();
    }

    public function testExceptionMessageContainsTheOffendingKey(): void
    {
        $known = $this->createCollectionTableDefinition('tx_test_collection');
        $collection = $this->createCollection([
            'first' => $this->createRecord(['tx_test_collection_headline' => 'Hello'], 'tx_test_collection'),
            'second' => $this->createRecord(['title' => 'Whatever'], 'tx_unknown_table'),
        ]);

        $subject = new LazyRecordCollectionToArray(
            $collection,
            null,
            $this->createTableDefinitionCollection($known),
            $this->createEventDispatcher()
        );

        $this->expectExceptionCode(1746095968);
        $this->expectExceptionMessage('for key "second"');

        $subject->toArray();
    }

    public function testArrayKeysArePreserved(): void
    {
        $tableDefinition = $this->createCollectionTableDefinition('tx_test_collection');
        $collection = $this->createCollection([
            'alpha' => $this->createRecord(['tx_test_collection_headline' => 'A'], 'tx_test_collection'),
            'bravo' => $this->createRecord(['tx_test_collection_headline' => 'B'], 'tx_test_collection'),
        ]);

        $subject = new LazyRecordCollectionToArray(
            $collection,
            $tableDefinition,
            $this->createTableDefinitionCollection($tableDefinition),
            $this->createEventDispatcher()
        );

        self::assertSame([
            'alpha' => ['headline' => 'A'],
            'bravo' => ['headline' => 'B'],
        ], $subject->toArray());
    }

    private function createCollectionTableDefinition(string $table): TableDefinition
    {
        return $this->createTableDefinition($table, [
            $this->createTcaFieldDefinition(
                $table . '_headline',
                'headline',
                $this->createFieldType(TextFieldType::class)
            ),
        ]);
    }

    /**
     * @param array<array-key, Record> $records
     */
    private function createCollection(array $records): LazyRecordCollection
    {
        return new LazyRecordCollection('', static fn(): array => $records);
    }
}

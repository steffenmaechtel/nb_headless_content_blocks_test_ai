<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyRecordCollectionToArray;
use TYPO3\CMS\ContentBlocks\Definition\Capability\TableDefinitionCapability;
use TYPO3\CMS\ContentBlocks\Definition\ContentType\ContentType;
use TYPO3\CMS\ContentBlocks\Definition\ContentType\ContentTypeDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Definition\PaletteDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Definition\SqlColumnDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinition;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Definition\TcaFieldDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry;
use TYPO3\CMS\Core\Collection\LazyRecordCollection;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use PHPUnit\Framework\Attributes\CoversClass;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(LazyRecordCollectionToArray::class)]
final class LazyRecordCollectionToArrayTest extends UnitTestCase
{
    private TableDefinitionCollection $emptyTableDefinitionCollection;
    private EventDispatcher $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->emptyTableDefinitionCollection = new TableDefinitionCollection(
            new AutomaticLanguageKeysRegistry()
        );
        $this->eventDispatcher = $this->createMock(EventDispatcher::class);
    }

    public function testEmptyCollectionReturnsEmptyArray(): void
    {
        $collection = $this->createMock(LazyRecordCollection::class);
        $collection->method('getIterator')->willReturn(new \EmptyIterator());

        $subject = new LazyRecordCollectionToArray(
            $collection,
            null,
            $this->emptyTableDefinitionCollection,
            $this->eventDispatcher
        );

        self::assertSame([], $subject->toArray());
    }

    public function testSingleItemIsConverted(): void
    {
        $record = $this->createRecordMock('tt_content', 1, 'Single item');

        $collection = $this->createMock(LazyRecordCollection::class);
        $collection->method('getIterator')->willReturn(
            new \ArrayIterator([0 => $record])
        );

        $subject = new LazyRecordCollectionToArray(
            $collection,
            $this->getPredefinedTableDefinition('tt_content'),
            $this->emptyTableDefinitionCollection,
            $this->eventDispatcher
        );

        $result = $subject->toArray();

        self::assertCount(1, $result);
        self::assertArrayNotHasKey('uid', $result[0]);
        self::assertSame('Single item', $result[0]['header']);
    }

    public function testMultipleItemsAreConverted(): void
    {
        $record1 = $this->createRecordMock('tt_content', 1, 'First');
        $record2 = $this->createRecordMock('tt_content', 2, 'Second');

        $collection = $this->createMock(LazyRecordCollection::class);
        $collection->method('getIterator')->willReturn(
            new \ArrayIterator([0 => $record1, 1 => $record2])
        );

        $subject = new LazyRecordCollectionToArray(
            $collection,
            $this->getPredefinedTableDefinition('tt_content'),
            $this->emptyTableDefinitionCollection,
            $this->eventDispatcher
        );

        $result = $subject->toArray();

        self::assertCount(2, $result);
        self::assertSame('First', $result[0]['header']);
        self::assertSame('Second', $result[1]['header']);
    }

    public function testSysCategoryFieldsAreReturned(): void
    {
        $rawRecord = $this->createMock(\TYPO3\CMS\Core\Domain\RawRecord::class);
        $rawRecord->method('getMainType')->willReturn('sys_category');

        $record = $this->createMock(Record::class);
        $record->method('getRawRecord')->willReturn($rawRecord);
        $record->method('toArray')->willReturn([
            'uid' => 5,
            'pid' => 0,
            'title' => 'My Category',
        ]);

        $collection = $this->createMock(LazyRecordCollection::class);
        $collection->method('getIterator')->willReturn(
            new \ArrayIterator([0 => $record])
        );

        $subject = new LazyRecordCollectionToArray(
            $collection,
            null,
            $this->emptyTableDefinitionCollection,
            $this->eventDispatcher
        );

        $result = $subject->toArray();

        self::assertCount(1, $result);
        self::assertArrayNotHasKey('uid', $result[0]);
        self::assertSame('My Category', $result[0]['title']);
    }

    public function testUnknownTableThrowsException(): void
    {
        $record = $this->createRecordMock('tx_unknown_table');

        $collection = $this->createMock(LazyRecordCollection::class);
        $collection->method('getIterator')->willReturn(
            new \ArrayIterator([0 => $record])
        );

        $subject = new LazyRecordCollectionToArray(
            $collection,
            null,
            $this->emptyTableDefinitionCollection,
            $this->eventDispatcher
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unknown case in LazyRecordCollectionToArray->toArray()');

        $subject->toArray();
    }

    public function testPredefinedTableDefinitionTakesPrecedenceOverAutoDetection(): void
    {
        $record = $this->createRecordMock('tx_custom_table', 1, 'With predefined');

        $collection = $this->createMock(LazyRecordCollection::class);
        $collection->method('getIterator')->willReturn(
            new \ArrayIterator([0 => $record])
        );

        $subject = new LazyRecordCollectionToArray(
            $collection,
            $this->getPredefinedTableDefinition('tx_custom_table'),
            $this->emptyTableDefinitionCollection,
            $this->eventDispatcher
        );

        $result = $subject->toArray();

        self::assertCount(1, $result);
        self::assertArrayNotHasKey('uid', $result[0]);
        self::assertSame('With predefined', $result[0]['header']);
    }

    /**
     * Creates a minimal but valid TableDefinition for testing.
     */
    private function getPredefinedTableDefinition(string $table): TableDefinition
    {
        $emptyTcaFields = TcaFieldDefinitionCollection::createFromArray([], $table);

        return new TableDefinition(
            table: $table,
            capability: TableDefinitionCapability::createFromArray([]),
            typeField: null,
            contentType: ContentType::CONTENT_ELEMENT,
            contentTypeDefinitionCollection: new ContentTypeDefinitionCollection(),
            sqlColumnDefinitionCollection: new SqlColumnDefinitionCollection(),
            tcaFieldDefinitionCollection: $emptyTcaFields,
            paletteDefinitionCollection: new PaletteDefinitionCollection(),
            parentReferences: [],
        );
    }

    /**
     * @param int $uid
     * @param string $fieldContent
     */
    private function createRecordMock(string $mainType, int $uid = 1, string $fieldContent = 'Test'): Record
    {
        $rawRecord = $this->createMock(\TYPO3\CMS\Core\Domain\RawRecord::class);
        $rawRecord->method('getMainType')->willReturn($mainType);

        $record = $this->createMock(Record::class);
        $record->method('getRawRecord')->willReturn($rawRecord);
        $record->method('toArray')->willReturn([
            'uid' => $uid,
            'pid' => 0,
            'colPos' => 0,
            'CType' => 'text',
            'header' => $fieldContent,
        ]);

        return $record;
    }
}
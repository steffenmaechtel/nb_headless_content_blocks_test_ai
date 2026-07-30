<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\RecordToArray;
use Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\TestHelper\ContentBlocksDefinitionTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinition;
use TYPO3\CMS\ContentBlocks\FieldType\TextFieldType;
use TYPO3\CMS\Core\Domain\RawRecord;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\Record\ComputedProperties;
use TYPO3\CMS\Core\Domain\RecordPropertyClosure;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class RecordToArrayTest extends UnitTestCase
{
    use ContentBlocksDefinitionTrait;

    public static function systemFieldDataProvider(): \Generator
    {
        yield 'uid' => ['uid'];
        yield 'pid' => ['pid'];
        yield 'colPos' => ['colPos'];
        yield 'CType' => ['CType'];
        yield 'foreign_table_parent_uid' => ['foreign_table_parent_uid'];
        yield 'tx_container_parent' => ['tx_container_parent'];
    }

    public function testRecordPropertiesAreReturned(): void
    {
        $record = $this->createRecord(['header' => 'My header', 'sorting' => 128]);

        $subject = $this->createSubject($record);

        self::assertSame(['header' => 'My header', 'sorting' => 128], $subject->toArray());
    }

    #[DataProvider('systemFieldDataProvider')]
    public function testSystemFieldIsRemoved(string $systemField): void
    {
        $record = $this->createRecord([
            'header' => 'My header',
            $systemField => 'should be removed',
        ]);

        $result = $this->createSubject($record)->toArray();

        self::assertArrayNotHasKey($systemField, $result);
        self::assertSame(['header' => 'My header'], $result);
    }

    public function testAllSystemFieldsAreRemovedAtOnce(): void
    {
        $record = $this->createRecord([
            'header' => 'My header',
            'colPos' => 0,
            'CType' => 'test_simple',
            'foreign_table_parent_uid' => 42,
            'tx_container_parent' => 4711,
        ]);

        self::assertSame(['header' => 'My header'], $this->createSubject($record)->toArray());
    }

    public function testTableDefinitionIsPassedDownForKeyDeprefixing(): void
    {
        $tableDefinition = $this->createContentTableDefinition();
        $record = $this->createRecord(['tt_content_my_text' => 'Some text']);

        $subject = $this->createSubject($record, $tableDefinition);

        self::assertSame(['my_text' => 'Some text'], $subject->toArray());
    }

    public function testResultIsSortedByKey(): void
    {
        $record = $this->createRecord(['zulu' => 1, 'alpha' => 2, 'mike' => 3]);

        self::assertSame([
            'alpha' => 2,
            'mike' => 3,
            'zulu' => 1,
        ], $this->createSubject($record)->toArray());
    }

    /**
     * A record property may reference a file that no longer exists on disk. The
     * exception escapes Record::toArray() unwrapped and must be turned into an
     * error message instead of breaking the whole JSON response.
     */
    public function testFileDoesNotExistExceptionIsConvertedToErrorMessage(): void
    {
        $record = new Record(
            new RawRecord(1, 1, [], new ComputedProperties(), 'tt_content'),
            [
                'image' => new RecordPropertyClosure(
                    static fn(): mixed => throw new FileDoesNotExistException('File 4711 does not exist', 1745000001)
                ),
            ]
        );

        $subject = $this->createSubject($record);

        self::assertSame(['__errorMessage' => 'File 4711 does not exist'], $subject->toArray());
    }

    public function testErrorMessageResultContainsNoOtherProperties(): void
    {
        $record = new Record(
            new RawRecord(1, 1, [], new ComputedProperties(), 'tt_content'),
            [
                'header' => 'My header',
                'image' => new RecordPropertyClosure(
                    static fn(): mixed => throw new FileDoesNotExistException('File 4711 does not exist', 1745000001)
                ),
            ]
        );

        $result = $this->createSubject($record)->toArray();

        self::assertArrayNotHasKey('header', $result);
        self::assertArrayHasKey('__errorMessage', $result);
    }

    private function createContentTableDefinition(): TableDefinition
    {
        return $this->createTableDefinition('tt_content', [
            $this->createTcaFieldDefinition(
                'tt_content_my_text',
                'my_text',
                $this->createFieldType(TextFieldType::class)
            ),
        ]);
    }

    private function createSubject(Record $record, ?TableDefinition $tableDefinition = null): RecordToArray
    {
        $tableDefinitionCollection = $tableDefinition === null
            ? $this->createTableDefinitionCollection()
            : $this->createTableDefinitionCollection($tableDefinition);

        return new RecordToArray(
            $record,
            $tableDefinition,
            $tableDefinitionCollection,
            $this->createEventDispatcher()
        );
    }
}

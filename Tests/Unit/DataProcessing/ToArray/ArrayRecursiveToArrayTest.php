<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\ArrayRecursiveToArray;
use Netzbewegung\NbHeadlessContentBlocks\Event\ModifyArrayRecursiveToArrayEvent;
use Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\TestHelper\ContentBlocksDefinitionTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinition;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Definition\TcaFieldDefinition;
use TYPO3\CMS\ContentBlocks\FieldType\CategoryFieldType;
use TYPO3\CMS\ContentBlocks\FieldType\ColorFieldType;
use TYPO3\CMS\ContentBlocks\FieldType\EmailFieldType;
use TYPO3\CMS\ContentBlocks\FieldType\FieldTypeInterface;
use TYPO3\CMS\ContentBlocks\FieldType\JsonFieldType;
use TYPO3\CMS\ContentBlocks\FieldType\PassFieldType;
use TYPO3\CMS\ContentBlocks\FieldType\PasswordFieldType;
use TYPO3\CMS\ContentBlocks\FieldType\RadioFieldType;
use TYPO3\CMS\ContentBlocks\FieldType\RelationFieldType;
use TYPO3\CMS\ContentBlocks\FieldType\SelectFieldType;
use TYPO3\CMS\ContentBlocks\FieldType\SlugFieldType;
use TYPO3\CMS\ContentBlocks\FieldType\TextareaFieldType;
use TYPO3\CMS\ContentBlocks\FieldType\TextFieldType;
use TYPO3\CMS\ContentBlocks\FieldType\UuidFieldType;
use TYPO3\CMS\Core\Collection\LazyRecordCollection;
use TYPO3\CMS\Core\Domain\FlexFormFieldValues;
use TYPO3\CMS\Core\Resource\Collection\LazyFolderCollection;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class ArrayRecursiveToArrayTest extends UnitTestCase
{
    use ContentBlocksDefinitionTrait;

    /**
     * String field types that must be passed through untouched.
     */
    public static function passThroughFieldTypeDataProvider(): \Generator
    {
        yield 'Color' => [ColorFieldType::class, '#ff8700'];
        yield 'Select' => [SelectFieldType::class, 'option-a'];
        yield 'Text' => [TextFieldType::class, 'Some text'];
        yield 'Email' => [EmailFieldType::class, 'info@example.org'];
        yield 'Pass' => [PassFieldType::class, 'raw-pass-value'];
        yield 'Slug' => [SlugFieldType::class, 'my-slug'];
        yield 'Uuid' => [UuidFieldType::class, 'f47ac10b-58cc-4372-a567-0e02b2c3d479'];
    }

    public function testNullValueIsPassedThrough(): void
    {
        $subject = $this->createSubject(['key' => null]);

        self::assertSame(['key' => null], $subject->toArray());
    }

    public function testIntegerValueIsPassedThrough(): void
    {
        $subject = $this->createSubject(['key' => 42]);

        self::assertSame(['key' => 42], $subject->toArray());
    }

    public function testStringValueIsPassedThrough(): void
    {
        $subject = $this->createSubject(['key' => 'value']);

        self::assertSame(['key' => 'value'], $subject->toArray());
    }

    public function testBooleanValueIsDropped(): void
    {
        $subject = $this->createSubject(['key' => true]);

        self::assertSame([], $subject->toArray());
    }

    public function testFloatValueIsDropped(): void
    {
        $subject = $this->createSubject(['key' => 13.37]);

        self::assertSame([], $subject->toArray());
    }

    public function testUnknownObjectValueIsDropped(): void
    {
        $subject = $this->createSubject(['key' => new \stdClass()]);

        self::assertSame([], $subject->toArray());
    }

    public function testDateTimeImmutableIsFormattedAsW3C(): void
    {
        $dateTime = new \DateTimeImmutable('2026-07-22 10:15:30', new \DateTimeZone('UTC'));

        $subject = $this->createSubject(['key' => $dateTime]);

        self::assertSame(['key' => $dateTime->format(\DateTimeImmutable::W3C)], $subject->toArray());
    }

    public function testNestedArrayIsProcessedRecursively(): void
    {
        $subject = $this->createSubject([
            'level1' => [
                'level2' => [
                    'key' => 'value',
                ],
            ],
        ]);

        self::assertSame([
            'level1' => [
                'level2' => [
                    'key' => 'value',
                ],
            ],
        ], $subject->toArray());
    }

    public function testResultIsSortedByKey(): void
    {
        $subject = $this->createSubject([
            'zulu' => 1,
            'alpha' => 2,
            'mike' => 3,
        ]);

        self::assertSame([
            'alpha' => 2,
            'mike' => 3,
            'zulu' => 1,
        ], $subject->toArray());
    }

    public function testHandledEventProcessedValueIsUsed(): void
    {
        $listener = static function (ModifyArrayRecursiveToArrayEvent $event): void {
            $event->setProcessedValue('processed');
        };

        $subject = $this->createSubject(['key' => 'original'], [$listener]);

        self::assertSame(['key' => 'processed'], $subject->toArray());
    }

    public function testEventReceivesKeyAndValue(): void
    {
        $receivedEvents = [];
        $listener = static function (ModifyArrayRecursiveToArrayEvent $event) use (&$receivedEvents): void {
            $receivedEvents[] = [$event->getKey(), $event->getValue()];
        };

        $subject = $this->createSubject(['myKey' => 'myValue'], [$listener]);
        $subject->toArray();

        self::assertSame([['myKey', 'myValue']], $receivedEvents);
    }

    // --------------------------------------------------------------------
    // Key de-prefixing
    // --------------------------------------------------------------------

    public function testPrefixedKeyIsReplacedByFieldIdentifier(): void
    {
        $tableDefinition = $this->createContentTableDefinition([
            $this->createTcaFieldDefinition('tt_content_my_text', 'my_text', $this->createFieldType(TextFieldType::class)),
        ]);

        $subject = $this->createSubject(['tt_content_my_text' => 'Some text'], [], $tableDefinition);

        self::assertSame(['my_text' => 'Some text'], $subject->toArray());
    }

    public function testUnknownKeyIsNotDeprefixed(): void
    {
        $tableDefinition = $this->createContentTableDefinition([
            $this->createTcaFieldDefinition('tt_content_my_text', 'my_text', $this->createFieldType(TextFieldType::class)),
        ]);

        $subject = $this->createSubject(['header' => 'My header'], [], $tableDefinition);

        self::assertSame(['header' => 'My header'], $subject->toArray());
    }

    public function testEventReceivesTcaFieldDefinitionForKnownField(): void
    {
        $fieldDefinition = $this->createTcaFieldDefinition(
            'tt_content_my_text',
            'my_text',
            $this->createFieldType(TextFieldType::class)
        );
        $tableDefinition = $this->createContentTableDefinition([$fieldDefinition]);

        $received = [];
        $listener = static function (ModifyArrayRecursiveToArrayEvent $event) use (&$received): void {
            $received[] = $event->getTcaFieldDefinition();
        };

        $subject = $this->createSubject(['tt_content_my_text' => 'Some text'], [$listener], $tableDefinition);
        $subject->toArray();

        self::assertSame([$fieldDefinition], $received);
    }

    public function testEventProcessedValueIsStoredUnderTheDeprefixedKey(): void
    {
        $tableDefinition = $this->createContentTableDefinition([
            $this->createTcaFieldDefinition('tt_content_my_text', 'my_text', $this->createFieldType(TextFieldType::class)),
        ]);

        $listener = static function (ModifyArrayRecursiveToArrayEvent $event): void {
            $event->setProcessedValue('processed');
        };

        $subject = $this->createSubject(['tt_content_my_text' => 'original'], [$listener], $tableDefinition);

        self::assertSame(['my_text' => 'processed'], $subject->toArray());
    }

    // --------------------------------------------------------------------
    // processStringField()
    // --------------------------------------------------------------------

    /**
     * @param class-string<FieldTypeInterface> $fieldTypeClass
     */
    #[DataProvider('passThroughFieldTypeDataProvider')]
    public function testStringFieldTypeIsPassedThrough(string $fieldTypeClass, string $value): void
    {
        $tableDefinition = $this->createContentTableDefinition([
            $this->createTcaFieldDefinition('tt_content_field', 'field', $this->createFieldType($fieldTypeClass)),
        ]);

        $subject = $this->createSubject(['tt_content_field' => $value], [], $tableDefinition);

        self::assertSame(['field' => $value], $subject->toArray());
    }

    public function testPasswordFieldValueIsEmptied(): void
    {
        $tableDefinition = $this->createContentTableDefinition([
            $this->createTcaFieldDefinition('tt_content_secret', 'secret', $this->createFieldType(PasswordFieldType::class)),
        ]);

        $subject = $this->createSubject(['tt_content_secret' => 'super-secret'], [], $tableDefinition);

        self::assertSame(['secret' => ''], $subject->toArray());
    }

    public function testTextareaWithoutRichtextIsPassedThrough(): void
    {
        $tableDefinition = $this->createContentTableDefinition([
            $this->createTcaFieldDefinition(
                'tt_content_bodytext',
                'bodytext',
                $this->createFieldType(TextareaFieldType::class, ['enableRichtext' => false])
            ),
        ]);

        $subject = $this->createSubject(['tt_content_bodytext' => "line one\nline two"], [], $tableDefinition);

        self::assertSame(['bodytext' => "line one\nline two"], $subject->toArray());
    }

    /**
     * Documents current behaviour: a string field type that is not listed in the
     * switch of processStringField() falls into the default case and is returned
     * unchanged rather than raising an error.
     */
    public function testUnhandledStringFieldTypeIsPassedThrough(): void
    {
        $tableDefinition = $this->createContentTableDefinition([
            $this->createTcaFieldDefinition('tt_content_radio', 'radio', $this->createFieldType(RadioFieldType::class)),
        ]);

        $subject = $this->createSubject(['tt_content_radio' => 'value-1'], [], $tableDefinition);

        self::assertSame(['radio' => 'value-1'], $subject->toArray());
    }

    /**
     * Documents a defect: processStringField() guards against integer keys via
     * `is_int($key)`, but toArray() already passes the key to
     * TcaFieldDefinitionCollection::hasField(), which only accepts strings.
     * The guard in processStringField() is therefore unreachable and an integer
     * key combined with a table definition raises a TypeError.
     *
     * This is currently not triggered in production because arrays are only
     * combined with a table definition when they originate from
     * Record::toArray(), which always yields string keys.
     */
    public function testIntegerKeyCombinedWithTableDefinitionThrowsTypeError(): void
    {
        $tableDefinition = $this->createContentTableDefinition([
            $this->createTcaFieldDefinition('tt_content_secret', 'secret', $this->createFieldType(PasswordFieldType::class)),
        ]);

        $subject = $this->createSubject([0 => 'super-secret'], [], $tableDefinition);

        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('TcaFieldDefinitionCollection::hasField(): Argument #1 ($key) must be of type string, int given');

        $subject->toArray();
    }

    public function testIntegerKeyWithoutTableDefinitionBypassesFieldTypeProcessing(): void
    {
        $subject = $this->createSubject([0 => 'super-secret']);

        self::assertSame([0 => 'super-secret'], $subject->toArray());
    }

    // --------------------------------------------------------------------
    // Value object branches
    // --------------------------------------------------------------------

    public function testJsonFieldArrayIsNotProcessedRecursively(): void
    {
        $tableDefinition = $this->createContentTableDefinition([
            $this->createTcaFieldDefinition('tt_content_my_json', 'my_json', $this->createFieldType(JsonFieldType::class)),
        ]);

        // Booleans and floats survive only because the raw array is taken over as is.
        $json = ['zulu' => true, 'alpha' => 13.37];

        $subject = $this->createSubject(['tt_content_my_json' => $json], [], $tableDefinition);

        self::assertSame(['my_json' => $json], $subject->toArray());
    }

    public function testFlexFormFieldValuesAreConvertedToSheetArray(): void
    {
        $sheets = ['sDEF' => ['settings' => ['headline' => 'Hello']]];

        $subject = $this->createSubject(['my_flex' => new FlexFormFieldValues($sheets)]);

        self::assertSame(['my_flex' => $sheets], $subject->toArray());
    }

    public function testLazyFolderCollectionIsConvertedToPaths(): void
    {
        $storage = $this->createMock(ResourceStorage::class);
        $storage->method('getConfiguration')->willReturn(['basePath' => 'fileadmin/']);
        $folder = new Folder($storage, '/user_upload/', 'user_upload');
        $collection = new LazyFolderCollection('', static fn(): array => [$folder]);

        $subject = $this->createSubject(['my_folders' => $collection]);

        self::assertSame(['my_folders' => [0 => '/fileadmin/user_upload/']], $subject->toArray());
    }

    public function testRecordValueIsConvertedWithResolvedTableDefinition(): void
    {
        $collectionDefinition = $this->createCollectionTableDefinition();
        $tableDefinition = $this->createContentTableDefinition([
            $this->createTcaFieldDefinition(
                'tt_content_my_relation',
                'my_relation',
                $this->createFieldType(RelationFieldType::class, ['foreign_table' => 'tx_test_collection'])
            ),
        ]);

        $record = $this->createRecord(['tx_test_collection_headline' => 'Inner'], 'tx_test_collection');

        $subject = $this->createSubject(
            ['tt_content_my_relation' => $record],
            [],
            $tableDefinition,
            $this->createTableDefinitionCollection($tableDefinition, $collectionDefinition)
        );

        self::assertSame(['my_relation' => ['headline' => 'Inner']], $subject->toArray());
    }

    public function testRecordValueUsesNoTableDefinitionForUnregisteredForeignTable(): void
    {
        $tableDefinition = $this->createContentTableDefinition([
            $this->createTcaFieldDefinition(
                'tt_content_my_relation',
                'my_relation',
                $this->createFieldType(RelationFieldType::class, ['foreign_table' => 'tx_not_registered'])
            ),
        ]);

        $record = $this->createRecord(['tx_test_collection_headline' => 'Inner'], 'tx_test_collection');

        $subject = $this->createSubject(['tt_content_my_relation' => $record], [], $tableDefinition);

        self::assertSame(['my_relation' => ['tx_test_collection_headline' => 'Inner']], $subject->toArray());
    }

    public function testKeyThatIsItselfATableNameResolvesThatTableDefinition(): void
    {
        $collectionDefinition = $this->createCollectionTableDefinition();
        $record = $this->createRecord(['tx_test_collection_headline' => 'Inner'], 'tx_test_collection');

        $subject = $this->createSubject(
            ['tx_test_collection' => $record],
            [],
            null,
            $this->createTableDefinitionCollection($collectionDefinition)
        );

        self::assertSame(['tx_test_collection' => ['headline' => 'Inner']], $subject->toArray());
    }

    // --------------------------------------------------------------------
    // getTableNameByKey(): "allowed" handling
    // --------------------------------------------------------------------

    public function testSingleAllowedTableResolvesTableDefinition(): void
    {
        $collectionDefinition = $this->createCollectionTableDefinition();
        $tableDefinition = $this->createContentTableDefinition([
            $this->createTcaFieldDefinition(
                'tt_content_my_relation',
                'my_relation',
                $this->createFieldType(RelationFieldType::class, ['allowed' => 'tx_test_collection'])
            ),
        ]);

        $record = $this->createRecord(['tx_test_collection_headline' => 'Inner'], 'tx_test_collection');

        $subject = $this->createSubject(
            ['tt_content_my_relation' => $record],
            [],
            $tableDefinition,
            $this->createTableDefinitionCollection($tableDefinition, $collectionDefinition)
        );

        self::assertSame(['my_relation' => ['headline' => 'Inner']], $subject->toArray());
    }

    /**
     * With more than one allowed table the target table is ambiguous, so no
     * table definition is resolved and the keys stay prefixed.
     */
    public function testMultipleAllowedTablesResolveNoTableDefinition(): void
    {
        $collectionDefinition = $this->createCollectionTableDefinition();
        $tableDefinition = $this->createContentTableDefinition([
            $this->createTcaFieldDefinition(
                'tt_content_my_relation',
                'my_relation',
                $this->createFieldType(RelationFieldType::class, ['allowed' => 'tx_test_collection,pages'])
            ),
        ]);

        $record = $this->createRecord(['tx_test_collection_headline' => 'Inner'], 'tx_test_collection');

        $subject = $this->createSubject(
            ['tt_content_my_relation' => $record],
            [],
            $tableDefinition,
            $this->createTableDefinitionCollection($tableDefinition, $collectionDefinition)
        );

        self::assertSame(['my_relation' => ['tx_test_collection_headline' => 'Inner']], $subject->toArray());
    }

    public function testForeignTableTakesPrecedenceOverAllowed(): void
    {
        $collectionDefinition = $this->createCollectionTableDefinition();
        $tableDefinition = $this->createContentTableDefinition([
            $this->createTcaFieldDefinition(
                'tt_content_my_relation',
                'my_relation',
                $this->createFieldType(RelationFieldType::class, [
                    'foreign_table' => 'tx_test_collection',
                    'allowed' => 'pages,tt_content',
                ])
            ),
        ]);

        $record = $this->createRecord(['tx_test_collection_headline' => 'Inner'], 'tx_test_collection');

        $subject = $this->createSubject(
            ['tt_content_my_relation' => $record],
            [],
            $tableDefinition,
            $this->createTableDefinitionCollection($tableDefinition, $collectionDefinition)
        );

        self::assertSame(['my_relation' => ['headline' => 'Inner']], $subject->toArray());
    }

    public function testCategoryFieldRoutesCollectionToSysCategoryConversion(): void
    {
        $tableDefinition = $this->createContentTableDefinition([
            $this->createTcaFieldDefinition(
                'tt_content_my_categories',
                'my_categories',
                $this->createFieldType(CategoryFieldType::class)
            ),
        ]);

        $collection = new LazyRecordCollection('', fn(): array => [
            $this->createRecord(['title' => 'News', 'description' => 'ignored'], 'sys_category', 7, 3),
        ]);

        $subject = $this->createSubject(['tt_content_my_categories' => $collection], [], $tableDefinition);

        self::assertSame([
            'my_categories' => [
                0 => ['uid' => 7, 'pid' => 3, 'title' => 'News'],
            ],
        ], $subject->toArray());
    }

    /**
     * @param TcaFieldDefinition[] $fields
     */
    private function createContentTableDefinition(array $fields): TableDefinition
    {
        return $this->createTableDefinition('tt_content', $fields);
    }

    private function createCollectionTableDefinition(): TableDefinition
    {
        return $this->createTableDefinition('tx_test_collection', [
            $this->createTcaFieldDefinition(
                'tx_test_collection_headline',
                'headline',
                $this->createFieldType(TextFieldType::class)
            ),
        ]);
    }

    /**
     * @param callable[] $listeners
     */
    private function createSubject(
        array $array,
        array $listeners = [],
        ?TableDefinition $tableDefinition = null,
        ?TableDefinitionCollection $tableDefinitionCollection = null
    ): ArrayRecursiveToArray {
        if ($tableDefinitionCollection === null) {
            $tableDefinitionCollection = $tableDefinition === null
                ? $this->createTableDefinitionCollection()
                : $this->createTableDefinitionCollection($tableDefinition);
        }

        return new ArrayRecursiveToArray(
            $array,
            $tableDefinition,
            $tableDefinitionCollection,
            $this->createEventDispatcher($listeners)
        );
    }
}

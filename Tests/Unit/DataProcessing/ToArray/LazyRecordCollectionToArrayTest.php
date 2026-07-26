<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyRecordCollectionToArray;
use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\RecordToArray;
use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\TypolinkParameterToArray;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry;
use TYPO3\CMS\Core\LinkHandling\TypolinkParameter;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyRecordCollectionToArrayTest extends UnitTestCase
{
    public function testConvertsAllRecords(): void
    {
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $records = [
            ['uid' => 1, 'my_field' => 'value1'],
            ['uid' => 2, 'my_field' => 'value2'],
            ['uid' => 3, 'my_field' => 'value3'],
        ];

        $subject = new LazyRecordCollectionToArray(
            $records,
            'tt_content',
            $tableDefinitionCollection,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(3, $result);
        self::assertArrayHasKey('my_field', $result[0]);
        self::assertSame('value1', $result[0]['my_field']);
        self::assertArrayHasKey('my_field', $result[1]);
        self::assertSame('value2', $result[1]['my_field']);
        self::assertArrayHasKey('my_field', $result[2]);
        self::assertSame('value3', $result[2]['my_field']);
    }

    public function testHandlesSysCategoryTable(): void
    {
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $records = [
            ['uid' => 1, 'pid' => 1, 'title' => 'Category 1'],
            ['uid' => 2, 'pid' => 1, 'title' => 'Category 2'],
        ];

        $subject = new LazyRecordCollectionToArray(
            $records,
            'sys_category',
            $tableDefinitionCollection,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(2, $result);
        self::assertArrayHasKey('uid', $result[0]);
        self::assertArrayHasKey('pid', $result[0]);
        self::assertArrayHasKey('title', $result[0]);
        self::assertArrayNotHasKey('my_field', $result[0]);
    }

    public function testHandlesSysCategoryTableWithoutTableDefinition(): void
    {
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $records = [
            ['uid' => 1, 'pid' => 1, 'title' => 'Category 1'],
        ];

        $subject = new LazyRecordCollectionToArray(
            $records,
            'sys_category',
            $tableDefinitionCollection,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(1, $result);
        self::assertArrayHasKey('uid', $result[0]);
        self::assertArrayHasKey('pid', $result[0]);
        self::assertArrayHasKey('title', $result[0]);
    }

    public function testHandlesUnknownTable(): void
    {
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $records = [
            ['uid' => 1, 'my_field' => 'value1'],
        ];

        $subject = new LazyRecordCollectionToArray(
            $records,
            'unknown_table',
            $tableDefinitionCollection,
            $typolinkConverter
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unknown table: unknown_table');

        $subject->toArray();
    }

    public function testHandlesEmptyRecords(): void
    {
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $subject = new LazyRecordCollectionToArray(
            [],
            'tt_content',
            $tableDefinitionCollection,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(0, $result);
    }

    public function testHandlesNullRecords(): void
    {
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $subject = new LazyRecordCollectionToArray(
            null,
            'tt_content',
            $tableDefinitionCollection,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(0, $result);
    }

    public function testHandlesNonArrayRecords(): void
    {
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $subject = new LazyRecordCollectionToArray(
            'not_an_array',
            'tt_content',
            $tableDefinitionCollection,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(0, $result);
    }

    public function testRemovesSystemFieldsFromRecords(): void
    {
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $records = [
            ['uid' => 1, 'pid' => 2, 'my_field' => 'value'],
        ];

        $subject = new LazyRecordCollectionToArray(
            $records,
            'tt_content',
            $tableDefinitionCollection,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertArrayNotHasKey('uid', $result[0]);
        self::assertArrayNotHasKey('pid', $result[0]);
        self::assertArrayHasKey('my_field', $result[0]);
    }

    public function testDelegatesToRecordToArray(): void
    {
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: 'https://example.com'));

        $records = [
            ['my_field' => 'value'],
        ];

        $subject = new LazyRecordCollectionToArray(
            $records,
            'tt_content',
            $tableDefinitionCollection,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertArrayHasKey('my_field', $result[0]);
        self::assertSame('value', $result[0]['my_field']);
    }

    public function testHandlesRecordsWithNullValues(): void
    {
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $records = [
            ['uid' => 1, 'my_field' => null],
            ['uid' => 2, 'my_field' => 'value'],
        ];

        $subject = new LazyRecordCollectionToArray(
            $records,
            'tt_content',
            $tableDefinitionCollection,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertArrayHasKey('my_field', $result[0]);
        self::assertNull($result[0]['my_field']);
        self::assertArrayHasKey('my_field', $result[1]);
        self::assertSame('value', $result[1]['my_field']);
    }

    public function testHandlesRecordsWithBooleanValues(): void
    {
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $records = [
            ['uid' => 1, 'my_field' => true],
            ['uid' => 2, 'my_field' => false],
        ];

        $subject = new LazyRecordCollectionToArray(
            $records,
            'tt_content',
            $tableDefinitionCollection,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertArrayHasKey('my_field', $result[0]);
        self::assertArrayHasKey('my_field', $result[1]);
    }

    public function testHandlesRecordsWithFloatValues(): void
    {
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $records = [
            ['uid' => 1, 'my_field' => 13.37],
        ];

        $subject = new LazyRecordCollectionToArray(
            $records,
            'tt_content',
            $tableDefinitionCollection,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertArrayHasKey('my_field', $result[0]);
    }

    public function testHandlesRecordsWithDateTimeValues(): void
    {
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $records = [
            ['uid' => 1, 'my_datetime' => new \DateTimeImmutable('2026-07-22 10:15:30', new \DateTimeZone('UTC'))],
        ];

        $subject = new LazyRecordCollectionToArray(
            $records,
            'tt_content',
            $tableDefinitionCollection,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertArrayHasKey('my_datetime', $result[0]);
        self::assertIsString($result[0]['my_datetime']);
    }

    /**
     * @param mixed $value
     */
    private function createSubject($value, string $tableName): LazyRecordCollectionToArray
    {
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        return new LazyRecordCollectionToArray($value, $tableName, $tableDefinitionCollection, $typolinkConverter);
    }
}

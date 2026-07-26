<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyRecordCollectionToArray;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyRecordCollectionToArrayTest extends UnitTestCase
{
    public function testConvertsRecordCollectionToLazyArray(): void
    {
        $record1 = ['uid' => 1, 'title' => 'Record 1'];
        $record2 = ['uid' => 2, 'title' => 'Record 2'];

        $collection = [
            'records' => [$record1, $record2],
        ];

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $subject = new LazyRecordCollectionToArray($collection, $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('records', $result);
        self::assertIsArray($result['records']);
        self::assertCount(2, $result['records']);
    }

    public function testHandlesEmptyCollection(): void
    {
        $collection = [
            'records' => [],
        ];

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $subject = new LazyRecordCollectionToArray($collection, $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('records', $result);
        self::assertIsArray($result['records']);
        self::assertEmpty($result['records']);
    }

    public function testHandlesNullCollection(): void
    {
        $collection = null;

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $subject = new LazyRecordCollectionToArray($collection, $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('records', $result);
        self::assertIsArray($result['records']);
        self::assertEmpty($result['records']);
    }

    public function testHandlesMissingKeyWithDefault(): void
    {
        $collection = [];

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $subject = new LazyRecordCollectionToArray($collection, $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('records', $result);
        self::assertIsArray($result['records']);
        self::assertEmpty($result['records']);
    }

    public function testHandlesMixedRecordTypes(): void
    {
        $collection = [
            'records' => [
                ['uid' => 1, 'title' => 'Simple Record'],
                ['uid' => 2, 'meta' => ['nested' => 'value']],
                ['uid' => 3, 'data' => [1, 2, 3]],
            ],
        ];

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $subject = new LazyRecordCollectionToArray($collection, $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertCount(3, $result['records']);
        self::assertIsArray($result['records'][1]['meta']);
        self::assertIsArray($result['records'][2]['data']);
    }

    public function testPreservesLazyLoadingStructure(): void
    {
        $record = ['uid' => 1, 'title' => 'Record 1'];

        $collection = [
            'records' => [$record],
        ];

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $subject = new LazyRecordCollectionToArray($collection, $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertArrayHasKey('records', $result);
        self::assertIsArray($result['records']);
        self::assertArrayNotHasKey('original', $result);
    }

    public function testHandlesRecordsWithNullValues(): void
    {
        $record = [
            'uid' => 1,
            'title' => null,
            'description' => null,
        ];

        $collection = [
            'records' => [$record],
        ];

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $subject = new LazyRecordCollectionToArray($collection, $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertCount(1, $result['records']);
        self::assertNull($result['records'][0]['title']);
        self::assertNull($result['records'][0]['description']);
    }

    public function testHandlesRecordsWithDateTimeValues(): void
    {
        $dateTime = new \DateTimeImmutable('2026-07-22 15:30:00', new \DateTimeZone('UTC'));

        $record = [
            'uid' => 1,
            'created' => $dateTime->format(\DateTimeImmutable::W3C),
        ];

        $collection = [
            'records' => [$record],
        ];

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $subject = new LazyRecordCollectionToArray($collection, $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertCount(1, $result['records']);
        self::assertIsString($result['records'][0]['created']);
        self::assertStringStartsWith('2026-07-22T', $result['records'][0]['created']);
    }
}
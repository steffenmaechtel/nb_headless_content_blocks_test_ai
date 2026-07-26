<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\RecordToArray;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class RecordToArrayTest extends UnitTestCase
{
    public function testConvertsRecordArrayToJsonCompatible(): void
    {
        $record = [
            'uid' => 123,
            'title' => 'Test Title',
            'description' => 'Test Description',
            'created' => '2026-01-01 00:00:00',
        ];

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $subject = new RecordToArray($record, $tableDefinitionCollection, $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('uid', $result);
        self::assertArrayHasKey('title', $result);
        self::assertArrayHasKey('description', $result);
    }

    public function testPassesThroughScalarValues(): void
    {
        $record = [
            'uid' => 42,
            'status' => 'active',
            'published' => true,
        ];

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $subject = new RecordToArray($record, $tableDefinitionCollection, $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertSame(42, $result['uid']);
        self::assertSame('active', $result['status']);
        self::assertTrue($result['published']);
    }

    public function testPassesThroughNullValues(): void
    {
        $record = [
            'uid' => 123,
            'title' => null,
            'description' => null,
        ];

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $subject = new RecordToArray($record, $tableDefinitionCollection, $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertNotNull($result['uid']);
        self::assertNull($result['title']);
        self::assertNull($result['description']);
    }

    public function testHandlesNestedArrays(): void
    {
        $record = [
            'uid' => 123,
            'meta' => [
                'tags' => ['tag1', 'tag2'],
                'author' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                ],
            ],
        ];

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $subject = new RecordToArray($record, $tableDefinitionCollection, $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertIsArray($result['meta']);
        self::assertIsArray($result['meta']['tags']);
        self::assertIsArray($result['meta']['author']);
    }

    public function testHandlesDateTimeValues(): void
    {
        $dateTime = new \DateTimeImmutable('2026-07-22 15:30:00', new \DateTimeZone('UTC'));

        $record = [
            'uid' => 123,
            'created' => $dateTime->format(\DateTimeImmutable::W3C),
        ];

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $subject = new RecordToArray($record, $tableDefinitionCollection, $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertIsString($result['created']);
        self::assertStringStartsWith('2026-07-22T', $result['created']);
    }

    public function testHandlesEmptyArray(): void
    {
        $record = [];

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $subject = new RecordToArray($record, $tableDefinitionCollection, $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertSame([], $result);
    }

    public function testHandlesMixedTypes(): void
    {
        $record = [
            'uid' => 123,
            'title' => 'Test',
            'description' => null,
            'published' => true,
            'tags' => [],
            'url' => 'https://example.com',
            'price' => 19.99,
        ];

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $subject = new RecordToArray($record, $tableDefinitionCollection, $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertSame(123, $result['uid']);
        self::assertSame('Test', $result['title']);
        self::assertNull($result['description']);
        self::assertTrue($result['published']);
        self::assertIsArray($result['tags']);
        self::assertSame('https://example.com', $result['url']);
        self::assertSame(19.99, $result['price']);
    }
}
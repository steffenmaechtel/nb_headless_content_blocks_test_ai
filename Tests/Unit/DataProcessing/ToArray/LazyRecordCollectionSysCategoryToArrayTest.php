<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use TYPO3\CMS\Core\Domain\Record;
use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyRecordCollectionSysCategoryToArray;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyRecordCollectionSysCategoryToArrayTest extends UnitTestCase
{
    public function testConvertsSysCategoryCollectionToArray(): void
    {
        $category1 = $this->createMock(Record::class);
        $category1->method('toArray')->willReturn(['uid' => 123, 'pid' => 1, 'title' => 'Test Category']);

        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);

        $subject = new LazyRecordCollectionSysCategoryToArray($category1, $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey('uid', $result[0]);
        self::assertArrayHasKey('pid', $result[0]);
        self::assertArrayHasKey('title', $result[0]);
        self::assertSame(123, $result[0]['uid']);
    }

    public function testHandlesEmptyCollection(): void
    {
        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);

        $subject = new LazyRecordCollectionSysCategoryToArray([], $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertEmpty($result);
    }

    public function testHandlesNullCollection(): void
    {
        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);

        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Typed property');

        $subject = new LazyRecordCollectionSysCategoryToArray(null, $tableDefinitionCollection);
    }

    public function testHandlesMultipleCategories(): void
    {
        $category1 = $this->createMock(Record::class);
        $category1->method('toArray')->willReturn(['uid' => 1, 'pid' => 1, 'title' => 'Category 1']);

        $category2 = $this->createMock(Record::class);
        $category2->method('toArray')->willReturn(['uid' => 2, 'pid' => 1, 'title' => 'Category 2']);

        $category3 = $this->createMock(Record::class);
        $category3->method('toArray')->willReturn(['uid' => 3, 'pid' => 1, 'title' => 'Category 3']);

        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);

        $subject = new LazyRecordCollectionSysCategoryToArray([$category1, $category2, $category3], $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(3, $result);
        self::assertSame(1, $result[0]['uid']);
        self::assertSame(2, $result[1]['uid']);
        self::assertSame(3, $result[2]['uid']);
    }

    public function testPreservesCategoryStructure(): void
    {
        $category = $this->createMock(Record::class);
        $category->method('toArray')->willReturn(['uid' => 1, 'title' => 'Test Category']);

        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);

        $subject = new LazyRecordCollectionSysCategoryToArray([$category], $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey(0, $result);
    }

    public function testHandlesCategoriesWithNestedData(): void
    {
        $category = $this->createMock(Record::class);
        $category->method('toArray')->willReturn([
            'uid' => 123,
            'pid' => 1,
            'title' => 'Test Category',
            'children' => [
                ['uid' => 1, 'title' => 'Child 1'],
                ['uid' => 2, 'title' => 'Child 2'],
            ],
            'tags' => ['tag1', 'tag2', 'tag3'],
        ]);

        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);

        $subject = new LazyRecordCollectionSysCategoryToArray([$category], $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertCount(1, $result);
        self::assertArrayHasKey('uid', $result[0]);
        self::assertArrayHasKey('pid', $result[0]);
        self::assertArrayHasKey('title', $result[0]);
    }

    public function testHandlesCategoriesWithNullValues(): void
    {
        $category = $this->createMock(Record::class);
        $category->method('toArray')->willReturn([
            'uid' => 123,
            'pid' => null,
            'title' => null,
        ]);

        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);

        $subject = new LazyRecordCollectionSysCategoryToArray([$category], $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertNotNull($result[0]['uid']);
        self::assertNull($result[0]['pid']);
        self::assertNull($result[0]['title']);
    }

    public function testHandlesNestedCategoryData(): void
    {
        $category1 = $this->createMock(Record::class);
        $category1->method('toArray')->willReturn([
            'uid' => 1,
            'pid' => 1,
            'title' => 'Parent Category',
            'children' => [
                ['uid' => 1, 'title' => 'Child'],
            ],
        ]);

        $category2 = $this->createMock(Record::class);
        $category2->method('toArray')->willReturn([
            'uid' => 1,
            'pid' => 1,
            'title' => 'Grand Parent',
            'children' => [
                ['uid' => 1, 'title' => 'Grandchild'],
            ],
        ]);

        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);

        $subject = new LazyRecordCollectionSysCategoryToArray([$category1, $category2], $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertCount(2, $result);
        self::assertArrayHasKey('uid', $result[0]);
        self::assertArrayHasKey('uid', $result[1]);
    }
}

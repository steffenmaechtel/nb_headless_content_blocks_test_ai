<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyRecordCollectionSysCategoryToArray;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyRecordCollectionSysCategoryToArrayTest extends UnitTestCase
{
    public function testConvertsSysCategoryCollectionToLazyArray(): void
    {
        $category = [
            'id' => 123,
            'title' => 'Test Category',
            'slug' => 'test-category',
        ];

        $collection = [
            'sys_category' => [$category],
        ];

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $subject = new LazyRecordCollectionSysCategoryToArray($collection, $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('sys_category', $result);
        self::assertIsArray($result['sys_category']);
        self::assertCount(1, $result['sys_category']);
    }

    public function testHandlesEmptyCollection(): void
    {
        $collection = [
            'sys_category' => [],
        ];

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $subject = new LazyRecordCollectionSysCategoryToArray($collection, $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('sys_category', $result);
        self::assertIsArray($result['sys_category']);
        self::assertEmpty($result['sys_category']);
    }

    public function testHandlesNullCollection(): void
    {
        $collection = null;

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $subject = new LazyRecordCollectionSysCategoryToArray($collection, $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('sys_category', $result);
        self::assertIsArray($result['sys_category']);
        self::assertEmpty($result['sys_category']);
    }

    public function testHandlesMissingKeyWithDefault(): void
    {
        $collection = [];

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $subject = new LazyRecordCollectionSysCategoryToArray($collection, $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('sys_category', $result);
        self::assertIsArray($result['sys_category']);
        self::assertEmpty($result['sys_category']);
    }

    public function testHandlesMultipleCategories(): void
    {
        $categories = [
            ['id' => 1, 'title' => 'Category 1'],
            ['id' => 2, 'title' => 'Category 2'],
            ['id' => 3, 'title' => 'Category 3'],
        ];

        $collection = [
            'sys_category' => $categories,
        ];

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $subject = new LazyRecordCollectionSysCategoryToArray($collection, $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertCount(3, $result['sys_category']);
        self::assertSame(1, $result['sys_category'][0]['id']);
        self::assertSame(2, $result['sys_category'][1]['id']);
        self::assertSame(3, $result['sys_category'][2]['id']);
    }

    public function testPreservesLazyLoadingStructure(): void
    {
        $category = ['id' => 1, 'title' => 'Test Category'];

        $collection = [
            'sys_category' => [$category],
        ];

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $subject = new LazyRecordCollectionSysCategoryToArray($collection, $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertArrayHasKey('sys_category', $result);
        self::assertIsArray($result['sys_category']);
        self::assertArrayNotHasKey('original', $result);
    }

    public function testHandlesCategoriesWithNestedData(): void
    {
        $category = [
            'id' => 123,
            'title' => 'Test Category',
            'children' => [
                ['id' => 1, 'title' => 'Child 1'],
                ['id' => 2, 'title' => 'Child 2'],
            ],
            'tags' => ['tag1', 'tag2', 'tag3'],
        ];

        $collection = [
            'sys_category' => [$category],
        ];

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $subject = new LazyRecordCollectionSysCategoryToArray($collection, $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertCount(1, $result['sys_category']);
        self::assertCount(2, $result['sys_category'][0]['children']);
        self::assertIsArray($result['sys_category'][0]['tags']);
        self::assertCount(3, $result['sys_category'][0]['tags']);
    }

    public function testHandlesCategoriesWithNullValues(): void
    {
        $category = [
            'id' => 123,
            'title' => null,
            'slug' => null,
        ];

        $collection = [
            'sys_category' => [$category],
        ];

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $subject = new LazyRecordCollectionSysCategoryToArray($collection, $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertCount(1, $result['sys_category']);
        self::assertNull($result['sys_category'][0]['title']);
        self::assertNull($result['sys_category'][0]['slug']);
    }

    public function testHandlesNestedCategoryData(): void
    {
        $parentCategory = [
            'id' => 1,
            'title' => 'Parent Category',
            'children' => [
                ['id' => 1, 'title' => 'Child'],
            ],
        ];

        $grandParent = [
            'id' => 1,
            'children' => [
                ['id' => 1, 'children' => [['id' => 1, 'title' => 'Grandchild']]],
            ],
        ];

        $collection = [
            'sys_category' => [$parentCategory, $grandParent],
        ];

        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $subject = new LazyRecordCollectionSysCategoryToArray($collection, $tableDefinitionCollection);

        $result = $subject->toArray();

        self::assertCount(2, $result['sys_category']);
        self::assertIsArray($result['sys_category'][0]['children']);
        self::assertIsArray($result['sys_category'][1]['children']);
        self::assertIsArray($result['sys_category'][1]['children'][0]['children']);
    }
}
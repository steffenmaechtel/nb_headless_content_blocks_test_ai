<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyRecordCollectionSysCategoryToArray;
use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\TypolinkParameterToArray;
use TYPO3\CMS\Core\LinkHandling\TypolinkParameter;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyRecordCollectionSysCategoryToArrayTest extends UnitTestCase
{
    public function testConvertsToReducedArray(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $records = [
            ['uid' => 1, 'pid' => 1, 'title' => 'Category 1'],
            ['uid' => 2, 'pid' => 1, 'title' => 'Category 2'],
            ['uid' => 3, 'pid' => 2, 'title' => 'Category 3'],
        ];

        $subject = new LazyRecordCollectionSysCategoryToArray(
            $records,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(3, $result);
        self::assertArrayHasKey('uid', $result[0]);
        self::assertArrayHasKey('pid', $result[0]);
        self::assertArrayHasKey('title', $result[0]);
        self::assertArrayNotHasKey('description', $result[0]);
    }

    public function testEachCategoryHasRequiredFields(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $records = [
            ['uid' => 1, 'pid' => 1, 'title' => 'Category 1'],
        ];

        $subject = new LazyRecordCollectionSysCategoryToArray(
            $records,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertArrayHasKey('uid', $result[0]);
        self::assertSame(1, $result[0]['uid']);
        self::assertArrayHasKey('pid', $result[0]);
        self::assertSame(1, $result[0]['pid']);
        self::assertArrayHasKey('title', $result[0]);
        self::assertSame('Category 1', $result[0]['title']);
    }

    public function testHandlesNullRecords(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $subject = new LazyRecordCollectionSysCategoryToArray(
            null,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(0, $result);
    }

    public function testHandlesEmptyRecords(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $subject = new LazyRecordCollectionSysCategoryToArray(
            [],
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(0, $result);
    }

    public function testHandlesNonArrayRecords(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $subject = new LazyRecordCollectionSysCategoryToArray(
            'not_an_array',
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(0, $result);
    }

    public function testRemovesNonRequiredFields(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $records = [
            [
                'uid' => 1,
                'pid' => 1,
                'title' => 'Category 1',
                'description' => 'Some description',
                'hidden' => 1,
                'deleted' => 0,
            ],
        ];

        $subject = new LazyRecordCollectionSysCategoryToArray(
            $records,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertArrayHasKey('uid', $result[0]);
        self::assertArrayHasKey('pid', $result[0]);
        self::assertArrayHasKey('title', $result[0]);
        self::assertArrayNotHasKey('description', $result[0]);
        self::assertArrayNotHasKey('hidden', $result[0]);
        self::assertArrayNotHasKey('deleted', $result[0]);
    }

    public function testHandlesRecordsWithNullValues(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $records = [
            ['uid' => 1, 'pid' => null, 'title' => 'Category 1'],
            ['uid' => 2, 'pid' => 1, 'title' => null],
        ];

        $subject = new LazyRecordCollectionSysCategoryToArray(
            $records,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertArrayHasKey('uid', $result[0]);
        self::assertArrayHasKey('pid', $result[0]);
        self::assertArrayHasKey('title', $result[0]);
        self::assertArrayHasKey('uid', $result[1]);
        self::assertArrayHasKey('pid', $result[1]);
        self::assertArrayHasKey('title', $result[1]);
    }

    public function testHandlesRecordsWithBooleanValues(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $records = [
            ['uid' => 1, 'pid' => 1, 'title' => 'Category 1', 'hidden' => true],
        ];

        $subject = new LazyRecordCollectionSysCategoryToArray(
            $records,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertArrayHasKey('uid', $result[0]);
        self::assertArrayHasKey('pid', $result[0]);
        self::assertArrayHasKey('title', $result[0]);
        self::assertArrayNotHasKey('hidden', $result[0]);
    }

    public function testHandlesRecordsWithFloatValues(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $records = [
            ['uid' => 1, 'pid' => 1, 'title' => 'Category 1', 'sorting' => 13.37],
        ];

        $subject = new LazyRecordCollectionSysCategoryToArray(
            $records,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertArrayHasKey('uid', $result[0]);
        self::assertArrayHasKey('pid', $result[0]);
        self::assertArrayHasKey('title', $result[0]);
        self::assertArrayNotHasKey('sorting', $result[0]);
    }

    /**
     * @param mixed $value
     */
    private function createSubject($value): LazyRecordCollectionSysCategoryToArray
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        return new LazyRecordCollectionSysCategoryToArray($value, $typolinkConverter);
    }
}

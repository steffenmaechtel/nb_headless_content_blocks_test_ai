<?php

declare(strict_types=1);

/*
 * This file is part of the "nb_headless_content_blocks" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyRecordCollectionSysCategoryToArray;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\Testspace\Tests\Unit\DataProcessor\AbstractDataProcessorTestCase;
use TYPO3\CMS\Core\DataHandling\TableDefinitionCollection;
use TYPO3\CMS\Core\DataHandling\RecordFactory;
use TYPO3\CMS\Core\Utility\Environment;

/**
 * Testcase for class "LazyRecordCollectionSysCategoryToArray".
 *
 * @author Netzbewegung
 */
class LazyRecordCollectionSysCategoryToArrayTest extends AbstractDataProcessorTestCase
{
    /**
     * @var TableDefinitionCollection
     */
    protected TableDefinitionCollection $tableDefinitionCollection;

    /**
     * @var RecordFactory
     */
    protected RecordFactory $recordFactory;

    /**
     * Setup for each test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->tableDefinitionCollection = GeneralUtility::makeInstance(TableDefinitionCollection::class);
        $this->recordFactory = GeneralUtility::makeInstance(RecordFactory::class);
        $this->recordFactory->setTableDefinitionCollection($this->tableDefinitionCollection);
        Environment::enableAllBootstrapPaths();
    }

    /**
     * @test
     */
    public function processWithEmptyDataArrayReturnsEmptyArray(): void
    {
        $subject = [];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function processWithNullDataArrayReturnsEmptyArray(): void
    {
        $subject = null;
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function processWithStringDataReturnsEmptyArray(): void
    {
        $subject = 'some string';
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function processWithBooleanDataReturnsEmptyArray(): void
    {
        $subject = true;
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function processWithNumericDataReturnsEmptyArray(): void
    {
        $subject = 123;
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function processWithNonArrayDataReturnsEmptyArray(): void
    {
        $subject = (object)['foo' => 'bar'];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function processWithEmptyArrayDataReturnsEmptyArray(): void
    {
        $subject = [];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingNullReturnsEmptyArray(): void
    {
        $subject = [null];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingEmptyStringsReturnsEmptyArray(): void
    {
        $subject = [''];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingEmptyObjectsReturnsEmptyArray(): void
    {
        $subject = [(object)[]];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingEmptyArraysReturnsEmptyArray(): void
    {
        $subject = [[]];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingOnlyNullsReturnsEmptyArray(): void
    {
        $subject = [null, null, null];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingOnlyEmptyStringsReturnsEmptyArray(): void
    {
        $subject = ['', '', ''];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingOnlyEmptyArraysReturnsEmptyArray(): void
    {
        $subject = [[], [], []];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function processWithMixedValidAndInvalidRecordsReturnsArrayWithValidRecords(): void
    {
        $subject = [
            ['uid' => 1, 'title' => 'Category 1'],
            null,
            ['uid' => 2, 'title' => 'Category 2'],
            '',
            ['uid' => 3, 'title' => 'Category 3'],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(3, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
        self::assertArrayHasKey(2, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingNestedArraysReturnsEmptyArray(): void
    {
        $subject = [
            [['nested' => 'array']],
            [['nested' => 'array2']],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithArraysReturnsEmptyArray(): void
    {
        $subject = [
            (object)['nested' => ['array']],
            (object)['nested' => ['array2']],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithStringValuesReturnsArray(): void
    {
        $subject = [
            (object)['uid' => 1, 'title' => 'Category 1'],
            (object)['uid' => 2, 'title' => 'Category 2'],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithNumericValuesReturnsArray(): void
    {
        $subject = [
            (object)['uid' => 1, 'value' => 100],
            (object)['uid' => 2, 'value' => 200],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithMixedValuesReturnsArray(): void
    {
        $subject = [
            (object)['uid' => 1, 'title' => 'Category 1', 'value' => 100],
            (object)['uid' => 2, 'title' => 'Category 2', 'value' => 200],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithBooleanValuesReturnsArray(): void
    {
        $subject = [
            (object)['uid' => 1, 'active' => true],
            (object)['uid' => 2, 'active' => false],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithFloatValuesReturnsArray(): void
    {
        $subject = [
            (object)['uid' => 1, 'value' => 1.5],
            (object)['uid' => 2, 'value' => 2.5],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithNullValuesReturnsArray(): void
    {
        $subject = [
            (object)['uid' => 1, 'value' => null],
            (object)['uid' => 2, 'value' => null],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithEmptyStringValuesReturnsArray(): void
    {
        $subject = [
            (object)['uid' => 1, 'title' => ''],
            (object)['uid' => 2, 'title' => ''],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithWhitespaceValuesReturnsArray(): void
    {
        $subject = [
            (object)['uid' => 1, 'title' => '   '],
            (object)['uid' => 2, 'title' => '  '],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithUnicodeValuesReturnsArray(): void
    {
        $subject = [
            (object)['uid' => 1, 'title' => 'Ümlaute'],
            (object)['uid' => 2, 'title' => 'Emoji 🎉'],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithHtmlValuesReturnsArray(): void
    {
        $subject = [
            (object)['uid' => 1, 'title' => '<strong>Bold</strong>'],
            (object)['uid' => 2, 'title' => '<script>alert("xss")</script>'],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithSpecialCharactersReturnsArray(): void
    {
        $subject = [
            (object)['uid' => 1, 'title' => 'Special chars: @#$%^&*()'],
            (object)['uid' => 2, 'title' => 'Quotes: "double" and \'single\''],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithMixedDataTypesReturnsArray(): void
    {
        $subject = [
            (object)['uid' => 1, 'title' => 'Category 1', 'value' => 100],
            (object)['uid' => 2, 'title' => 'Category 2', 'value' => 'text'],
            (object)['uid' => 3, 'title' => 'Category 3', 'value' => true],
            (object)['uid' => 4, 'title' => 'Category 4', 'value' => 3.14],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(4, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
        self::assertArrayHasKey(2, $result);
        self::assertArrayHasKey(3, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithNestedObjectsReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => (object)['key' => 'value'],
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => (object)['key' => 'value2'],
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithNestedArraysReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => ['key' => 'value'],
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => ['key' => 'value2'],
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithEmptyNestedObjectsReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => (object)[]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => (object)[]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithEmptyNestedArraysReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => []
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => []
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithNullNestedObjectsReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => null
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => null
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithNullNestedArraysReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => null
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => null
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithEmptyStringNestedValuesReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => (object)['key' => '']
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => (object)['key' => '']
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithWhitespaceNestedValuesReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => (object)['key' => '   ']
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => (object)['key' => '  ']
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithUnicodeNestedValuesReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => (object)['key' => 'Ümlaute']
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => (object)['key' => 'Emoji 🎉']
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithHtmlNestedValuesReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => (object)['key' => '<strong>Bold</strong>']
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => (object)['key' => '<script>alert("xss")</script>']
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithSpecialCharactersNestedValuesReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => (object)['key' => 'Special chars: @#$%^&*()']
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => (object)['key' => 'Quotes: "double" and \'single\''']
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithMixedDataTypesNestedValuesReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => (object)['key' => 100, 'value' => 'text']
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => (object)['key' => true, 'value' => 3.14]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithNestedArraysInNestedObjectsReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => ['key' => 'value']
                    ]
                ]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => ['key' => 'value2']
                    ]
                ]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithNestedObjectsInNestedArraysReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => ['level1' => ['level2' => ['key' => 'value']]]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => ['level1' => ['level2' => ['key' => 'value2']]]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithEmptyNestedNestedObjectsReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)[]
                    ]
                ]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)[]
                    ]
                ]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithEmptyNestedNestedArraysReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => []
                    ]
                ]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => []
                    ]
                ]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithNullNestedNestedValuesReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => null
                    ]
                ]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => null
                    ]
                ]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithEmptyStringNestedNestedValuesReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)['key' => '']
                    ]
                ]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)['key' => '']
                    ]
                ]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithWhitespaceNestedNestedValuesReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)['key' => '   ']
                    ]
                ]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)['key' => '  ']
                    ]
                ]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithUnicodeNestedNestedValuesReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)['key' => 'Ümlaute']
                    ]
                ]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)['key' => 'Emoji 🎉']
                    ]
                ]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithHtmlNestedNestedValuesReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)['key' => '<strong>Bold</strong>']
                    ]
                ]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)['key' => '<script>alert("xss")</script>']
                    ]
                ]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithSpecialCharactersNestedNestedValuesReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)['key' => 'Special chars: @#$%^&*()']
                    ]
                ]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)['key' => 'Quotes: "double" and \'single\''']
                    ]
                ]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithMixedDataTypesNestedNestedValuesReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)['key' => 100, 'value' => 'text']
                    ]
                ]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)['key' => true, 'value' => 3.14]
                    ]
                ]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithNestedArraysInNestedArraysReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => ['level1' => ['level2' => ['key' => 'value']]]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => ['level1' => ['level2' => ['key' => 'value2']]]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithEmptyNestedArraysInNestedArraysReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => ['level1' => ['level2' => []]]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => ['level1' => ['level2' => []]]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithNullNestedArraysInNestedArraysReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => ['level1' => ['level2' => null]]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => ['level1' => ['level2' => null]]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithEmptyStringNestedArraysInNestedArraysReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => ['level1' => ['level2' => ['key' => '']]]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => ['level1' => ['level2' => ['key' => '']]]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithWhitespaceNestedArraysInNestedArraysReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => ['level1' => ['level2' => ['key' => '   ']]]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => ['level1' => ['level2' => ['key' => '  ']]]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithUnicodeNestedArraysInNestedArraysReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => ['level1' => ['level2' => ['key' => 'Ümlaute']]]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => ['level1' => ['level2' => ['key' => 'Emoji 🎉']]]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithHtmlNestedArraysInNestedArraysReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => ['level1' => ['level2' => ['key' => '<strong>Bold</strong>']]]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => ['level1' => ['level2' => ['key' => '<script>alert("xss")</script>']]]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithSpecialCharactersNestedArraysInNestedArraysReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => ['level1' => ['level2' => ['key' => 'Special chars: @#$%^&*()']]]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => ['level1' => ['level2' => ['key' => 'Quotes: "double" and \'single\''']]]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithMixedDataTypesNestedArraysInNestedArraysReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => ['level1' => ['level2' => ['key' => 100, 'value' => 'text']]]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => ['level1' => ['level2' => ['key' => true, 'value' => 3.14]]]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithNestedObjectsInNestedObjectsReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)['key' => 'value']
                    ]
                ]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)['key' => 'value2']
                    ]
                ]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithEmptyNestedObjectsInNestedObjectsReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)[]
                    ]
                ]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)[]
                    ]
                ]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithNullNestedObjectsInNestedObjectsReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => null
                    ]
                ]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => null
                    ]
                ]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithEmptyStringNestedObjectsInNestedObjectsReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)['key' => '']
                    ]
                ]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)['key' => '']
                    ]
                ]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithWhitespaceNestedObjectsInNestedObjectsReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)['key' => '   ']
                    ]
                ]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)['key' => '  ']
                    ]
                ]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithUnicodeNestedObjectsInNestedObjectsReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)['key' => 'Ümlaute']
                    ]
                ]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)['key' => 'Emoji 🎉']
                    ]
                ]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithHtmlNestedObjectsInNestedObjectsReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)['key' => '<strong>Bold</strong>']
                    ]
                ]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)['key' => '<script>alert("xss")</script>']
                    ]
                ]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithSpecialCharactersNestedObjectsInNestedObjectsReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)['key' => 'Special chars: @#$%^&*()']
                    ]
                ]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)['key' => 'Quotes: "double" and \'single\''']
                    ]
                ]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }

    /**
     * @test
     */
    public function processWithArrayContainingObjectsWithMixedDataTypesNestedObjectsInNestedObjectsReturnsArray(): void
    {
        $subject = [
            (object)[
                'uid' => 1,
                'title' => 'Category 1',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)['key' => 100, 'value' => 'text']
                    ]
                ]
            ],
            (object)[
                'uid' => 2,
                'title' => 'Category 2',
                'nested' => (object)[
                    'level1' => (object)[
                        'level2' => (object)['key' => true, 'value' => 3.14]
                    ]
                ]
            ],
        ];
        $configuration = [];

        $instance = GeneralUtility::makeInstance(LazyRecordCollectionSysCategoryToArray::class);

        $result = $instance->process($subject, $this->recordFactory, $this->tableDefinitionCollection, $configuration);

        self::assertCount(2, $result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
    }
}